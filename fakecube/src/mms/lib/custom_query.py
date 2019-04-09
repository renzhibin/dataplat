#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import re
import time
import sys
import MySQLdb
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
from fun_replace import FuncReplace
from utils import getMysqlConfigByAppName,getDBConfigByGroups,getDBConfigByName
from mms_conf import MmsConf

reload(sys)
sys.setdefaultencoding('utf-8')
class CustomQuery():
    def __init__(self):
        self.projects= []
        self.op_map = {
            'like': lambda c, v: ' '.join(('lower('+c+')', 'like', '"%' + str(v).lower() + '%"')),
            'not like': lambda c, v: ' '.join((c, 'not like', '"%' + str(v) + '%"')),
            'start with': lambda c, v: ' '.join((c, 'like', '"' + str(v) + '%"')),
            'end with': lambda c, v: ' '.join((c, 'like', '"%' + str(v) + '"')),
            '=': lambda c, v: ' '.join((c, '=', '"' + str(v) + '"')),
            '!=': lambda c, v: ' '.join((c, '!=', '"' + str(v) + '"')),
            '>': lambda c, v: ' '.join((c, '>', str(float(v)))),
            '>=': lambda c, v: ' '.join((c, '>=', str(float(v)))),
            '<': lambda c, v: ' '.join((c, '<', str(float(v)))),
            '<=': lambda c, v: ' '.join((c, '<=', str(float(v)))),
            'REGEXP': lambda c, v: ' '.join((c, 'REGEXP', '"' + str(v) + '"')),
            'NOT REGEXP': lambda c, v: ' '.join((c, 'NOT REGEXP', '"' + str(v) + '"')),
            'IS NULL': lambda c, v: c + ' IS NULL',
            'IS NOT NULL': lambda c, v: c + ' IS NOT NULL',
            'IS EMPTY': lambda c, v: ' '.join(('(', c, 'IS NULL or', c, "==''")),
            'IS NOT EMPTY': lambda c, v: ' '.join(('(', c, 'IS NOT NULL or', c, "!=''")),
            'in': lambda c, v: c + ' in (' + v + ')',
            'not in': lambda c, v: c + ' not in (' + v + ')',
            'between': lambda c, v: c + ' between ' + v[0] + ' and ' + v[1]
        }
        self.white_custom=False
        self.is_dbtable=False
        self.custom_datasource_key=[]

    def __getGroupKeys(self, qs):

        a = qs.strip().split(",")

        groups = []

        for i in a:
            j = i.split("=")

            groups.append(j[0].strip())

        groups.sort()

        return groups
    def getDB2tables(self,sql='',date='',edate=''):
        udf = ['DB']
        reg_udf = '|'.join(udf)
        r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9._ ]+)\))' % reg_udf, re.DOTALL)
        result = r.findall(sql)
        db_list=[]
        db2tables={}
        for e in result:
            db_table=str(e[2]).split('.')
            db_list.append(db_table[0])
            #新增支持跨库查询
            # db2tables[e[0]]=db_table[1]
            db2tables[e[0]] = str(e[2])
        if len(db2tables)>0:
            self.is_dbtable=True

        return list(set(db_list)),db2tables

    def getDims2tables(self,sql='',date='',edate=''):

        tables_dict={}
        sql_project_list=[]
        udf = ['TABLE']
        reg_udf = '|'.join(udf)
        r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
        result = r.findall(sql)


        for e in result:
            pro_dims=str(e[2]).split(',')
            project=pro_dims[0]
            dims=pro_dims[1:]
            dims.sort()
            if len(dims) == 1 and 'all' in dims:
                dims[0]=''
            dims_str=','.join(dims)
            sql_project_list.append("'%s'"%(project))
            tables_dict[e[0]]={'project':project,'dims':dims_str,'tables':[]}

        if not tables_dict:
            return tables_dict

        sql_project_list=list(set(sql_project_list))
        self.projects = sql_project_list
        project_str=','.join(sql_project_list)

        start_suffix = time.strftime('%Y%m', time.strptime(date, "%Y-%m-%d"))
        end_suffix = time.strftime('%Y%m', time.strptime(edate, "%Y-%m-%d"))
        white_custom_project=['system_custom_select']

        if self.projects:
            if str(self.projects[0]).strip("'") in white_custom_project:
                start_suffix='2016'
                end_suffix='2016'
                self.white_custom=True

        sql='''
            select * from t_stat_entry_table where metric_conf in (%s) and suffix>=%s and suffix<=%s
        '''%(project_str,start_suffix,end_suffix)
        mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()
        cur.execute(sql)

        result=[]
        columns=cur.description
        for value in cur.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]]=column
            result.append(tmp)
        cur.close()
        conn.close()

        dims2tables={}

        for k,v in tables_dict.items():
            dims_exists = False
            for e in result:
                if v['dims']==e['group_keys']:
                    if not dims2tables.has_key(k):
                        dims2tables[k]=[]
                    dims2tables[k].append(e['table_name'])
                    #其他数据源配置key
                    self.custom_datasource_key.append('_'.join((e['metric_conf'],e['group_keys'])))
                    dims_exists = True
            if not dims_exists:
                dims2tables[k]=[]

        return dims2tables

    def __getWhereStr(self,search=None):
        where=''
        if not search:
            return True,where

        for gb in search:
            col = gb.get("key", '').strip()

            if len(col)==0:
                continue

            op = gb.get("op", '')
            val = gb.get("val", [])

            if not op:
                continue
            #操作符IS NULL,IS NOT NULL,IS EMPTY,IS NOT EMPTY 不需要用户传val值，其他操作符都需要，如果没传直接忽略
            #if op not in ('IS NULL', 'IS NOT NULL', 'IS_EMPTY', 'IS NOT EMPTY') and not val:
              #  continue
            #between条件所提供的值应该是长度为2的数组


            if op in ('in', 'not in'):
                tmp_val = ['"' + v + '"' for v in val]
                val = ','.join(tmp_val)
            elif op == 'between':
                val=val
            else:
                val=val[0]

            op_func = self.op_map.get(op)
            if not op_func:
                return False,"op_not_support"

            try:
                tmpw = op_func(col, val)
            except ValueError as e:
                return False,"filter_value_error"
            where+=' and ' + tmpw

        return where
    def __createSql2(self,base_sql,db2tables,search=None,customSort=None):
        #当查询非metric库表
        base_sql = base_sql.strip(';')
        sql = ''

        for k, v in db2tables.items():
            table = v
            base_sql = base_sql.replace(k, table)

        sql = base_sql

        sql = "select * from (%s)tmp_base " % (sql)
        if search:
            where = 'where 1=1 '
            where += self.__getWhereStr(search=search)
            sql += where

        if customSort:
            tmp_sql_order = []
            for c_s in customSort:
                order_col = c_s['key']
                order_type = c_s['order']
                tmp_order_str = order_col + ' ' + order_type
                tmp_sql_order.append(tmp_order_str)

            if len(tmp_sql_order) > 0:
                tmp_sql_order_str = ",".join(tmp_sql_order)
                sql += " order by %s " % (tmp_sql_order_str)

        return sql


    def __createSql(self,base_sql,dims2tables,search=None,customSort=None):
        '''
            len tables_dict
            0:没有维度组合表
            1:只有一个维度组合表
            >1:多个
        '''

        start_suffix = time.strftime('%Y%m', time.strptime(self.date, "%Y-%m-%d"))
        end_suffix = time.strftime('%Y%m', time.strptime(self.edate, "%Y-%m-%d"))

        base_sql=base_sql.strip(';')
        sql=''
        tables_len=len(dims2tables)


        if tables_len==0:
            #没有维度组合表
            sql=base_sql
        elif tables_len==1:

            for k,v in dims2tables.items():
                v=list(set(v))
                index = 1
                sub_sql = 'select * from ({0}) tmp_{1}'
                for t in v:
                    sql += str.format(sub_sql, base_sql.replace(k,t), index)
                    sql += ' union '
                    index += 1
            sql=sql.strip()[:-5]

        else:
            if start_suffix==end_suffix:
                #同一个月
                for k,v in dims2tables.items():
                    v=list(set(v))
                    table=v[0]
                    base_sql=base_sql.replace(k,table)
                sql=base_sql
            else:
                #不同月且多个维度组合
                index=0
                for k,v in dims2tables.items():
                    v=list(set(v))
                    sub_sql1 = 'select * from ({0}) tmp_{1}'
                    sub_sql2 = "select * from {0} where cdate>='{1}' and cdate<='{2}'"
                    tmp_table=''
                    for table in v:
                        index+=1
                        tmp_table += str.format(sub_sql1, str.format(sub_sql2, table,self.date,self.edate), index)
                        tmp_table += ' union '
                        #tmp_table+="(select * from %s where cdate>='%s' and cdate<='%s') union "%(table,self.date,self.edate)
                    tmp_table=tmp_table.strip()[:-5]
                    tmp_table = '(%s) dims_tmp_%s' % (tmp_table,str(index))
                    base_sql=base_sql.replace(k,tmp_table)
                sql=base_sql
        sql="select * from (%s)tmp_base "%(sql)
        if search:
            where='where 1=1 '
            where += self.__getWhereStr(search=search)
            sql+=where

        if customSort:
            tmp_sql_order=[]
            for c_s in customSort:
                order_col=c_s['key']
                order_type=c_s['order']
                tmp_order_str=order_col+' '+order_type
                tmp_sql_order.append(tmp_order_str)

            if len(tmp_sql_order)>0:
                tmp_sql_order_str=",".join(tmp_sql_order)
                sql+=" order by %s "%(tmp_sql_order_str)

        return sql

    def repalce_params(self,sql='',params=None):
        udf = ['START','END']
        reg_udf = '|'.join(udf)
        r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
        result = r.findall(sql)
        obj = FuncReplace()
        for content in result:
            if content[1]=='START' and not params['start']:
                continue
            if content[1]=='END' and not params['end']:
                continue
            b = getattr(obj, content[1])
            #忽略函数的参数，SQL的时间偏移不再处理，交给报表的时间条件设置, 偏移量都设置为0
            replace_str = b(params, '0')
            sql = sql.replace(content[0], replace_str)

        return sql


    def getResult(self,sql='',date='',edate='',index='',offset='',total=False,search=None,customSort=None):
        self.index=index
        self.offset=offset
        self.date=date
        self.edate=edate


        #TODO 检查sql中date,edate
        if '$START' not in sql or '$END' not in sql:
            return False,'sql需使用$START和$END时间函数进行时间区间设置。'

        r_p={'dt':date,'start':date,'end':edate}
        sql=self.repalce_params(sql,r_p)

        #是否为垮库查询通过判断是否使用$DB函数
        db_list,db2tables=self.getDB2tables(sql=sql,date=date,edate=edate)
        #新增支持跨库查询
        # if self.is_dbtable and len(db_list)!=1:
        #     return False, '不支持垮库查询.'

        dims2tables=self.getDims2tables(sql=sql,date=date,edate=edate)

       #根据project所对应的db进行查询
        store_db = []
        store_db_name = 'metric1'
        if len(self.projects) !=0:

            if self.white_custom:
                store_db_name='metric1'
            else:
                object_mms_conf = MmsConf()
                for project in self.projects:
                    project = project.strip("'")
                    res = object_mms_conf.select(project)
                    mysql_db = res[0]['store_db']
                    store_db.append(mysql_db)
                object_mms_conf.close_connection()

                store_db = list(set(store_db))
                if len(store_db) > 1:
                    #当前查询项目数据跨库存储， 暂时不允许
                    return False, "查询项目跨库存储, 目前不支持自定义逻辑查询"
                elif len(store_db) == 1:
                    store_db_name= store_db[0]
        else:
            if not self.is_dbtable:
                return False, '自定义逻辑查询项目为空'

        try:

            db_config = getMysqlConfigByAppName(store_db_name, 'slave', '2')
            #自定义数据表判断是否为其他数据源
            if self.white_custom:
                tmp_config=getDBConfigByGroups(self.custom_datasource_key)
                if tmp_config:
                    db_config=tmp_config


            # db_config = getMysqlConfigByAppName(store_db_name, 'slave', '2')
        except Exception, ex:
            return False, '获取db配置信息失败, Exception: %s' % str(ex)

        empty_dims_list = []
        for k,v in dims2tables.iteritems():
            v=list(set(v))
            if len(v) == 0:
                empty_dims_list.append(k)

        if len(empty_dims_list)!=0:
            empty_dims_str = ','.join(empty_dims_list)
            return False, '%s 包含的维度组合表不存在' % empty_dims_str

        sql=self.__createSql(sql,dims2tables,search=search,customSort=customSort)

        # 使用$DB函数获取配置
        if self.is_dbtable:
            #获取数据库配置
            tmp_config=getDBConfigByName(str(db_list[0]))
            if tmp_config:
                db_config=tmp_config

            sql=self.__createSql2(sql,db2tables,search=search,customSort=customSort)



        sql='select * from (%s) table_result '%(sql)
        if self.index!=''  and   self.offset!='':
            sql+= ' limit %s,%s'%(int(self.index)*int(self.offset),int(self.offset))

        elif total==True:
            sql="select count(1) total from (%s)a "%(sql)


        #TODO 添加缓存

        print sql
        starttime = time.time()
        query_msyql=MmsMysql(db_config)
        conn=query_msyql.get_conn()
        cur=query_msyql.get_cur()

        try:
            cur.execute('set autocommit=1;')
            cur.execute(sql)

        except MySQLdb.Error as e:
            print 'search sql:'+sql + '\n'
            return False,str(e)


        endtime = time.time()
        spend_time=str(endtime-starttime)

        #查询耗时
        print '查询耗时:'+spend_time
        ret=[]
        columns = cur.description
        tmp = dict()
        result_list = cur.fetchall()
        if len(result_list) != 0:
            for value in result_list:
                #跨月份多表union存在None result
                isNoneList = lambda x: x is not None
                if len(filter(isNoneList, value)) == 0:
                    continue
                else:
                    tmp = {}
                    for (index, column) in enumerate(value):
                        #lower the column name
                        tmp[str.lower(columns[index][0])] = column
                    ret.append(tmp)

        if len(ret) == 0:
            for column in columns:
                tmp[column[0]] = ''
            ret.append(tmp)

        conn.close()

        return True,ret








if __name__=='__main__':
    sql='''select a.cdate,MAX(a.activity_act_common_pay_pay_total_gmv) as bignum
from $TABLE(higo_h5_data,dim) a,$TABLE(custom_time_test,first_source) b
where a.cdate=b.cdate and a.cdate>='$START(0)' and a.cdate<='$END(0)'
    '''

    q=CustomQuery()
    #q.getDims2tables(sql,date='2015-09-26',edate='2015-09-27')
    q.getResult(sql,date='2015-06-24',edate='2015-09-28')