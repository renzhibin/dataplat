#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'xingjieliu'

import time

import web
import mms.conf.env as conf
from mms.lib.mms_mysql import MmsMysql
import mms.conf.mms_mysql_conf as mmsMysqlConf
import mms.lib.utils as utils
from lib import *


class SetTaskReady(action.Action):

    def GET(self):
        user_data=web.input(serial='',app_name='',stat_date='',module_name='',username='')
        file_id = user_data.serial
        user_name=user_data.username
        app_name = user_data.app_name
        stat_date = user_data.stat_date
        module_name = user_data.module_name
        type = 1 # 0: 杀死任务， 1: 置为就绪

        if  not file_id:
            return 'not file name given'

        mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur(mmsMysql.DICTCURSOR_MODE)
        sql='select * from mms_run_log where id=%s'%(file_id)
        cur.execute(sql)
        result= cur.fetchone()
        conn.close()
        if len(result)==0:
            return base.retu('1','没有此任务')
        task_creater=result['creater']
        task_status=result['status']
        conf_name=result['conf_name']
        if int(task_status) == 2:
            return base.retu('1','任务已经为就绪状态')

        if int(task_status) == 1:
            if not conf_name:
                conf_name='inf01'
            #任务状态
            res = utils.update_task_status(file_id,conf.READY,conf_name=conf_name)
            if not res:
                return base.retu('-1', '更新任务状态置为就绪失败')
            self.write2syslog(result,app_name,stat_date, module_name,file_id, user_name)
            utils.sendMail(result,task_creater,user_name, type)
            return base.retu('0','任务状态置为就绪成功.')
        else:
            return base.retu('1', '任务状态不是阻塞, 不能置为就绪')

    def POST(self):
        self.GET()

    def write2syslog(self, result, app_name, stat_date, module_name,file_id, user_name):
        hour='0'
        minute='0'
        fs = None
        log_file_path='%s/%s/run_%s_%s_%s.log' %(conf.LOG_PATH,app_name,stat_date,module_name,file_id)
        if len(result)>0 :
            if 'minute'==result['schedule_level'] or 'hour'==result['schedule_level']:
                t_time=time.strptime(str(result['stat_date']),"%Y-%m-%d %H:%M")
                hour=str(int(time.strftime("%H",t_time)))
                minute=str(int(time.strftime("%M",t_time)))
                module_name=str(result['run_module'])
                stat_date=str(time.strftime("%Y-%m-%d",t_time))
                log_file_path='%s/%s/run_%s_%s_%s_%s_%s.log' %(conf.LOG_PATH,app_name,stat_date,hour,minute,module_name,file_id)
        try:
            import datetime
            fs=open(log_file_path, 'a')
            fs.write('\n')
            time_str = datetime.datetime.strftime(datetime.datetime.now(), '%Y-%m-%d %H:%M')
            fs.write('[SYS_INFO][%s] 用户 %s 在 %s 强制把任务id %s (appname: %s, moudle_name: %s ) 从阻塞置为就绪状态.' % (time_str,user_name,time_str,file_id,app_name,module_name))
            fs.close()
        except:
            return base.retu('-1', '更新任务置为就绪失败')