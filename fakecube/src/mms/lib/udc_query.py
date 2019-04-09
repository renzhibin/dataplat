#!/usr/bin/env python2.7
# coding=utf-8

'''
author: haiyuanhuang
description:
    mms基本模式只支持绝对值指标，不支持指标之间的加减乘除。用户虽然可以在HQL中写这样的逻辑，但会造成性能
    上的浪费。该文件的目的在于支持用户自定义列，这些列是其他列之间的四则运算结果。
'''

import argparse
import urllib2
import re
import string
import keyword
import random
import sys

import env as conf
from decimal2AZ import decimal2AZ
from query import Query

alphas=string.ascii_letters+'_' # the first_letter
nums=string.digits

#$A/sum($B)=_mms_conf_A/sum_mms_conf_B
#$A/$B = _mms_conf_A/_mms_conf_B
#如果是第一种 要先计算出sum_mms_conf_B
#计算方法是:
#正则表达式 匹配出sum $B
#for循环 声称sum_mms_conf_B的值


# user variable pattern
# '$A' refer the first metric column, just like in excel
var_p = re.compile(r"\$([A-Z]+)")

#aggregate expression pattern
#sum($B),sum($A+$B),avg($A+$B)
aggr_p=re.compile(r"((?:SUM|MAX|MIN|COUNT|AVG)\s*\([^\)]+\))")
op_col_re=re.compile(r"(SUM|MAX|MIN|AVG|COUNT)\s*\((.+)\)")
# in case of name crash, variables like '$A' need to be replaced to
# '_mms_sub_A'
var_sub_template = '_mms_sub_%s'


class UserDefinedColumn(object):
    def __init__(self, name, expr_str):

        self.name = name
        self.expr_str = expr_str.upper()  # 用户提交的当前列的计算表达式
        self.subed_expr_str = self.expr_str.upper()  # 替换'$A'为内部变量后的计算表达式
        self.compiled_expr = None  # 对self.subed_expr_str的编译对象
        self.aggr_expr_list = [] #有聚合函数的列

    def sub_var(self, table_header_refs):
        ref_sets = set(table_header_refs)

        all_var_list = var_p.findall(self.expr_str)
        for var in all_var_list:
            if var not in ref_sets:
                return False, '[FATAL] Reference[%s] in expression[%s] is not valid, the max ref is [%s]' % (
                var, self.expr_str, table_header_refs[-1])
            self.subed_expr_str = re.sub('\$%s' % var, var_sub_template % var, self.subed_expr_str)
        #print '[DEBUG] %s'%self.subed_expr_str
        #self.compiled_expr = compile(self.subed_expr_str, '', 'eval')


        self.aggr_expr_list = aggr_p.findall(self.subed_expr_str)


        self.compiled_expr = compile(self.subed_expr_str,'','eval')


        return True, 'OK'

def _genVarName():

    while True:
        varLen=random.randint(1,10)
        fst_letter=random.sample(alphas,1)
        remain_letters=random.sample(alphas+nums,varLen-1)

        varName=''.join(fst_letter+remain_letters)

        #是否式关键字 或者已经在命名空间中存在
        if varName in keyword.kwlist or varName in locals().keys():
            continue

        return varName

def getResult(q, dt, app_name, dim_str, metric_str, udc_str, order, order_metric, getkey, edate, index, offset,
              filter_str,search=None,table_list=None,storetype=None,start_hour=None,end_hour=None,custom_sort=None,converge=None,date_type='day'):
    '''
    para:
        dt  -   date
        app_name - the app name
        dim_str -   dimension composition
        metric_str  -   metrics
        udc_str -   user defined columns. for example: 'foo=$A/$B,bar=($C-$A)/$B'
        order   -   need order?
        order_metric    -   order by this metric
        getkey  -   no idea
        filter_str --filer condition.for example 'client_version not in ('3.0.0') and client_decive='iphone''
    '''

    status,result = q.getResult(dt, app_name, dim_str, metric_str, order, order_metric, getkey, edate, index, offset,
                         filter_str,search=search,table_list=table_list,storetype=storetype,start_hour=start_hour,end_hour=end_hour,custom_sort=custom_sort,converge=converge,date_type=date_type)
    if status==False:
        return False, result, result
    if udc_str is None or udc_str == '':
        return True, 'OK', result

    metric_list = metric_str.replace('.', '_').split(',')

    #TODO why again? to del
    if udc_str is None:
        return True, 'OK', result

    metric_num = len(metric_str.split(','))
    # 'A', 'B', ..., 'Z', 'AA', 'AB', 'AC', ...
    table_header_refs = []
    for i in range(1, metric_num + 1):
        table_header_refs.append(decimal2AZ(i))
    # user defined columns
    udc_str = urllib2.unquote(udc_str)
    udc_list = [UserDefinedColumn(*e.split('=', 1)) for e in udc_str.split(',')]
    for udc in udc_list:
        status, info = udc.sub_var(table_header_refs)
        if not status:
            return status, info, []

    # all refer are substituted
    for i in range(0, len(table_header_refs)):
        table_header_refs[i] = var_sub_template % table_header_refs[i]
    # row format: dt, dims, metrics
    metric_start_idx = len(dim_str.split(',')) + 1

    # some metric could be just None, so we have to assign one by one
    assign_code = '''
for idx, ref in enumerate(table_header_refs):
    if row[metric_list[idx]] is not None:
        exec('%s = float(row[metric_list[idx]])'%ref)
    else:
        exec('%s = 0.0'%ref)
    '''

    #udc表达式中所有聚合函数,key:为每个函数随机生成一个唯一变量名,value:(聚合操作符,表达式)组成的列表
    aggr_expr_list = {}
    for udc in udc_list:
        for e in udc.aggr_expr_list:
            (op,col)=op_col_re.findall(e)[0]
            while True:
                expKey=_genVarName()
                if aggr_expr_list.has_key(expKey):
                    continue
                udc.subed_expr_str=udc.subed_expr_str.replace(e,expKey)
                aggr_expr_list[expKey]=(op,col)
                break

        udc.compiled_expr=compile(udc.subed_expr_str,'','eval')

    #key:随机生成的唯一变量名,value:每个聚和函数的结果值
    aggr_expr_val={}

    row_idx = 0
    for row in result:

        row_idx+=1

        exec(assign_code)

        for aggr_key,aggr_val in aggr_expr_list.items():
            op,col=aggr_val
            try:
                v=eval(col)
            except Exception as e:
                v=0

            #首行初始化原始值
            aggr_expr_val[aggr_key]=v if row_idx ==1 else aggr_expr_val.get(aggr_key)

            org_v = aggr_expr_val.get(aggr_key)

            if op == 'MAX':
                aggr_expr_val[aggr_key]=v if v > org_v else org_v
            elif op == 'MIN':
                aggr_expr_val[aggr_key]=v if v < org_v else org_v
            elif op == 'COUNT':
                aggr_expr_val[aggr_key]=len(result)
                break
            else:
                aggr_expr_val[aggr_key]+=v if row_idx !=1 else 0

    for aggr_key,aggr_val in aggr_expr_list.items():
        if aggr_val[0] == 'AVG':
            aggr_expr_val[aggr_key]=float(aggr_expr_val[aggr_key])/row_idx

    for aggr_key,aggr_val in aggr_expr_val.items():
        exec('%s=aggr_val'%aggr_key)

    # rows in result are tuples, need another 2d-list to hold the final result
    extend_result = []

    for row in result:

        #print '[DEBUG]', assign_code
        #print '[DEBUG]', row
        #exit()
        #print '[DEBUG]', metric_start_idx
        exec (assign_code)
        #print '[DEBUG] %s'%_mms_sub_A
        # row is a tuple
        #print row
        # extend_row = list(row)
        extend_row = row
        for udc in udc_list:
            try:
                v = eval(udc.compiled_expr)
            except (ZeroDivisionError,TypeError):
                v = 0
            #extend_row.append(v)
            extend_row[udc.name] = v
        extend_result.append(extend_row)

    return True, 'OK', extend_result


def cmdArgsDef():
    #python query.py -p mob_app_metric -d 2014-09-21 -q client_device,client_version=5.0.0,poster_name -m user.poster_access.poster_access_uv,user.poster_access.poster_access_pv,wap.poster_access.poster_access_uv,wap.poster_access.poster_access_pv

    arg_parser = argparse.ArgumentParser()
    arg_parser.add_argument('-c', '--project', help='project name, i.e. mob_app_metric.', required=True)
    arg_parser.add_argument('-d', '--date',
                            help='Which date\'s data are going to be calculated. And the format must be "YYYY-MM-DD".',
                            required=True)
    arg_parser.add_argument('-g', '--group',
                            help='the dim need to show in query, i.e. client_device,client_version=5.0.0.',
                            required=True)
    arg_parser.add_argument('-m', '--metric',
                            help='metrics to show in query, i.e. wap.poster_access.poster_access_uv,user.poster_access.poster_access_uv.',
                            required=True)
    arg_parser.add_argument('-u', '--udc',
                            help='User defined column. Use this, user could define their own column using $A, $B just like in Excel. But only +-*/ and "()" are supported.')

    arg_parser.add_argument('-t', '--test', action='store_true',
                            help='Will use test hql to execute. But the HQL generator may not implement a test HQL.')
    args = arg_parser.parse_args()
    return args


if __name__ == "__main__":

    arg = cmdArgsDef()

    if arg.test == True:
        conf.ENTRY_TABLE = conf.TEST_ENTRY_TABLE
        conf.TABLE_PREFIX = conf.TEST_TABLE_PREFIX
    else:
        conf.ENTRY_TABLE = conf.PRODUCT_ENTRY_TABLE
        conf.TABLE_PREFIX = conf.PRODUCT_TABLE_PREFIX

    status, info, result = getResult(Query(), arg.date, arg.project, arg.group, arg.metric, arg.udc)

    if status:
        for r in result:
            print '\t'.join(map(str, r))
    else:
        print info