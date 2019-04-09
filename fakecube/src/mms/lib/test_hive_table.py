#!/usr/bin/env python2.7
#coding=utf-8

"""
Attention: currently, this API only support daily check
"""
import subprocess,requests,traceback
import time
import sys
import simplejson as json

import env as conf
# import hive.happyhive as happyhive
from log4me import MyLogger
from mms_table_tag import checkTableTag

logger = MyLogger.getLogger()

HDFS_HOME = '/hive'
HADOOP_BIN = 'hadoop'
BLACKHOLE = open('/dev/null')
# hp=happyhive.happyhive()
def _getHDFSPATH(db, table, partition_name='', partition_value=''):
    if db == 'default':
        hdfs_dir = '/'.join((HDFS_HOME, table, '%s=%s'%(partition_name, partition_value)))
    else:
        hdfs_dir = '/'.join((HDFS_HOME, '%s'%db, table, '%s=%s'%(partition_name, partition_value)))
    hdfs_dir = hdfs_dir.rstrip('/=')
    return hdfs_dir

#判断是否为sqoop导表，数据是是否ready
def check_sqoop_table_ready(db,table,stat_date,hour=None):
    tmp_time=int(int(time.mktime(time.strptime('2015-09-18',"%Y-%m-%d"))))
    stat_date_time=int(time.mktime(time.strptime(stat_date,"%Y-%m-%d")))
    if tmp_time>=stat_date_time:
        return False,False
    tag_dir="%s/%s_%s/"%('/user/data_ready_tag',str(db),str(table))
    dir_cmd="%s dfs -ls %s"%(HADOOP_BIN,tag_dir)
    logger.info(dir_cmd)
    ret_code=subprocess.call(dir_cmd,stdout= BLACKHOLE,shell=True)
    # ret_code=hp.checkpath(tag_dir)
    func=lambda x:str(x) if len(str(x))==2 else '0'+str(x)
    if ret_code==conf.TAG_STATUS.SUCCESS:
        day_tag="%s_%s_%s"%(str(table),str(stat_date),'day')
        tag_cmd=dir_cmd+day_tag
        tag_path=tag_dir+day_tag

        if hour:
            hour_tag="%s_%s_%s_%s"%(str(table),str(stat_date),func(str(hour)),'hour')
            tag_cmd=dir_cmd+hour_tag
            tag_path=tag_dir+hour_tag
        logger.info(tag_cmd)
        ret_tag_code=subprocess.call(tag_cmd,stdout= BLACKHOLE,shell=True)
        # ret_tag_code=hp.checkpath(tag_path, user_check=True)
        if ret_tag_code==conf.TAG_STATUS.SUCCESS:
            return True,True
        elif ret_tag_code == conf.TAG_STATUS.SKIP:
            return False,False
        elif not hour:
            #如果
            for e in range(0,24):
                e=func(e)
                tmp_tag="%s_%s_%s_%s"%(str(table),str(stat_date),str(e),'hour')
                tmp_tag_cmd=dir_cmd+tmp_tag
                tmp_tag_dir=tag_dir+tmp_tag
                tmp_ret_tag_code=subprocess.call(tmp_tag_cmd,stdout= BLACKHOLE,shell=True)
                # tmp_ret_tag_code=hp.checkpath(tmp_tag_dir, user_check=True)
                logger.info(tmp_tag_cmd)
                if tmp_ret_tag_code!=conf.TAG_STATUS.SUCCESS:
                    if tmp_ret_tag_code == conf.TAG_STATUS.SKIP:
                        return False,False
                    return True,False
            return True,True

        return True,False
    return False,False

def _testByCLI1(db, table, partition_name, partition_value):

    try:
        #sqoop
        ret_dir,tag_dir=check_sqoop_table_ready(db,table,partition_value)
        if ret_dir and not tag_dir:
            return False
        elif ret_dir and tag_dir:
            return True



        hdfs_dir = _getHDFSPATH(db, table, partition_name, partition_value)

        import re
        r=re.compile(r'(([-a-zA-Z0-9,_ ]+)/([-a-zA-Z0-9,_ ]+))')
        r_res=r.findall(partition_name)
        if len(r_res)==1:
            hdfs_dir = '/'.join((HDFS_HOME, '%s'%db, table, '%s=%s'%(r_res[0][1], partition_value)))

        list_cmd = "%s dfs -ls %s"%(HADOOP_BIN,hdfs_dir)

        logger.info(list_cmd)

        #ret_code = subprocess.call(list_cmd,stdout=BLACKHOLE, stderr=BLACKHOLE, shell=True)
        ret_code = subprocess.call(list_cmd,stdout= BLACKHOLE,shell=True)
        #print >> sys.stderr, ret_code

        if ret_code == 0:
            return True
    except:
        logger.exception("_testByCLI1 exception occur")


    return False

def _testByCLI2(db, table, running_date):

    hdfs_dir = _getHDFSPATH(db, table)

    list_cmd = "%s dfs -ls %s"%(HADOOP_BIN,hdfs_dir)
    logger.info(list_cmd)
    flag=True
    try:
        #sqoop
        ret_dir,tag_dir=check_sqoop_table_ready(db,table,running_date)
        if ret_dir and not tag_dir:
            return False
        elif ret_dir and tag_dir:
            return True


        # cannot set stdout to BLACKHOLE
        file_lists = subprocess.check_output(list_cmd, stderr=BLACKHOLE,shell=True).split('\n')
        #print >> sys.stderr, file_lists
        for line in file_lists:
            eles = line.split()
            if len(eles) < 5:
                continue
            modify_date = eles[5]
            # '<=' is neccessary
            # print  'modify',modify_date
            # print 'run',running_date
            if modify_date <= running_date:
                flag=False
                continue
            else:
                flag=True
                break
    except:
        logger.exception('test table exception occur!')
        return False
    return flag



def _testByCLI3(db, table, running_date,hour,minute):

    hdfs_dir = _getHDFSPATH(db, table)

    list_cmd = "%s dfs -ls %s"%(HADOOP_BIN,hdfs_dir)
    logger.info(list_cmd)
    flag=True
    try:
        func=lambda x:str(x) if len(str(x))==2 else '0'+str(x)
        #sqoop
        ret_dir,tag_dir=check_sqoop_table_ready(db,table,running_date,func(hour))
        if ret_dir and not tag_dir:
            return False
        elif ret_dir and tag_dir:
            return True

        # cannot set stdout to BLACKHOLE
        file_lists = subprocess.check_output(list_cmd, stderr=BLACKHOLE,shell=True).split('\n')
        #print >> sys.stderr, file_lists
        print file_lists
        for line in file_lists:
            eles = line.split()
            if len(eles) < 6:
                continue
            modify_day = eles[5]
            modify_hour = eles[6]
            modify_date="%s %s"%(modify_day,modify_hour)
            tmp_running_date="%s %s:%s"%(running_date,func(hour),func(minute))
            # '<=' is neccessary
            # print  'modify',modify_date
            # print 'run',running_date
            if modify_date <= tmp_running_date:
                flag=False
                continue
            else:
                flag=True
                break
    except:
        logger.exception('test table exception occur!')
        return False
    return flag


def _testByCLI4(db, table, partition_name, partition_value,hour,minute):

    try:
        func=lambda x:str(x) if len(str(x))==2 else str(0)+str(x)

        ret_dir,tag_dir=check_sqoop_table_ready(db,table,partition_value,func(hour))
        if ret_dir and not tag_dir:
            return False
        elif ret_dir and tag_dir:
            return True

        hdfs_dir = ''
        table_tag=table+'_'+str(partition_value)+'_'+str(func(hour))
        if db == 'default':
            hdfs_dir = '/'.join((HDFS_HOME, table, '%s=%s'%(partition_name, partition_value),table_tag))
        else:
            hdfs_dir = '/'.join((HDFS_HOME, '%s'%db, table, '%s=%s'%(partition_name, partition_value),table_tag))

            import re
            r=re.compile(r'(([-a-zA-Z0-9,_ ]+)/([-a-zA-Z0-9,_ ]+))')
            r_res=r.findall(partition_name)
            tmp_hour=func(hour)
            if len(r_res)==1:
                hdfs_dir = '/'.join((HDFS_HOME, '%s'%db, table, '%s=%s'%(r_res[0][1], partition_value),'%s=%s'%(r_res[0][2],str(tmp_hour))))


        hdfs_dir = hdfs_dir.rstrip('/=')


        list_cmd = "%s dfs -ls %s"%(HADOOP_BIN,hdfs_dir)
        logger.info(list_cmd)

        #ret_code = subprocess.call(list_cmd,stdout=BLACKHOLE, stderr=BLACKHOLE, shell=True)
        ret_code = subprocess.call(list_cmd,stdout= BLACKHOLE,shell=True)
        #print >> sys.stderr, ret_code

        if ret_code == 0:
            return True
    except:
        logger.exception("_testByCLI1 exception occur")


    return False


def _getHDFSPATH_v2(db, table, partition_name='', partition_value='',hour=''):
    func=lambda x:str(x) if len(str(x))==2 else str(0)+str(x)
    if not partition_name:
        partition_value=''
    hdfs_join_list=[HDFS_HOME,'%s'%db, table,'%s=%s'%(partition_name, partition_value)]
    if db == 'default':
        hdfs_join_list=[HDFS_HOME,table,'%s=%s'%(partition_name, partition_value)]

    if partition_name:

        import re
        r=re.compile(r'(([-a-zA-Z0-9,_ ]+)/([-a-zA-Z0-9,_ ]+))')
        r_res=r.findall(partition_name)
        if hour:
            tmp_hour=func(hour)
            if len(r_res)==1:
                hdfs_join_list=[HDFS_HOME,'%s'%db, table,'%s=%s'%(r_res[0][1], partition_value),'%s=%s'%(r_res[0][2],str(tmp_hour))]
                if db =='default':
                    hdfs_join_list=[HDFS_HOME, table,'%s=%s'%(r_res[0][1], partition_value),'%s=%s'%(r_res[0][2],str(tmp_hour))]
            else:
                hour_table_tag=table+'_'+str(partition_value)+'_'+str(func(hour))
                hdfs_join_list.append(hour_table_tag)
        elif not hour and len(r_res)==1:
            #小时天
            hdfs_join_list=[HDFS_HOME,'%s'%db, table,'%s=%s'%(r_res[0][1], partition_value)]
            if db =='default':
                hdfs_join_list=[HDFS_HOME, table,'%s=%s'%(r_res[0][1], partition_value)]

    hdfs_dir='/'.join(hdfs_join_list)
    hdfs_dir = hdfs_dir.rstrip('/=')
    return hdfs_dir

def _getHdfsPathV3(db, table, partitionName = '', partitionValue = '', hour = ''):
    try:
        if not partitionName:
            return _getHdfsPathByHiveLocation(db, table)
        if not hour:
            return _getHdfsPathByHivePartitionLocation(db, table, partitionName, partitionValue)
        return _getHdfsPathWithSpecialTreatmentHourAndPartition(db, table, partitionName, partitionValue, hour)
    except Exception,e:
        logger.exception("_getHdfsPathByHiveLocation exception occur by " + e.message)
        return ''

def _getHdfsPathByHiveLocation(db, table):
    hiveDescCmd = "hive -e 'desc extended " + db + '.' + table + "'"
    return _getLocationByHiveDescExtended(hiveDescCmd)

def _getHdfsPathByHivePartitionLocation(db, table, partitionName = '', partitionValue = ''):
    hiveDescCmd = 'hive -e "desc extended ' + db + '.' + table +  " partition(" + partitionName + '=' + "'" + partitionValue + "')" + '"'
    return _getLocationByHiveDescExtended(hiveDescCmd)

def _getHdfsPathWithSpecialTreatmentHourAndPartition(db, table, partitionName, partitionValue, hour):
    hdfsPath = ''
    dirSeparator = '/'
    partitionNameInfoList = []
    hdfsBasePath = _getHdfsPathByHiveLocation(db, table)
    import re
    pattern = re.compile(r'(([-a-zA-Z0-9,_ ]+)/([-a-zA-Z0-9,_ ]+))')
    partitionNameInfoList=pattern.findall(partitionName)
    timeAddPlaceholders = lambda x:str(x) if len(str(x))==2 else str(0)+str(x)
    if len(partitionNameInfoList) == 1:
        partitionKey1 = partitionNameInfoList[0][1]
        partitionKey2 = partitionNameInfoList[0][2]
        return hdfsBasePath + dirSeparator + '%s=%s'%(partitionKey1, str(partitionValue)) + dirSeparator + '%s=%s'%(partitionKey2, str(timeAddPlaceholders(hour)))
    else:
        return hdfsBasePath + dirSeparator + table + '_' + str(partitionValue) + '_' + str(timeAddPlaceholders(hour))

def _getLocationByHiveDescExtended(cmd):
    systemOutput = subprocess.check_output(cmd, stderr = BLACKHOLE, shell = True)
    locationStr = systemOutput[systemOutput.find('location'):]
    locationStrcommonPos = locationStr.find(',')
    location = locationStr[len('location:'):locationStrcommonPos]
    return location

def check_request_table_ready(db,table,partition_value='',hour='',minute='',check_level='day'):
    '''
        通过接口校验依赖表是否ready
        return bool,bool
        检查表tag ready 返回true,true
            tag not ready 返回false,如果接口状态返回2 false,false.接口返回非2 false,true

    '''
    try:
        if db=='':
            db='default'
        params={"target_prefix":db,
                "target":table,
                "ready_date":partition_value,
                "ready_hour":hour,
                "schedule_level":check_level}

        req_post=requests.post("http://data.lsh123.com:8001/get_table_status",data=params)
        status=req_post.status_code
        content=req_post.text
        content=json.loads(content)
        if int(status)==200:
            r_s=content['status']
            if int(r_s)==0:
                return True,False
            elif int(r_s)==2:
                return False,False
            else:
                return False,True
        else:
            return False,True

    except Exception,e:
        traceback.print_exc()
        logger.error("check request tag exception")
        return False,True


def _testByCLI(db, table, running_date, partition_name='', check_value='',hour='',minute='',check_level='day'):
    try:
        func=lambda x:str(x) if len(str(x))==2 else str(0)+str(x)

        if conf.CHECK_TABLE_TAG:
            ret_dir,tag_dir=check_sqoop_table_ready(db,table,check_value,hour)

            if ret_dir and not tag_dir:
                return False
            elif ret_dir and tag_dir:
                return True

        logger.info("check table tag")
        checkTagDate = running_date
        if not running_date:
            checkTagDate = check_value
        is_ready,is_next = checkTableTag(db, table, checkTagDate, hour, check_level, logger)
        if is_ready:
            logger.info("table tag is ready")
            return True
        logger.info("table tag is not ready")
        is_next = False
        if not is_next:
            logger.info("table tag already maintained,always check the table tag")
            return False
        '''hdfs_dir = _getHDFSPATH_v2(db,table,partition_name=partition_name,partition_value=check_value,hour=hour)'''
        '''get hdfsDir currently by hive location modify by yangyulong'''
        logger.info("check table by hdfs")
        hdfs_dir = _getHdfsPathV3(db, table, partitionName=partition_name, partitionValue=check_value, hour=hour)
        if hdfs_dir == '':
            return False

        list_cmd = "%s dfs -ls %s"%(HADOOP_BIN,hdfs_dir)
        logger.info(list_cmd)

        #指定分区
        if partition_name:
            #ret_code = subprocess.call(list_cmd,stdout= BLACKHOLE,shell=True)
            ret_code = subprocess.call(list_cmd,stdout= BLACKHOLE,shell=True)
            # ret_code=hp.checkpath(hdfs_dir)
	    print 'ret_code',ret_code
            if ret_code == 0:
                return True
        else:
            file_lists = subprocess.check_output(list_cmd, stderr=BLACKHOLE,shell=True).split('\n')
            # file_lists=hp.checkpathlist(hdfs_dir)
            flag=True
            for line in file_lists:
                eles = line.split()
                if len(eles) < 6:
                    continue
                fileName = eles[7]
                if fileName.find('.hive-staging_hive') >= 0:
                    continue
                modify_day = eles[5]
                modify_hour = eles[6]
                modify_date=modify_day
                tmp_running_date=check_value
                if hour:
                    modify_date="%s %s"%(modify_day,modify_hour)
                    tmp_running_date="%s %s:%s"%(check_value,func(hour),func(minute))
                # print modify_date,tmp_running_date
                if modify_date <= tmp_running_date:
                    flag=False
                    continue
                else:
                    flag=True
                    break
            return flag
    except:
        logger.exception("_testByCLI exception occur")

    return False


def testTable(table_name, partition_name, partition_value, running_date=None,check_level=None,hour='',minute=''):



    '''
    test if the given table exists. If the partition_name is passed in None or empty, check if table last_modification date is
    latter than running_date. Otherwise, use the given partition_name and partition_value to check if there at least exist one file.
    Return True if the table is ready to use, False otherwise.
    params:
        table_name - the given table name, can be in 'db.table_name' format
        partition_name - the first partition name. This is used together with partition_value.
        partition_value - the first partition value. This is used together with partition_name.
        running_date - if the partition_name and partition_value is passed in as None and empty, this parameter is neccessary!
    '''

    assert table_name is not None and len(table_name.strip()) != 0

    logger.info("test table ready \ttable_name:%s\tpartition_name:%s\tpartition_value:%s\trunning_date:%s\t"%(table_name,partition_name,partition_value,running_date))

    # specify the db and table
    table_name_eles = table_name.split(".")
    assert len(table_name_eles) in (1,2)
    if len(table_name_eles) == 1:
        db = 'default'
        table = table_name_eles[0]
    else:
        db = table_name_eles[0]
        table = table_name_eles[1]

    # check by partition_name and partition_value
    if running_date is None or len(running_date.strip()) == 0:
        assert (partition_name is not None and len(partition_name.strip()) != 0) and (partition_value is not None and len(partition_value.strip())!=0) 
        return _testByCLI(db,table,running_date,partition_name=partition_name,check_value=partition_value,hour=hour,minute=minute,check_level=check_level)
        '''
        #检查小时任务
        if 'minute'==str(check_level) or 'hour'==str(check_level):
            return _testByCLI4(db,table,partition_name,partition_value,hour,minute)
        return _testByCLI1(db, table, partition_name, partition_value)
        '''
    # check by running_date
    else:
        assert (partition_name is None or len(partition_name.strip()) == 0) and (partition_value is None or len(partition_value.strip())==0) 
        return _testByCLI(db,table,running_date,partition_name='',check_value=running_date,hour=hour,minute=minute,check_level=check_level)
        '''
        #检查小时任务
        if 'minute'==str(check_level) or 'hour'==str(check_level):
            return _testByCLI3(db, table, running_date,hour,minute)

        return _testByCLI2(db, table, running_date)
        '''

# unit test
if '__main__' == __name__:

    import datetime
    now = datetime.datetime.now()
    today_str = now.strftime('%Y-%m-%d')
    day_add1 = now + datetime.timedelta(days=1)
    day_add1_str = day_add1.strftime('%Y-%m-%d')
    day_minus1 = now - datetime.timedelta(days=1)
    day_minus1_str = day_minus1.strftime('%Y-%m-%d')
    day_minus2 = now - datetime.timedelta(days=2)
    day_minus2_str = day_minus2.strftime('%Y-%m-%d')


    #assert not testTable('visitlogs', 'dt', day_add1_str)
    #assert testTable('visitlogs', 'dt', today_str)
    #assert testTable('visitlogs', 'dt', day_minus1_str)
    #assert testTable('visitlogs', 'dt', day_minus2_str)
    #assert not testTable('dm.dm_order_4analyst', '', '',day_add1_str)
    #assert not testTable('dm.dm_order_4analyst', '', '',today_str)
    #assert testTable('dm.dm_order_4analyst', '', '',day_minus1_str)
    #assert testTable('dm.dm_order_4analyst', '', '',day_minus2_str)
    assert testTable('msq_nginx_log','dt', day_minus1_str)
    assert testTable('dw.dw_goods_catalog_tree','','', day_minus1_str)
    assert testTable('ods_brd_shop_goods_info','','', day_minus1_str)
