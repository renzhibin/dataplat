#!/usr/bin/env python2.7
#coding=utf-8

import time
from abc import abstractmethod
import subprocess
import env as conf
from lock import SingletonLock
from log4me import MyLogger
from utils import err_json
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql

BLACKHOLE=open('/dev/null')
logger = MyLogger.getLogger()
class HandleData(object):

    def __init__(self,dt,metric_conf,cat,group,storetype,hql,log_file,hql_type=1,schedule_level='day',stat_hour='0',stat_minute='0', exec_type='all'):

        self.metric_conf = metric_conf
        self.project=metric_conf
        self.cat = cat
        self.group = group
        self.dt = dt
        self.storetype=storetype
        self.hql_type=hql_type
        self.table2dim=dict()
        self.metric_list=[]
        self.met_metric_list=[]
        self.schedule_level=schedule_level#minute,hour,day
        self.storetype2suffix=time.strftime('%Y%m',time.strptime(self.dt,"%Y-%m-%d"))
        self.custom_time_suffix = []
        self.stat_hour=stat_hour
        self.stat_minute=stat_minute
        self.type_dict={'varchar':'varchar(100) binary','decimal':'DECIMAL(64,2)','varchar200':'varchar(200) binary','varchar1024':'varchar(1024) binary'}
        self.custom_cdate = 0
        self.big_result = False
        self.hive_result_file = ''
        self.hive_result_nr = 0
        self.dict_file = {}
        self.exec_type = exec_type

        if self.group.has_key('custom_cdate'):
            self.custom_cdate = int(self.group['custom_cdate'])

        if self.group.has_key('hql_type'):
                self.hql_type=self.group['hql_type']
        if cat and group.has_key("metrics"):
            self.lowercat=cat.lower()
            self.lowerhqlname=self.group["name"].lower()

            self.metric_prefix= self.cat.lower()+"_"+self.group["name"].lower()+"_"
            for i  in self.group["metrics"]:
                name=i['name'].lower()
                self.metric_list.append(name)
                name=self.metric_prefix+name
                self.met_metric_list.append(name)
        self.exists_tables=dict()
        self.hql=hql
        self.log_file=log_file
        self.init()
    def init(self):
        pass
    def getHql(self):
        return self.hql

    def getExistsTables(self):
        tables = {}
        metric = self.metric_conf
        cat = self.cat
        group_name = self.group["name"]
        sql = """select group_keys,table_name
               from %s where metric_conf = '%s'  and storetype='%s'
               """ % (conf.ENTRY_TABLE,metric,self.storetype)

        mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        meta_conn=mmsMysql.get_conn()
        meta_cur=mmsMysql.get_cur()
        meta_cur.execute(sql)
        for row in meta_cur:
            tables[row[0]] = row[1]

        mmsMysql.conn_close()
        return tables


    #建表和插入入口记录
    @abstractmethod
    def createTable4NewGroup(self,dims,i,tab_suffix=None,custom_time=None):
        table_name=''
        return table_name


    @abstractmethod
    def deleteExistsData(self):
        return  True

    def alterNewCol(self):
        pass
    @abstractmethod
    def loadData(self):
        num=0
        return num

    def  execute(self,log_file_id):
        self.log_file_id=log_file_id
        #check hive result first
        self.hive_result_file = conf.OUTPUT_PATH +'.'.join((self.dt,self.metric_conf, self.cat, self.group["name"]))
        if 'minute'==self.schedule_level or 'hour'==self.schedule_level:
            self.hive_result_file=conf.OUTPUT_PATH+'.'.join((self.dt,self.stat_hour,self.stat_minute,self.metric_conf, self.cat, self.group["name"]))
        wc_cmd='cat %s |wc -l'%(self.hive_result_file)

        self.hive_result_nr=int(subprocess.check_output(wc_cmd,stderr=BLACKHOLE,shell=True).strip())
        if self.hive_result_nr > int(conf.ALTER_FILE_NUM):
            self.big_result = True

        #lock
        lock_file = conf.LOCK_PATH + self.metric_conf
        l = SingletonLock(lock_file)
        l.ex_lock()

        logger.info("%s.%s handle_data start." %(self.cat,self.group["name"]))
        if int(self.hql_type) ==2:
            return {'ret_code':conf.SUCCESS,'msg':'skip over'}

        #skip the process in the custom cate mode
        if self.custom_cdate == 0:
            try:
                all_exists_tables = self.getExistsTables()
            except:
                msg="project %s get  exists table error" %(self.metric_conf)
                logger.exception(msg)
                return err_json(msg)

            table_count = len(all_exists_tables)
            for i in self.group["dim_sets"]:

                dims_str = i["name"].lower().strip("()")
                tmp_arr = dims_str.split(",")
                dim_arr = []
                for j in tmp_arr:
                    dim_arr.append(j.strip().lower())
                dim_arr.sort()
                dim_str = ",".join(dim_arr)

                if not dim_str in all_exists_tables.keys():
                    try:
                        new_table = self.createTable4NewGroup(dim_arr,table_count)
                        logger.info("%s.%s create newtable %s" %(self.cat,self.group["name"],new_table))
                        table_count += 1

                        self.exists_tables[dim_str] = new_table
                    except:
                        msg="project %s %s.%s create table for new group %s error!" %(self.metric_conf,self.cat,self.group["name"],dim_arr)
                        return err_json(msg,logger=logger)

                else:
                    self.exists_tables[dim_str] = all_exists_tables[dim_str]

            self.table2dim= {v: k for k, v in self.exists_tables.items()}

        else:
            #handle the process in custom cdate
            cdate_custom_start, cdate_custom_end = self.getCustomTime()
            #get suffix tables
            cdate_custom_suffix = []
            # 格式要求 %Y-%m  或者 %Y-%m-%d
            for cdate in [cdate_custom_start, cdate_custom_end]:
                try:
                    # 格式 %Y-%m-%d 取月份
                    cdate_suffix = time.strftime('%Y%m',time.strptime(cdate,"%Y-%m-%d"))
                except: # 格式 %Y-%m 取月份
                    try:
                        cdate_suffix = time.strftime('%Y%m',time.strptime(cdate,"%Y-%m"))
                    except:
                        msg='custom cdate format is not correct %s' % cdate
                        return err_json(msg,logger=logger)
                cdate_custom_suffix.append(cdate_suffix)
            try:
                custom_all_exists_tables = self.getRangeExistsTables(cdate_custom_suffix[0],cdate_custom_suffix[1])
            except:
                msg="project %s get custom cdate exists table error" %(self.metric_conf)
                logger.exception(msg)
                return err_json(msg, logger=logger)

            dim_list = []
            for i in self.group['dim_sets']:
                dims_str = i["name"].lower().strip("()")
                tmp_arr = dims_str.split(",")
                dim_arr = []
                for j in tmp_arr:
                    dim_arr.append(j.strip().lower())
                dim_arr.sort()
                dim_str = ','.join(dim_arr)
                dim_list.append(dim_str)

            #pick the right tables with group keys from all exists tables
            for items in custom_all_exists_tables:
                dim = items['group_keys']
                table_name = items['table_name']
                if dim in dim_list:
                    if self.exists_tables.has_key(dim):
                        self.exists_tables[dim].append(table_name)
                    else:
                        self.exists_tables[dim]=[table_name]
                    self.table2dim.update({table_name:dim})

        try:
            self.alterNewCol()
        except AssertionError as e:
            return err_json(e.message,logger=logger)

        try:
            self.deleteExistsData()
        except:
            msg="project %s %s.%s delete table for group %s error!" %(self.metric_conf,self.cat,self.group["name"],dim_arr)
            return err_json(msg,logger=logger)
        logger.info('delete data succ')

        #unlock
        l.unlock()

        #exit run after delete the data
        if self.exec_type == 'delete':
            return {'ret_code': conf.SUCCESS, 'msg': 'delete data success'}


        a = time.time()
        try:
            ret_load=self.loadData()
            logger.info('xxxxxxxxx')
        except Exception, ex:
            msg = "load data occurs error: %s" % ex.message
            return err_json(msg, logger=logger)

        b = time.time()
        num_file=ret_load['num']
        status=ret_load['status']
        msg=ret_load['msg']
        logger.info("%s.%s catTodb end spend %s s time, and %s lines has been load to db."%(self.cat,self.group['name'],b-a,num_file))

        meta_mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META)
        meta_conn=meta_mmsMysql.get_conn()
        meta_cur=meta_mmsMysql.get_cur()

        try:
            monitor_sql="update %s set data_size=%s,load_time_spend=%s where id=%s" %(conf.QUEUE_TABLE,num_file,round(b-a,5),log_file_id)
            meta_cur.execute(monitor_sql)
            meta_conn.commit()
        except Exception,ex:
            logger.exception("insert monitor error:%s" %ex.message)

        meta_cur.close()
        meta_conn.close()
        if not msg:
            msg='load data success.'
        return {'ret_code':status,'msg':msg}



    def getdimensionsWithGroupingID(self,grouping_id):

        dim_str = ""
        tmp = []
        if not grouping_id.isdigit():
            return
        dims = self.group["dimensions"]
        #自定义数据展现时间下grouping_id为1即只有cdate 1个维度，返回“”
        if self.custom_cdate == 1:
            if int(grouping_id) !=1:
                grouping_id = int(grouping_id) >> 1 #remove cdate bit
            else:
                return tmp
        max_grouping_arr=[str(1) for i in range(len(dims))]
        flag= bin(int(grouping_id))[2:]
        flag=flag.zfill(len(dims))
        flag_arr=[flag[len(flag)-1-i] for i in range(len(flag))]
        a_flag_arr=[]
        for i in range(len(dims)):
           a_flag_arr.append(str(int(max_grouping_arr[i])-int(flag_arr[i])))
        flag=''.join(a_flag_arr)
        if len(flag) > len(dims):
            return

        m = len(dims)
        for i in range(len(flag)):
            j = len(flag) - 1 - i
            if flag[j]=='1' :
                n = i
                tmp.append(dims[n]["name"].lower())

        tmp.sort()
        return tmp

    def getGroupingIDWithdimensions(self,dim_sets):
        from math import pow
        dim_str = ""
        groupingid=0
        for index,dim  in enumerate(self.group["dimensions"]):
            if dim["name"].lower() in dim_sets:
                groupingid+=pow(2,index)
        return int(groupingid)

