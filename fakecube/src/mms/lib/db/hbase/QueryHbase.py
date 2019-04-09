#!/usr/bin/env python2.7
# coding=utf-8

import happybase


class QueryHbase():
    def __init__(self):
        #self.connection = happybase.Connection(host='127.0.0.1',port=47625,transport='framed',table_prefix='fakecube')
        #ssh -f zhibinren@osys11.meilishuo.com -L 47625:10.6.0.85:30000 -N
        self.connection=happybase.Connection(host='10.6.0.85',port=30000,transport='framed',table_prefix='fakecube')






    def getResult(self, dt, metric_conf, str_dim, metric, order=0, ordermetric='', getkey=False, edate='', index=0, offset=100000,
                  filter_str='',gettotal=False,list_addColumn=None,search=None,table_list=None,storetype=None):
        if gettotal==True:
            return True,{0:{0:10000000}}
        if not edate:
            edate = dt
        filter=None
        if search:
            filter_content=str(search[0]['val'][0])
            filter="RowFilter(=, 'regexstring:.*%s.*')"%(filter_content)
            print filter


        index=int(index)
        offset=int(offset)
        table=self.connection.table(table_list[0])
        #under_dt=time.strftime('%Y_%m_%d',time.strptime(dt,'%Y-%m-%d'))
        #under_edate=time.strftime('%Y_%m_%d',time.strptime(edate,'%Y-%m-%d'))
        res=table.scan(row_start=dt,row_stop=self.str_increment(edate),limit=(int(index)+1)*int(offset),
            filter=filter)
        dim_arr=str_dim.split(',')
        dim_arr.sort()
        #dim_arr.insert(0,'date')
        retu=list()
        cnt=-1
        start=index*offset
        end=(index+1)*offset

        for k, v in res:
            cnt+=1
            if not (cnt>=start and  cnt<=end):
                continue
            tmp_retu=dict()
            tmp_k=k.split('|')
            #tmp_retu['date']='-'.join(tmp_k[0:3])
            tmp_retu['date']=tmp_k[0]
            for index in range(1,len(tmp_k)):

                tmp_retu[dim_arr[index-1]]=tmp_k[index]
            for mk,mv in v.items():
                fk,rk=mk.split(':')
                tmp_retu[rk]=mv
            retu.append(tmp_retu)

        #pprint(retu)

        return True,retu


    def str_increment(self,s):
        for i in xrange(len(s) - 1, -1, -1):
            if s[i] != '\xff':
                return s[:i] + chr(ord(s[i]) + 1)
        return None