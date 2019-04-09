#!/usr/bin/env python2.7
#coding=utf-8
import re
import datetime
import calendar
import env as conf
from log4me import MyLogger
from mms_conf import MmsConf
from LocalToMysqlBase import LocalToMysql

logger = MyLogger.getLogger()

'''
    处理项目 storetype=5
'''
class LocalToMysqlStoreType3(LocalToMysql):

    def init(self):
        LocalToMysql.init(self)

        self.needDeleteDataTable={}
        self.updateRangeTimeTable={}

    def getRangeExistsTables(self, suffix_s, suffix_e):

        tmp_suffix_e=datetime.datetime.strptime(suffix_e,'%Y%m')
        suffix_e_day=calendar.monthrange(tmp_suffix_e.year,tmp_suffix_e.month)
        suffix_e_day=suffix_e_day[1]
        suffix_e_day='{}{}'.format(str(suffix_e),str(suffix_e_day))

        suffix_s_day='{}{}'.format(str(suffix_s),'01')

        mmsMysql = self.meta_mmsMysql_slave
        meta_conn=mmsMysql.get_conn()
        meta_cur=mmsMysql.get_cur(mmsMysql.DICTCURSOR_MODE)

        tables = {}
        metric = self.metric_conf
        sql = """select group_keys,table_name,id,start_time,end_time
               from %s where metric_conf = '%s'  and storetype='%s' and schedule_level='%s'
               """ % (conf.ENTRY_TABLE,metric,self.storetype,self.schedule_level)

        # sql+= ''' and (start_time>='%s' or end_time<='%s') '''% (suffix_s_day, suffix_e_day)
        logger.info("table start start_time:"+ suffix_s_day + " end_time:" + suffix_e_day)
        tables={}
        meta_cur.execute(sql)
        for e in meta_cur.fetchall():
            if not tables.has_key(e['group_keys']):
                tables[e['group_keys']]=[]
            tables[e['group_keys']].append(e)
        meta_conn.close()
        use_tables=[]
        for k,v in tables.items():
            v=sorted(v,key=lambda x:x['id'],reverse=True)
            max_row=v[0]
            if not self.checkTableRowsAndSize(max_row['table_name']):
                use_tables.append(max_row)
                self.needDeleteDataTable[max_row['table_name']]=k
            filtered_tables=filter(lambda x:(suffix_s_day<=x['start_time'] and suffix_e_day>=x['start_time']) or (suffix_s_day>=x['start_time'] and suffix_e_day<=x['end_time']) or (suffix_s_day<=x['start_time'] and suffix_e_day>=x['end_time']),v)
            map(lambda x:self.needDeleteDataTable.setdefault(x['table_name'],k),filtered_tables)

        return use_tables

    def getExistsTablesCustomTime(self, suffix=None,custom_time=None):
        mmsMysql = self.meta_mmsMysql_slave
        meta_conn=mmsMysql.get_conn()
        meta_cur=mmsMysql.get_cur(mmsMysql.DICTCURSOR_MODE)

        tables = {}
        metric = self.metric_conf
        cat = self.cat
        group_name = self.group["name"]
        sql = """select group_keys,table_name,id,start_time,end_time
               from %s where metric_conf = '%s'  and storetype='%s' and schedule_level='%s'
               """ % (conf.ENTRY_TABLE,metric,self.storetype,self.schedule_level)
        # sql+= ''' and suffix='%s' '''% suffix

        meta_cur.execute(sql)

        for e in meta_cur.fetchall():
            if not tables.has_key(e['group_keys']):
                tables[e['group_keys']]=[]
            tables[e['group_keys']].append(e)
        meta_conn.close()

        use_tables={}
        for k,v in tables.items():
            v=sorted(v,key=lambda x:x['id'],reverse=True)
            max_row=v[0]
            if not self.checkTableRowsAndSize(max_row['table_name']):
                table_key=','.join((suffix,max_row['group_keys']))
                use_tables[table_key]=max_row['table_name']

                update_table_key='-'.join((max_row['group_keys'],max_row['table_name']))
                self.updateRangeTimeTable[update_table_key]={'group_keys':max_row['group_keys'],'table_name':max_row['table_name'],'start_time':max_row['start_time'],'end_time':max_row['end_time']}

        return use_tables

    def getExistsTables(self):
        '''
            1，同一维度组合中ID最大
            2，表数据行数和大小没超过阀值
            初始化需要删除数据的表
        '''
        mmsMysql = self.meta_mmsMysql_slave
        meta_conn=mmsMysql.get_conn()
        meta_cur=mmsMysql.get_cur()

        tables = {}
        metric = self.metric_conf
        cat = self.cat
        group_name = self.group["name"]
        sql = """select group_keys,table_name,id,start_time,end_time
               from %s where metric_conf = '%s'  and storetype='%s' and schedule_level='%s'
               """ % (conf.ENTRY_TABLE,metric,self.storetype,self.schedule_level)

        # sql+= ''' and suffix='%s' '''% self.storetype2suffix

        meta_cur.execute(sql)
        for row in meta_cur:
            tmp_row={}
            tmp_row['table_name']=row[1]
            tmp_row['id']=row[2]
            #TODO
            tmp_row['start_time']=row[3]
            tmp_row['end_time']=row[4]
            if not tables.has_key(row[0]):
                tables[row[0]]=[]
            tables[row[0]].append(tmp_row)
        meta_conn.close()

        use_tables={}
        for k,v in tables.items():
            v=sorted(v,key=lambda x:x['id'],reverse=True)
            max_row=v[0]
            tmp_dt=self.dt.replace('-','')
            if not self.checkTableRowsAndSize(max_row['table_name']):
                use_tables[k]=max_row['table_name']
                #TODO 更新索引表中的开时间和结束时间
                tmp_key='_'.join((k,max_row['table_name']))
                tmp_start_time=max_row['start_time']
                tmp_end_time=max_row['end_time']
                if tmp_dt<tmp_start_time:
                    tmp_start_time=tmp_dt
                if tmp_dt>tmp_end_time:
                    tmp_end_time=tmp_dt
                self.updateRangeTimeTable[tmp_key]={'group_keys':k,'table_name':max_row['table_name'],'start_time':tmp_start_time,'end_time':tmp_end_time}
                # self.updateTableDataRangeTime(k,max_row['table_name'],tmp_dt,max_row['start_time'],max_row['end_time'])
                self.needDeleteDataTable[max_row['table_name']]=k
            filtered_tables=filter(lambda x:tmp_dt>=x['start_time'] and tmp_dt<=x['end_time'],v)
            map(lambda x:self.needDeleteDataTable.setdefault(x['table_name'],k),filtered_tables)

        return use_tables

    def createTable4NewGroup(self,dims,i, tab_suffix=None,custom_time=None):

        if tab_suffix is None:
            tab_suffix = self.storetype2suffix

        meta_mmsMysql = self.meta_mmsMysql
        meta_conn=meta_mmsMysql.get_conn()
        meta_cur=meta_mmsMysql.get_cur()

        data_mmsMysql = self.data_mmsMysql
        data_conn=data_mmsMysql.get_conn()
        data_cur=data_mmsMysql.get_cur()

        dims_mms_conf=MmsConf()
        dimensions_inf=dims_mms_conf.get_app_dimensions_params(self.metric_conf)
        dimensions2type={}
        dim_type_dict={}
        for k,v in dimensions_inf.items():
            if v.has_key('type') and v['type']:
                dim_type=v['type']
                if self.type_dict.has_key(dim_type) and self.type_dict[dim_type]:
                    dimensions2type[k]=self.type_dict[dim_type]
                    dim_type_dict[k]=dim_type
        #id 表名后缀自增id
        i=self.getExistTableCount()
        id = i + 1
        dim_str = ",".join(dims)
        storetype=self.storetype

        func=lambda x:str(dimensions2type[x]) if dimensions2type.has_key(x) else str(self.type_dict['varchar'])

        table = conf.TABLE_PREFIX +"v2_" +"_".join(map(str,[self.metric_conf,id,tab_suffix]))

        if 'minute'==self.schedule_level or 'hour'==self.schedule_level:
            table = conf.TABLE_PREFIX +"v2_"+self.schedule_level+"_" +"_".join(map(str,[self.metric_conf,id,tab_suffix]))

        column=''
        #增加唯一索引列fakecube_unique,值为所有维度md5
        uk = "`fakecube_unique`"
        index_key="`cdate`,"
        #索引最大长度3072 bytes 对应到utf8mb4最大是768个字符 当有7个以上维度时没法直接建立索引

        tmp_dims=[]
        for i in dims:
            i=i.strip()
            if i!="":
                if (dim_type_dict.has_key(i) and dim_type_dict[i]!='decimal') or not dim_type_dict.has_key(i):
                    tmp_dims.append(i)

        prefix_index=100
        if len(tmp_dims)>7 and len(tmp_dims)<15:
            prefix_index=(768-10)/len(tmp_dims)

        if len(tmp_dims)>=15:
            prefix_index=(768-10)/15

        for i in range(0,len(tmp_dims)):
            if i<=14:
                index_key+="`" + tmp_dims[i] + "`(" + str(prefix_index)+"),"

        index_key=index_key.strip(',')

        for i in dims:
            i=i.strip()
            if i!="":
                ns = "`" + i + "`" + " %s,"%(func(i))
                us = "`" + i + "`" + ","
                column = column + ns

        uk=uk.strip(',')
        column=column.strip(',')
        if column:
            column +=','

        #分钟级别添加hour minute
        schedule_filed=''
        if 'minute'==self.schedule_level or 'hour'==self.schedule_level:
            schedule_filed='hour varchar(10),minute varchar(10),'
            uk=uk+',`hour`,`minute`'
        create_sql = """create table if not exists %s (
                   `id`  bigint(20)  NOT NULL auto_increment COMMENT '主键',
                   `fakecube_unique` varchar(100) ,
                   cdate varchar(10),
                   %s
                   %s
                   PRIMARY KEY  (`id`),
                   UNIQUE(%s),
                   KEY (%s)
                   )
                   ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='table';""" % (table,column,schedule_filed,uk,index_key)

        logger.debug('create sql'+create_sql)
        data_cur.execute(create_sql)
        data_conn.commit()

        data_mmsMysql.conn_close()

        tmp_dt=self.dt.replace('-','')
        if custom_time:
            tmp_dt=custom_time.replace('-','')
            if len(tmp_dt)==6:
                tmp_dt='{}{}'.format(str(tmp_dt),'01')
        insert_sql = "insert into "+conf.ENTRY_TABLE+" (metric_conf,group_keys,table_name,storetype,suffix,schedule_level,start_time,end_time) values(%s,%s,%s,%s,%s,%s,%s,%s);"
        logger.debug('insert entry sql'+insert_sql+str((self.metric_conf,dim_str,table,str(storetype),tab_suffix)))

        meta_cur.execute(insert_sql,(self.metric_conf,dim_str,table,str(storetype),tab_suffix,self.schedule_level,tmp_dt,tmp_dt))
        meta_conn.commit()
        meta_mmsMysql.conn_close()
        tmp_dim_table='-'.join((dim_str,table))
        self.updateRangeTimeTable[tmp_dim_table]={'group_keys':dim_str,'table_name':table,'start_time':tmp_dt,'end_time':tmp_dt}
        return table


    def deleteExistsData(self):

        data_mmsMysql = self.data_mmsMysql
        data_conn=data_mmsMysql.get_conn()
        data_cur=data_mmsMysql.get_cur()
        tables = []

        # tables=self.table2dim.keys()
        groups_dims=self.getGroupSetStrList()
        for t,d in self.needDeleteDataTable.items():
            if d in groups_dims:
                tables.append(t)
        print tables
        cdate=self.dt
        cat = self.cat
        group_name = self.group["name"]
        storetype=self.storetype

        #项目user_access_count删除特殊处理
        if self.metric_conf=='user_access_count':
            for table_name in tables:
                sql='truncate table %s'%(table_name)
                data_cur.execute(sql)
                data_conn.commit()
        else:
            #删除某些数据
            for table_name in tables:
                met_list=[]

                not_metric_met=self.needDeleteDataTable[table_name].split(',')
                not_metric_met.append('id')
                not_metric_met.append('fakecube_unique')
                not_metric_met.append('cdate')
                if 'minute'==self.schedule_level or 'hour'==self.schedule_level:
                    not_metric_met.append('hour')
                    not_metric_met.append('minute')
                not_metric_met += self.met_metric_list

                sql="show columns from %s"%table_name
                data_cur.execute(sql)
                realy_colums=[]
                for value in data_cur.fetchall():
                    realy_colums.append(value[0].lower())
                    if not value[0].lower() in not_metric_met:
                        met_list.append(value[0].lower())
                and_hour_sql=''
                if 'minute'==self.schedule_level or 'hour'==self.schedule_level:
                    and_hour_sql=" and hour='"+self.stat_hour+"' and minute='"+self.stat_minute+"' "

                where_cdate =''
                if self.custom_cdate ==1:
                    cdate_custom_start, cdate_custom_end = self.getCustomTime()
                    if cdate_custom_start == cdate_custom_end:
                        where_cdate="cdate='%s'" % cdate_custom_start
                        logger.info("delete existed data in %s custom time: cdate='%s'" % (table_name, cdate_custom_start))
                    else:
                        where_cdate = "cdate>='%s' and cdate<='%s'" % (cdate_custom_start, cdate_custom_end)
                        logger.info("delete existed date in %s custom time: cdate>='%s' and cdate<='%s'" % (table_name, cdate_custom_start, cdate_custom_end))
                else:
                    where_cdate=" cdate='%s' "%(self.dt)
                    if self.group.has_key('schedule_interval') and self.group['schedule_interval']:
                        #如果是月截取出月份
                        r = re.compile(r'^(\d+)(_(\d+)+)?$')
                        res=r.findall(str(self.group['schedule_interval']))
                        if res:
                            res=res[0]
                            func=lambda x:str(x) if len(str(x))==2 else str(0)+str(x)
                            if int(res[0])==30:
                                tmp_date=datetime.datetime.strptime(self.dt,'%Y-%m-%d')
                                tmp,month_days=calendar.monthrange(tmp_date.year,tmp_date.month)
                                cdate_start='%s-%s-%s'%(tmp_date.year,func(tmp_date.month),'01')
                                cdate_end='%s-%s-%s'%(tmp_date.year,func(tmp_date.month),str(month_days))
                                where_cdate=" cdate>='%s' and cdate<='%s' "%(cdate_start,cdate_end)
                            if int(res[0])==7:
                                tmp_date=datetime.datetime.strptime(self.dt,'%Y-%m-%d')
                                cdate_start=tmp_date-datetime.timedelta(days=tmp_date.weekday())
                                cdate_start=cdate_start.strftime('%Y-%m-%d')
                                where_cdate=" cdate>='%s' and cdate<='%s' "%(cdate_start,self.dt)


                #删除控制条数设为3000
                where_sql = " where %s" % (where_cdate)
                where_sql += and_hour_sql
                for value in met_list:
                    where_sql+= ' and `'+value+ '` is null'

                delete_sql="delete from "+ table_name+ where_sql + ' limit %s' % conf.UD_CONTROL_NUMBER
                logger.info("delete sql: %s" % delete_sql)
                d_count = 0
                while True:
                    data_cur.execute(delete_sql)
                    data_conn.commit()
                    affect_nr = data_cur.rowcount
                    logger.info("delete time= %s, affect_nr=%s" % (d_count, affect_nr))
                    if affect_nr ==0:
                        logger.info("finish the delete process")
                        break
                    d_count +=1

                update_sql='update '+table_name+' set '
                is_update=False
                need_update_metric_list=[]
                for value in self.met_metric_list:
                    if value in realy_colums:
                        need_update_metric_list.append(value)
                        update_sql+=value+'=null,'

                update_sql=update_sql.strip(',')
                update_sql+= " where %s "%(where_cdate)
                update_sql+=and_hour_sql
                nr_met_metric_list = len(need_update_metric_list)
                if nr_met_metric_list !=0:
                    update_sql += 'and ('
                    for index, value in enumerate(need_update_metric_list):
                        if index == nr_met_metric_list-1:
                            update_sql+= value + ' is not null'
                        else:
                            update_sql += value+ ' is not null or '
                    update_sql += ' )'

                update_sql+= ' limit %s' % conf.UD_CONTROL_NUMBER
                if nr_met_metric_list>0:
                    is_update=True
                    logger.info('update sql: %s' % update_sql)
                u_count = 0

                while is_update:
                    data_cur.execute(update_sql)
                    data_conn.commit()
                    affect_nr = data_cur.rowcount
                    logger.info("update time= %s, affect_nr=%s" % (u_count, affect_nr))
                    if affect_nr ==0:
                        logger.info("finish the update process")
                        break
                    u_count +=1


        data_mmsMysql.conn_close()

        logger.info('delete data succ')


    def updateTableDataRangeTime(self,tableTime):
        try:
                meta_mmsMysql = self.meta_mmsMysql
                meta_conn=meta_mmsMysql.get_conn()
                meta_cur=meta_mmsMysql.get_cur()
                sql='''
                    update {} set start_time='{}',end_time='{}' where metric_conf='{}' and group_keys='{}' and `table_name`='{}'
                '''.format(conf.ENTRY_TABLE,tableTime['start_time'],tableTime['end_time'],self.metric_conf,tableTime['group_keys'],tableTime['table_name'])
                meta_cur.execute(sql)
                meta_conn.commit()
        except:
            logger.error('update table data range time error')
            import traceback
            traceback.print_exc()
            raise  Exception


    def getExistTableCount(self):
        mmsMysql = self.meta_mmsMysql_slave
        meta_conn=mmsMysql.get_conn()
        meta_cur=mmsMysql.get_cur()

        metric = self.metric_conf
        sql = """select group_keys,table_name,id,start_time,end_time
               from %s where metric_conf = '%s'  and storetype='%s'
               """ % (conf.ENTRY_TABLE,metric,self.storetype)
        count=0
        meta_cur.execute(sql)
        count=len(meta_cur.fetchall())
        meta_conn.close()
        return count

    def getGroupSetStrList(self):
        dims=[]
        for i in self.group["dim_sets"]:
            dims_str = i["name"].lower().strip("()")
            tmp_arr = dims_str.split(",")
            dim_arr = []
            for j in tmp_arr:
                dim_arr.append(j.strip().lower())
            dim_arr.sort()
            dim_str = ",".join(dim_arr)
            dims.append(dim_str)
        return dims