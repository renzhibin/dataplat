#!/usr/bin/env python2.7
#coding=utf-8

import os
import time
import socket

import web
import requests

import mms.conf.env as conf
from mms.lib.mms_mysql import MmsMysql
import mms.conf.mms_mysql_conf as mmsMysqlConf
from mms.lib.utils import getScheduleNameByIp
from mms.lib.utils import getScheduleConfByName
from mms.lib.utils import getAllScheduleConf


class GetLogDetail:

    def GET(self):
        user_data=web.input(serial='',app_name='',stat_date='',module_name='')
        file_id = user_data.serial
        app_name = user_data.app_name
        stat_date = user_data.stat_date
        module_name = user_data.module_name


        web.header('Content-type','text/html;charset=utf-8')
        web.header('Transfer-Encoding','chunked')

        if  not file_id:
            return 'not file name given'


        #项目user_access_count特殊处理
        white_project_list=conf.WHITE_PROJECT_LIST.split(',')
        if app_name in white_project_list:
            stat_date='2015-07-16'
        mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()

        sql = "select app_name,stat_date,status,run_module,step,is_test,schedule_level,conf_name from %s where id = %s" % (conf.QUEUE_TABLE,file_id)
        cur.execute(sql)
        columns = cur.description
        result = cur.fetchone()
        tmp = {}
        if result:
            for (index, column) in enumerate(result):
                tmp[columns[index][0]] = column
        module_name=tmp['run_module']

        mmsMysql.conn_close()

        hour='0'
        minute='0'

        ip=socket.gethostbyname(socket.gethostname())
        conf_name=getScheduleNameByIp(ip)
        all_conf_name=getAllScheduleConf()
        if (not tmp['conf_name']) or (tmp['conf_name'] not in all_conf_name) :
            tmp['conf_name']='inf01'

        log_cont=''
        br='<br>'
        if not conf_name:
            conf_name='inf01'
        if True:#tmp['conf_name']==conf_name:
            log_file_path='%s/%s/run_%s_%s_%s.log' %(conf.LOG_PATH,app_name,stat_date,module_name,file_id)

            if len(tmp)>0 :
                if 'minute'==tmp['schedule_level'] or 'hour'==tmp['schedule_level']:
                    t_time=time.strptime(str(tmp['stat_date']),"%Y-%m-%d %H:%M")
                    hour=str(int(time.strftime("%H",t_time)))
                    minute=str(int(time.strftime("%M",t_time)))
                    module_name=str(tmp['run_module'])
                    stat_date=str(time.strftime("%Y-%m-%d",t_time))
                    log_file_path='%s/%s/run_%s_%s_%s_%s_%s.log' %(conf.LOG_PATH,app_name,stat_date,hour,minute,module_name,file_id)
                print log_file_path

            if not os.path.exists(log_file_path):
                return 'not found'
            log_file=open(log_file_path)
            log_cont=log_file.read()
        else:
            ip=getScheduleConfByName(tmp['conf_name'],'ip')
            req_url="http://%s:8001/get_run_detail?serial=%s&app_name=%s&stat_date=%s&module_name=%s"%(str(ip),str(file_id),str(app_name),str(stat_date),str(module_name))
            r=requests.get(req_url)
            log_cont=r.text
            br=''
        tmp=''
        for line in log_cont.split('\n'):
           tmp+=line+br

        return tmp

