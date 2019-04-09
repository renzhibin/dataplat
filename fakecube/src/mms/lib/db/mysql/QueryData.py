#!/usr/bin/env python2.7
# coding=utf-8
import os,sys
import env as conf
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql


class QueryData():
    def __init__(self, tunnel_port=-1, webapi_tag=False):
        #for webapi
        if webapi_tag == True:
            conf.ENTRY_TABLE = conf.PRODUCT_ENTRY_TABLE
            conf.TABLE_PREFIX = conf.PRODUCT_TABLE_PREFIX




        self.table_list = ''
        self.storetype = ''

    def __del__(self):
        if self.cur:
            self.cur.close()
        if self.conn:
            self.conn.close()
    def op_map(self):
        a={
            'like': lambda c, v: ' '.join((c, 'like', '\'%' + str(v) + '%\'')),
            'not like': lambda c, v: ' '.join((c, 'not like', '"%' + str(v) + '%"')),
            'start with': lambda c, v: ' '.join((c, 'like', '\'' + str(v) + '%"')),
            'end with': lambda c, v: ' '.join((c, 'like', '"%' + str(v) + '\'')),
            '=': lambda c, v: ' '.join((c, '=', '\'' + str(v) + '\'')),
            '!=': lambda c, v: ' '.join((c, '!=', '\'' + str(v) + '\'')),
            '>': lambda c, v: ' '.join((c, '>', str(float(v)))),
            '>=': lambda c, v: ' '.join((c, '>=', str(float(v)))),
            '<': lambda c, v: ' '.join((c, '<', str(float(v)))),
            '<=': lambda c, v: ' '.join((c, '<=', str(float(v)))),
            'REGEXP': lambda c, v: ' '.join((c, 'REGEXP', '\'' + str(v) + '\'')),
            'NOT REGEXP': lambda c, v: ' '.join((c, 'NOT REGEXP', '\'' + str(v) + '\'')),
            'IS NULL': lambda c, v: c + ' IS NULL',
            'IS NOT NULL': lambda c, v: c + ' IS NOT NULL',
            'IS EMPTY': lambda c, v: ' '.join(('(', c, 'IS NULL or', c, "==''")),
            'IS NOT EMPTY': lambda c, v: ' '.join(('(', c, 'IS NOT NULL or', c, "!=''")),
            'in': lambda c, v: c + ' in (' + v + ')',
            'not in': lambda c, v: c + ' not in (' + v + ')',
            'between': lambda c, v: c + ' between ' + v[0] + ' and ' + v[1]
        }
        return  a
    def __getGroupKeys(self, qs):

        a = qs.strip().split(",")

        groups = []

        for i in a:
            j = i.split("=")

            groups.append(j[0].strip())

        groups.sort()

        #gks = ",".join(groups)

        return groups



    def getTableList(self, metric_conf, qs, dt=None, edate=None,store_type=None):

        import time

        start_suffix = time.strftime('%Y%m', time.strptime(dt, "%Y-%m-%d"))

        end_suffix = time.strftime('%Y%m', time.strptime(edate, "%Y-%m-%d"))

        gp = self.__getGroupKeys(qs)

        schedule_interval_minute=False
        schedule_interval_hour=False
        if 'hour' in gp:
            gp.remove('hour')
            schedule_interval_hour=True
        if 'minute' in gp:
            gp.remove('minute')
            schedule_interval_minute=True

        gks = ",".join(gp)


        table = []
        sql = """select table_name,storetype,suffix,start_time,end_time from %s

        where metric_conf = \"%s\"
	         and group_keys = \"%s\"

        """ % (conf.ENTRY_TABLE, metric_conf, gks)
        if store_type is not None:
            sql +=' and storetype='+str(store_type)
        if schedule_interval_hour and schedule_interval_minute:
            sql +=" and schedule_level='minute' "
        if schedule_interval_hour and not schedule_interval_minute:
            sql +=" and schedule_level='hour' "
        if not schedule_interval_minute and not schedule_interval_hour:
            sql +=" and schedule_level='day' "
        mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        self.cur=mmsMysql.get_cur()
        self.conn=mmsMysql.get_conn()
        self.cur.execute(sql)

        for row in self.cur:

           # print row
            self.storetype = row[1]
            if int(self.storetype)==int(5):
                s_dt=str(dt).replace('-','')
                e_dt=str(edate).replace('-','')
                if (row[3]<=s_dt and row[4]>=s_dt) or (row[3]<=e_dt and row[4]>=e_dt):
                    table.append(row[0])
            else:
                suffix = row[2]

                if suffix is None or  suffix=='' or ( suffix >= start_suffix and suffix <= end_suffix):
                    table.append(row[0])
        print 'entry sql:'+sql+' the result table :'+str(table)
        return table
    def __getWhereStr(self, filter_cond,dim,metric):

        g_conf = ""
        m_conf = ""
        if not filter_cond:
            return True,(g_conf, m_conf)
        for gb in filter_cond:
            print gb
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

            op_func = self.op_map().get(op)
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


    def createSql(self,table,schema,str_dim,metric, dt, edate='', filter_str='',search=None,metricNullFlag=True):

        underline_metric_list=metric.lower().replace('.', '_').split(',')#下划线连接的key


        #兼容dim的老逻辑
        dim_list = str_dim.strip().split(",")
        query_cols_name = ','.join(dim_list) + ','
        if query_cols_name.strip()==',':
            query_cols_name=''

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

        wgm_conf=wg_conf+' and '+wm_conf
        sql=''

        select_str=''
        print 'schmea-----',schema
        for m in underline_metric_list:
            if m in schema:
                select_str += m+','

            else:
                select_str += ' null as %s,'%(m)


        select_str = select_str[:-1]

          #null值删除
        wnull_conf='1!=1'
        if metricNullFlag is True:
            for m in underline_metric_list:
                if m in schema:
                    wnull_conf +=' or '+m+' is not null '



        sql += """
            select cdate, %s %s
            from %s
            where %s and cdate>='%s' and cdate<='%s' and (%s)
         """ % (query_cols_name, select_str,
           table,
           wgm_conf, dt, edate,wnull_conf)









      #  sql='select * from ('+sql+')b where '+wnull_conf

        return True,sql
