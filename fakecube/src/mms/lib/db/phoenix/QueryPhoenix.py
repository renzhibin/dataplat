#!/usr/bin/env python2.7
# coding=utf-8

from PhoenixBase import PhoenixBase
from mysql.QueryData import QueryData
import time

class QueryPhoenix(QueryData,PhoenixBase):
    def __init__(self):
        pass
    def __del__(self):
        pass




    def getResult(self, dt, metric_conf, str_dim, metric, order=0, ordermetric='', getkey=False, edate='', index=0, offset=100000,
                  filter_str='',gettotal=False,list_addColumn=None,search=None,table_list=None,storetype=None):
        if gettotal==True:
            return True,{0:{0:10000000}}
        if not edate:
            edate = dt
        table_name=table_list[0]
        res=self.getPhoenix(table_name,'schema')
        schema=res['data']
        status,sql=self.createSql(table_name,schema,str_dim,metric,dt,edate,filter_str,search,False)
        #phoenix不开放默认排序



        if isinstance(ordermetric,list):
            int2str={1:'asc',2:'desc'}

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


        index=int(index)
        offset=int(offset)
        #不支持limit start,end
        limitnum= (int(index)+1)*int(offset);
        sql+= ' limit %s'%limitnum




        start=index*offset
        end=(index+1)*offset
        cnt=0
        starttime = time.time()
        try:
            res=self.getPhoenix(sql)
        except Exception as e:
            print 'search sql:'+sql + '\n'
            return False,'phoenix错误'+str(e)
        endtime = time.time()
        spend_time=str(endtime-starttime)
        retu=[]
        for v in res['data']:
            cnt+=1
            if not (cnt>start and  cnt<=end):
                continue

            retu.append(v)





        return True,retu


