#!/usr/bin/env python2.7
# coding=utf-8

import time
import argparse
import sys
import re

import MySQLdb
import env as conf

from mms_mysql import MmsMysql
from mms_conf import MmsConf
from utils import getMysqlConfigByAppName

reload(sys)
sys.setdefaultencoding('utf-8')


class Query(object):
    def __init__(self, tunnel_port=-1, webapi_tag=False,query_mysql_type='slave',mysql_weight='1', db_name='metric1'):
        #for webapi
        if webapi_tag == True:
            conf.ENTRY_TABLE = conf.PRODUCT_ENTRY_TABLE
            conf.TABLE_PREFIX = conf.PRODUCT_TABLE_PREFIX

        mms_mysql_conf = getMysqlConfigByAppName(db_name, query_mysql_type, mysql_weight)
        mmsMysql=MmsMysql(mms_mysql_conf)
        self.conn=mmsMysql.get_conn()
        self.cur=mmsMysql.get_cur()
        self.dbname = db_name

        self.table_list=''
        self.storetype=''
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

    def __del__(self):
        self.cur.close()
        self.conn.close()




    #for example:
    #filter_str={
    # "group":[{"key":client_version,"op":"not in","val":['3.0.1','3.0.2']},{"col":"client_device","op":'!=',"val":'andriod'},{"col":"source","op":"like","val":"cart"}...]
    # "metrix":[{"col":"uv","op":">","val":100},{"col":"GMV","op":"<","val":1000},...]
    # }
    #return "where client_version not in ('3.0.1','3.0.2') and client_device != 'andriod' and source like '%cart%' and uv > 100 and GMV < 1000
    # "
    # def __getOpFunc(self,op):
    #     if op == 'like':
    #         return lambda c,v:' '.join(c,'like','"%'+str(v)+'%"')
    #     if op == 'start with':
    #         return lambda c,v:' '.join(c,'rlike','"'+str(v)+'"')
    #     if op == 'end with':
    #         return lambda c,v:' '.join(c,'like','"'+str(v)+'$"')
    #     if op in ('=','!=','>','>=','<','<=','REGEXP','NOT REGEXP'):
    #         return lambda c,v:' '.join(c,op,'"'+str(v)+'"')
    #     if op in ('IS NULL','IS NOT NULL'):
    #         return lambda c,v:' '.join(c,op)
    #     if op == 'IS NOT EMPTY':
    #         return lambda c,v:' '.join(c,'IS NOT NULL AND',c,"!=''")
    #     if op == 'IS EMPTY':
    #         return lambda c,v:' '.join('(',c,'IS NULL or',c,"==''")
    #     if op in ('in','not in'):
    #         return lambda c,v:' '.join(c,op,'(',v,')')
    #     if op == 'between':
    #         return lambda c,v:' '.join(c,'between',v[0],'and',v[1])

    def __getWhereStr(self, filter_cond,dim,metric):

        g_conf = ""
        m_conf = ""
        if not filter_cond:
            return True,(g_conf, m_conf)
        for gb in filter_cond:
            col_tmp = gb.get("key", '').strip().split('.')
            conf_type = len(col_tmp)

            col = col_tmp[0] if len(col_tmp) == 1 else '_'.join(col_tmp)
            if conf_type==1 and  not  col in dim:
                continue
            elif conf_type==3 and  not col in metric:
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
            if conf_type == 1:
                g_conf += ' and ' + tmpw
            elif conf_type == 3:
                m_conf += ' and ' + tmpw
        return True,(g_conf, m_conf)

    def __createSql(self,project,str_dim,metric, dt, edate='', filter_str='',search=None,limitFlag=True,start_hour=None,end_hour=None,converge=None,date_type='day'):

        table_list=self.table_list
        underline_metric_list=metric.lower().replace('.', '_').split(',')#下划线连接的key


        #兼容dim的老逻辑
        dim_list = str_dim.strip().split(",")

        query_cols_name = ','.join(dim_list) + ','
        if   query_cols_name.strip()==',':
            query_cols_name=''
        else:
            query_cols_name = ','.join(['`'+i+'`' for i in dim_list]) + ','

        wg_conf = "1=1"# where条件
        wm_conf = "1=1"# metric的where条件


        if filter_str:
            status,ret = self.__getWhereStr(filter_str,dim_list,underline_metric_list)
            if status ==False:
                return False,ret

            g_conf, m_conf = ret
            wg_conf += g_conf
            wm_conf += m_conf

        if search:
            status,ret = self.__getWhereStr(search,dim_list,underline_metric_list)
            if status ==False:
                return False,ret

            g_conf, m_conf = ret
            wg_conf += g_conf
            wm_conf += m_conf

        select_str = ''
        case_str = ''
        column_list=[]
        if  self.storetype==1:
            table=table_list[0]
            for str_m in metric.split(','):
                m=str_m.split('.')
                temp = '_'.join(m)
                select_str += 'sum(%s) as %s,' % (temp, temp)
                case_str += 'case when cat = \'%s\' and group_name = \'%s\' and metric_key=\'%s\' then metric_value end as %s,' % (
                m[0], m[1], m[2], temp)

            select_str = select_str[:-1]
            case_str = case_str[:-1]
            sql = """
                select cdate, %s %s
                from (
                    select cdate, %s %s
                    from %s
                    where  cdate>="%s" and cdate<="%s"
                ) pivot where %s and  %s
                group by cdate, %s
            """ % (query_cols_name, select_str,
                   query_cols_name, case_str,
                   table,
                   dt, edate,wg_conf, wm_conf, query_cols_name.rstrip(','))
            # if  query_cols is empty
            sql = sql.rstrip(', \t\n')
        elif self.storetype==2 or self.storetype==5:
            hour_where=''
            if start_hour and end_hour and 'hour' in dim_list:
                hour_where=" and concat(replace(cdate,'-',''),if(length(hour)<2,concat('0',hour),hour))>='%s' and concat(replace(cdate,'-',''),if(length(hour)<2,concat('0',hour),hour))<='%s' "%(start_hour,end_hour)
            wgm_conf=wg_conf+' and '+wm_conf
            sql=''
            all_column_list=[]
            for table in table_list:
                column_list=[]
                show_sql="show columns from %s"%table
                self.cur.execute(show_sql)
                for value in self.cur.fetchall():
                    column_list.append(value[0].lower())
                    all_column_list.append(value[0].lower())
                select_str=''
                for m in underline_metric_list:
                    if m in column_list:
                        select_str += ' %s as %s,'%(m,m)
                        # select_str += ' cast( %s as decimal(65,2)) as %s,'%(m,m)
                    else:
                        select_str += ' null as %s,'%(m)


                select_str = select_str[:-1]

                #处理用户自定义时间月份查询
                replace_dt=dt
                replace_edate=edate
                if date_type=='month':
                    replace_dt= time.strftime('%Y-%m', time.strptime(replace_dt, "%Y-%m-%d"))
                    #replace_edate= time.strftime('%Y-%m', time.strptime(replace_edate, "%Y-%m-%d"))

                sql += """
                    select cdate, %s %s
                    from %s
                    where %s and cdate>="%s" and cdate<="%s" %s
                    union
                 """ % (query_cols_name, select_str,
                   table,
                   wgm_conf, replace_dt, replace_edate,hour_where)
            sql=sql.strip()[:-5]






        #null值删除
        wnull_conf='1!=1'
        if len(column_list)>0:
            for m in underline_metric_list:
                if m in all_column_list:
                    wnull_conf +=' or `'+m+'` is not null '
            if len(table_list)>1:
                sql='select * from ('+sql+')b where '+wnull_conf
            else:
                sql += ' and (' + wnull_conf+ ')'


        #聚合指标
        if converge:
            aggre_group=''
            aggre_col_list=[]
            for aggre in converge:
                aggre_col=aggre['key']
                aggre_col=aggre_col.lower().replace('.', '_')
                aggre_fun=aggre['fun']
                if aggre_col in underline_metric_list:
                    tmp_aggre_col='%s(%s) as %s'%(aggre_fun,'%s.%s'%('converge',aggre_col),aggre_col)
                    aggre_col_list.append(tmp_aggre_col)
            aggre_col_str=','.join(aggre_col_list)

            if ''!=query_cols_name:
                tmp_converge_query_cols_name=query_cols_name[:-1]
                aggre_group='group by %s '%(tmp_converge_query_cols_name)

            sql='select %s %s from (%s) converge %s'%(query_cols_name,aggre_col_str,sql,aggre_group)



        if self.index!=''  and   self.offset!=''  and  (not (self.order>0 and self.ordermetric) and not self.custom_sort) and limitFlag:
            sql+= ' limit %s,%s'%(int(self.index)*int(self.offset),int(self.offset))




        return True,sql





    #dt 时间 metric_conf项目名  qs 维度  mt 指标  order 排序 0 default 1:'asc',2:'desc' ordermetric 排序key getkey是否获取表头
    # edate end date
    # filter_str 纬度和指标的过滤条件
    #str_dim example first_source='****',second_source='***'
    def getResult(self, dt, metric_conf, str_dim, metric, order=0, ordermetric='', getkey=False, edate='', index='', offset='',
                  filter_str='',gettotal=False,list_addColumn=None,search=None,table_list=None,storetype=None,start_hour=None,end_hour=None,custom_sort=None,converge=None,date_type='day'):
        if (table_list is None or storetype is None) and  gettotal is False:
            return False,'没有数据'
        self.storetype=storetype
        self.table_list=table_list
        self.index=index
        self.offset=offset
        self.order=order
        self.ordermetric=ordermetric
        self.custom_sort=custom_sort

        if not edate:
            edate = dt

        #筛选出自定义列的过滤条件
        addColumn_filter=[]
        addColumn_metric=[]
        if filter_str or search:
            filter_search=[]
            if filter_str:
               filter_search+=filter_str
            if search:
                filter_search+=search
                
            for gp in filter_search:
                col_tmp = gp.get("key", '').strip().split('.')
                conf_type = len(col_tmp)
                col = col_tmp[0] if len(col_tmp) == 1 else '_'.join(col_tmp)


                if conf_type==1 and  not  col in str_dim.split(','):
                    if not str_dim and 'all'==str(col).strip().lower():
                        break
                    addColumn_metric.append(col)
                    addColumn_filter.append(gp)

        status,data=self.__createSql(metric_conf,str_dim,metric,dt,edate,filter_str,search,start_hour=start_hour,end_hour=end_hour,converge=converge,date_type=date_type)
        dim_list=str_dim.split(',')

        if status == False:
            return False,data
        else:
            sql=data

        status,data=self.__createSql(metric_conf,str_dim,metric,dt,edate,filter_str=filter_str,start_hour=start_hour,end_hour=end_hour,converge=converge,date_type=date_type)
        nofilter_sql=data

        #list_addColumn=[{'expression':'sum(first_source->mob.channel_trade.gmv>>date,first_source)','name':'udctest'},{'expression':'sum(second_source->mob.channel_trade.gmv>>date,second_source)/mob.channel_trade.gmv','name':'udctest1'}];
        #list_addColumn=[{'expression':'sum(first_source->mob.channel_trade.gmv>>date,first_source)','name':'udctest'},{'expression':'sum(second_source->mob.channel_trade.gmv>>date,second_source)/mob.channel_second_access.s_uv','name':'udctest1'}];
        if list_addColumn:
            udf=['max','min','sum','avg','count']
            reg_udf='|'.join(udf)

            #r=re.compile(r'((%s)\((.*?)>>(.*?)\))'%reg_udf)
            r=re.compile(r'((%s)\((.*?)(>>.*)?\))'%reg_udf)

            allr=re.compile(r'((.*?)(\+|\-|\*|/|$)(?!>))')

            for dict_column in list_addColumn:
                str_result=''
                column_name=dict_column['name']
                addColumn=dict_column['expression']
                allresult=allr.findall(addColumn)
                result_str=''
                for all  in allresult:
                    #all  expression 、+-*/
                   # result_name= all[1].replace('.','_')
                    result_name= all[1]
                    dim_metric=[]

                    arg_func=''

                    result=[]

                    #匹配是否有聚合函数
                    tmp_result=r.findall(result_name)
                    if  tmp_result:

                        result=tmp_result[0]

                    if  result :
                        content=result[2]
                        if result[1].lower() not in udf:
                            return False,'不支持函数'.result[1]
                        else:
                            arg_func=result[1]
                        if not result[3]:
                            #默认时间聚合
                            arg_dim='cdate'
                            #return False,'请注明聚合维度,如date'
                        else:
                            if not result[3].strip().startswith('>>'):
                                return False,'请合法注明聚合维度,如>>date'
                            else:
                                arg_dim=result[3].strip()[2:].replace('date','cdate')
                        if  not result[2]:
                            return False,'请注明聚合指标'
                        else:
                            arg_metric=result[2]


                    else:
                        content=result_name
                    #content值除去了聚合函数之后的内容
                    dim_metric=content.split('->')
                    #是否是维度上翻(含有->)
                    if len(dim_metric)==2:
                        #roll up   example:'first_source,second_source->mob.channel_second_access.s_uv

                        try:
                            add_dim=dim_metric[0].split(',')
                            split_metric=dim_metric[1].split('.')
                            if len(split_metric)==4:
                                #跨项目
                                add_project=split_metric[0]
                                add_metric='.'.join(split_metric[1:])
                            else:
                                add_project=metric_conf
                                add_metric=dim_metric[1]

                        except:
                            import traceback
                            traceback.print_exc()
                            return  False,'非法表达式'

                        #跨项目检查是否数据在一个库里
                        if add_project != metric_conf:
                            object_mms_conf = MmsConf()
                            res = object_mms_conf.select(add_project)
                            mysql_db = res[0]['store_db']
                            object_mms_conf.close_connection()

                            if self.dbname != mysql_db:
                                return False, 'UDC查询暂时不支持跨库查询,项目名称：%s' % add_project

                        from mysql.QueryData import  QueryData
                        obj_query=QueryData()
                        table_list=obj_query.getTableList(add_project,dim_metric[0],dt,edate)
                        if not table_list:
                            return False,'新增列'+'项目'+add_project+'维度组合:'+dim_metric[0]+' 下的数据还未生成'
                        self.table_list=table_list

                        status,data=self.__createSql(add_project,dim_metric[0],add_metric,dt,edate,filter_str=filter_str,limitFlag=False,start_hour=start_hour,end_hour=end_hour,converge=converge,date_type=date_type)
                        #print  'create semi sql-------',data
                        if status == False:
                            return False,data
                        else:
                            add_sql=data
                        add_under_metric=add_metric.replace('.', '_')
                        rename_metric='_'.join(add_dim)+'_'+add_under_metric+'_'+column_name

                       # inter_dim = set(dim_list).intersection(add_dim)
                       # inter_dim.add('cdate')
                        suffix='1=1 '
                        add_dim.append('cdate')
                        for i in add_dim:
                            if i:
                                suffix+=' and  add_a.%s=add_b.%s '%(i,i)

                        sql='select add_a.*,(add_b.%s) as %s  from (%s) add_a left join  (%s) add_b on (%s)'%(add_under_metric,rename_metric,sql,add_sql,suffix)
                        nofilter_sql='select add_a.*,(add_b.%s) as %s  from (%s) add_a left join  (%s) add_b on (%s)'%(add_under_metric,rename_metric,nofilter_sql,add_sql,suffix)
                        arg_metric=rename_metric

                        result_name=rename_metric

                    #是否有聚合函数
                    if arg_func:
                            arg_metric=arg_metric.replace('.', '_')
                            rename_arg=arg_func+'_'+arg_metric+'_'+column_name
                            result_name=rename_arg
                            select_arg=''
                            suffix_arg=''
                            if arg_dim:
                                select_arg=arg_dim+','
                                suffix_arg=' group by %s'%(arg_dim)

                            arg_sql='select %s %s(%s) as  %s  from (%s) arg_tmp '%(select_arg,arg_func,arg_metric,rename_arg,nofilter_sql)
                            arg_sql+=suffix_arg

                            inter_dim =set(arg_dim.split(','))
                           # inter_dim.add('cdate')
                            suffix='1=1 '
                            for i in inter_dim:
                                if i:
                                    suffix+=' and  arg_a.%s=arg_b.%s '%(i,i)
                            sql='select arg_a.*,arg_b.%s from (%s) arg_a left join  (%s) arg_b on (%s)'%(rename_arg,sql,arg_sql,suffix)
                    result_name=result_name.replace('.','_')
                    result_str+=result_name
                    if all[2]:
                        result_str+=all[2]
                sql='select *,(%s) as `%s`  from (%s) table_result '%(result_str,column_name,sql)


        if addColumn_filter:
            status,ret = self.__getWhereStr(addColumn_filter,addColumn_metric,[])
            if status == False:
                return False,ret
            g_w, m_w=ret

            sql='select * from (%s) table_result where 1=1  %s '%(sql,g_w)

        underline_metric_list=metric.replace('.', '_').split(',')#下划线连接的key

        #新报表排序逻辑
        if custom_sort:
            tmp_sql_order=[]
            for c_s in custom_sort:
                order_col=c_s['key']
                order_type=c_s['order']
                tmp_order_str=''
                if 'date'==order_col:
                    tmp_order_str='cdate '+order_type
                elif 'minute'==order_col:
                    tmp_order_str='cast(hour as unsigned) '+order_type+' '+'cast(minute as unsigned) '+order_type
                elif 'hour'==order_col:
                    tmp_order_str='cast(hour as unsigned) '+order_type
                else:
                    tmp_order_str=order_col+' '+order_type
                if not converge or (converge and 'date'!=order_col):
                    tmp_sql_order.append(tmp_order_str)

            if len(tmp_sql_order)>0:
                tmp_sql_order_str=",".join(tmp_sql_order)
                sql+=" order by %s "%(tmp_sql_order_str)

        if order>0 and not custom_sort:
            int2str={1:'asc',2:'desc'}
            '''
            if  ordermetric == 'cdate':
                ordermetric += ' '+int2str[(int)(order)]+','+underline_metric_list[0]
            '''
            if ordermetric == 'hour':
                ordermetric='cast(hour as unsigned)'
            if ordermetric == 'minute':
                ordermetric='cast(hour as unsigned) '+int2str[(int)(order)]+',cast(minute as unsigned)'

            #特殊处理多个指标排序
            if isinstance(ordermetric,list):
                order_tmp=[]
                for i in ordermetric:
                    if 'date'==i:
                        order_tmp.append('cdate')
                    elif 'minute'==i:
                        order_tmp.append('cast(minute as unsigned)')
                    elif 'hour'==i:
                        order_tmp.append('cast(hour as unsigned)')
                    else:
                        order_tmp.append(i)
                join_str=' '+int2str[(int)(order)]+','
                ordermetric=join_str.join(order_tmp)

            if ordermetric!='':
                sql += " order by %s  %s"%( ordermetric,int2str[(int)(order)])


        if gettotal==True:
            sql='select count(1) from ('+sql+')a'
        elif index!='' and offset!='' and ((order>0  and ordermetric!='') or custom_sort):
            sql+= ' limit %s,%s'%(int(index)*int(offset),int(offset))

        starttime = time.time()
        try:
            self.cur.execute('set autocommit=1;')
            if converge is not None:
                self.cur.execute('set session sort_buffer_size= %s' % str(conf.UPDATE_MYSQL_SORT_BUFFER_SIZE))
            self.cur.execute(sql)

        except MySQLdb.Error as e:
            print 'search sql:'+sql + '\n'
            return False,'报表配置有问题，请联系相关数据工程师:'+str(e)
        endtime = time.time()
        spend_time=str(endtime-starttime)

        if  not gettotal==True:
            print 'spend_time:'+spend_time+'  search sql:'+sql + '\n'
        else:
            print 'spend_time:'+spend_time+'  count sql:'+sql + '\n'

        ret = []
        if getkey == False:
            for row in self.cur:
                ret.append(row)
        else:
            columns = self.cur.description
            tmp = dict()
            for value in self.cur.fetchall():
                tmp = {}
                for (index, column) in enumerate(value):
                    tmp[columns[index][0]] = column
                ret.append(tmp)
        return True,ret

def cmdArgsDef():
    #python query.py -p mob_app_metric -d 2014-09-21 -q client_device,client_version=5.0.0,poster_name -m user.poster_access.poster_access_uv,user.poster_access.poster_access_pv,wap.poster_access.poster_access_uv,wap.poster_access.poster_access_pv

    arg_parser = argparse.ArgumentParser()
    arg_parser.add_argument('-c', '--project', help='project name, i.e. mob_app_metric.', required=True)
    arg_parser.add_argument('-d', '--date',
                            help='[Required] Which date\'s data are going to be calculated. And the format must be "YYYY-MM-DD".',
                            required=True)
    arg_parser.add_argument('-g', '--group',
                            help='the dim need to show in query, i.e. client_device,client_version=5.0.0.',
                            required=True)
    arg_parser.add_argument('-m', '--metric',
                            help='metrics to show in query, i.e. wap.poster_access.poster_access_uv,user.poster_access.poster_access_uv.',
                            required=True)

    arg_parser.add_argument('-t', '--test', action='store_true',
                            help='[Optional] Will use test hql to execute. But the HQL generator may not implement a test HQL.')
    args = arg_parser.parse_args()
    return args


if __name__ == "__main__":

    #
    arg = cmdArgsDef()

    if arg.test == True:
        conf.ENTRY_TABLE = conf.TEST_ENTRY_TABLE
        conf.TABLE_PREFIX = conf.TEST_TABLE_PREFIX
    else:
        conf.ENTRY_TABLE = conf.PRODUCT_ENTRY_TABLE
        conf.TABLE_PREFIX = conf.PRODUCT_TABLE_PREFIX

    q = Query()
    ret = q.getResult(arg.date, arg.project, arg.group, arg.metric, list_addColumn={'name':'test', 'cn_name':'test', 'explain':'test', 'expression':'first_source->higo_h5_data.mob.channel_second_access.s_uv'})
    if ret == 'no_table':
        print 'Get no table, -g input is invalid.'
        exit(-1)

    if ret == 'bad_metric':
        print '-m input is wrong.'
        exit(-1)

    if ret == 'bad_filter_column':
        print 'filter input column name is wrong'
        exit(-1)
    if ret == 'op_not_support':
        print 'filter input op is not support yet'
        exit(-1)
    if ret == 'filter_value_error':
        print 'filter input val type is invalid(<,<=,>,>= must float or int)'
        exit(-1)

    for r in ret:
        print '\t'.join(map(str, r))

