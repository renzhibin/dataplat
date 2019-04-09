#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import re,subprocess,os,requests
import simplejson as json
from TagManage import TagManage

HADOOP_BIN = '/hadoop/hadoop/bin/hadoop'
TAG_HOME = '/user/data_ready_tag/'
BLACKHOLE = open('/dev/null')

def remove_mms_table_tag(table, stat_date, stat_hour, schedule_level):
    li = table.split('.')
    db = 'default'
    if len(li)==2:
        db=li[0]
        table=li[1]
    else:
        db='default'
        table=li[0]

    try:
        par_hour = str(stat_hour).zfill(2)
        params = {"target_prefix": db,
                  "target": table,
                  "ready_date": stat_date,
                  "ready_hour": par_hour,
                  "schedule_level": schedule_level,
                  "enable": 0,
                  "token_name":"data",
                  "token_value":"7BTUhUuzeOmB"}
        req_post = requests.post("http://data.lsh123.com:8001/update_table_status", data=params)
        status = req_post.status_code
        content = req_post.text
        content = json.loads(content)
        if int(status) == 200:
            r_s = content['status']
            msg = content['msg']
            if int(r_s) != 0:
                print "generate tag {}.{} date:{} {}request exception {}".format(db, table, stat_date, par_hour, msg)
        else:
            print "generate tag {}.{} date:{} {}request exception".format(db, table, stat_date, par_hour)
    except Exception, e:
        print "generate tag {}.{} date:{} {} exception".format(db, table, stat_date, par_hour)
        return
    '''
    table_tag_dir='%s%s_%s/'%(TAG_HOME,db,table)
    table_tag_name=''

    if schedule_level=='day':
        table_tag_name='%s_%s_%s'%(table,stat_date,'day')

    if schedule_level=='hour':
        func=lambda x:str(x) if len(str(x))==2 else '0'+str(x)
        table_tag_name='%s_%s_%s_%s'%(table,stat_date,func(stat_hour),'hour')

        # if int(stat_hour)==23:
        #     tag_hour_day='%s%s_%s_%s'%(table_tag_dir,table,stat_date,'day')

    if table_tag_name:
        try:
            tag_file='%s%s'%(table_tag_dir,table_tag_name)
            get_tag_cmd = "%s fs -ls %s"%(HADOOP_BIN,tag_file)
            print get_tag_cmd
            ret_code = subprocess.call(get_tag_cmd,stdout= BLACKHOLE,shell=True)
            #如果tag文件不存在忽略

            if ret_code!=0:
                print 'tag %s not exist, delete tag skip' % table_tag_name
            else:
                remove_cmd="%s fs -rm %s"%(HADOOP_BIN,tag_file)
                print remove_cmd
                os.system(remove_cmd)
                print 'remove tag %s successfully' % table_tag_name
                # if tag_hour_day:
                #     remove_cmd ="%s fs -rm %s"%(HADOOP_BIN,tag_hour_day)
                #     print remove_cmd
                #     #os.system(remove_cmd)
                #     print 'remove tag %s successfully' % tag_hour_day
        except Exception, ex:
            print ex
            print 'remove tag %s exception occur' % table_tag_name
    '''

def generate_mms_table_tag(hql,group,stat_date,stat_hour,schedule_level,logger,enable=1):
    if enable == 0:
        return
    db,table,par_date,par_hour=match_hql_table_tag(hql)
    if not db or not table:
        return
    try:
        tagManger = TagManage(db + '.' + table, stat_date, stat_hour, '300', '00', None, schedule_level)
        tagManger.create_tag()
        logger.info("generate tag {}.{} date:{} {} success".format(db,table,stat_date,stat_hour))
        return
    except Exception,e:
        logger.error("generate tag {}.{} date:{} {} exception:{}".format(db,table,stat_date,stat_date,e.message))
        return

def checkTableTag(db, table, date, hour, schedule_level, logger):
    try:
        tagManger = TagManage(db + '.' + table, date, hour, '300', '00', None, schedule_level)
        return tagManger.check_tag(False)
    except Exception,e:
        logger.error("check tag {}.{} date:{} {} exception:{}".format(db,table,date,hour,e.message))
        return False, False


'''def generate_mms_table_tag(hql,group,stat_date,stat_hour,schedule_level,logger,enable=1):


    db,table,par_date,par_hour=match_hql_table_tag(hql)
    if not db or not table:
        return
    try:
        if schedule_level == 'day':
            if not par_date:
                par_date = stat_date
        if schedule_level == 'hour':
            if not par_date:
                par_date = stat_date
            if not par_hour:
                par_hour = stat_hour

        par_hour=str(par_hour).zfill(2)
        params={"target_prefix":db,
                "target":table,
                "ready_date":par_date,
                "ready_hour":par_hour,
                "schedule_level":schedule_level,
                "enable":enable,
                "token_name": "data",
                "token_value": "7BTUhUuzeOmB"}
        req_post = requests.post("http://data.lsh123.com:8001/update_table_status", data=params)
        status = req_post.status_code
        content = req_post.text
        content = json.loads(content)
        if int(status)==200:
            r_s=content['status']
            msg=content['msg']
            if int(r_s)!=0:
                logger.error("generate tag {}.{} date:{} {}request exception {}".format(db,table,par_date,par_hour,msg))
        else:
            logger.info("generate tag {}.{} date:{} {}request exception".format(db,table,par_date,par_hour))
    except Exception,e:
        logger.error("generate tag {}.{} date:{} {} exception".format(db,table,par_date,par_hour))
        return

    table_tag_dir='%s%s_%s/'%(TAG_HOME,db,table)
    table_tag_name=''
    tag_hour_day=''
    if schedule_level=='day':
        if not par_date:
            par_date=stat_date
        table_tag_name='%s_%s_%s'%(table,par_date,'day')

    if schedule_level=='hour':
        if not par_date:
            par_date=stat_date
        if not par_hour:
            par_hour=stat_hour
        func=lambda x:str(x) if len(str(x))==2 else '0'+str(x)

        table_tag_name='%s_%s_%s_%s'%(table,par_date,func(par_hour),'hour')

        if int(par_hour)==23:
            tag_hour_day='%s%s_%s_%s'%(table_tag_dir,table,par_date,'day')

    if table_tag_name:
        tag_file='%s%s'%(table_tag_dir,table_tag_name)
        get_tag_cmd = "%s fs -ls %s"%(HADOOP_BIN,tag_file)
        logger.info(get_tag_cmd)
        ret_code = subprocess.call(get_tag_cmd,stdout= BLACKHOLE,shell=True)
        #如果tag文件不存在建立
        if ret_code!=0:
            create_cmd="%s fs -touchz %s"%(HADOOP_BIN,tag_file)
            logger.info(create_cmd)
            os.system(create_cmd)
            if tag_hour_day:
                create_cmd="%s fs -touchz %s"%(HADOOP_BIN,tag_hour_day)
                logger.info(create_cmd)
                os.system(create_cmd)
    '''

def match_hql_table_tag(hql):
    hql=hql.strip().lower()
    match_table=re.compile(r'insert\s+(overwrite|into)?\s+table\s+([\s\S]*?)\s+(partition\s*\(([\s\S]*?)\))?')

    match_res=match_table.findall(hql)
    if not match_res:
        return None,None,None,None
    match_res=match_res[0]
    table=match_res[1]
    partition_str=match_res[3]

    if table:
        table=table.split('.')
        db='default'
        par_date=None
        par_hour=None

        if len(table)==2:
            db=table[0]
            table=table[1]
        else:
            table=table[0]

        if partition_str:
            par_list=partition_str.split(',')
            match_par=re.compile(r'([-0-9]+)')
            match_par_date=None
            match_par_hour=None
            if len(par_list)==2:
                match_par_date=match_par.findall(par_list[0])
                match_par_hour=match_par.findall(par_list[1])

                if match_par_date:
                    par_date=match_par_date[0]
                if match_par_hour:
                    par_hour=match_par_hour[0]
            elif len(par_list)==1:
                match_par_date=match_par.findall(par_list[0])
                if match_par_date:
                    par_date=match_par_date[0]
        return db,table,par_date,par_hour

    return None,None,None,None




if __name__=='__main__':
    str='''
insert overwrite table tmp.tmp_weiquan_user_data partition(dt='2015-02-10')
select client_version,client_device,device_id,user_id
from mobile_app_log_new
where dt = '$DATE(0)' and lower(client_app)='weiquan'
group by client_version,client_device,device_id,user_id


    '''
    print match_hql_table_tag(str)