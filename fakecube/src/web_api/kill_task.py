#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'
import os
import time
import socket

import web
import requests

import mms.conf.env as conf
from mms.lib.mms_mysql import MmsMysql
import mms.conf.mms_mysql_conf as mmsMysqlConf
import mms.lib.utils as utils
from mms.lib.lock import SingletonLock
from mms.lib.utils import getScheduleNameByIp
from mms.lib.utils import getScheduleConfByName
from mms.lib.utils import getAllScheduleConf
from lib import *


class KillTask(action.Action):
    def GET(self):
        user_data=web.input(serial='',app_name='',stat_date='',module_name='',username='')
        file_id = user_data.serial
        app_name = user_data.app_name
        stat_date = user_data.stat_date
        module_name = user_data.module_name
        user_name=user_data.username

        if  not file_id:
            return 'not file name given'

        mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
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
        white_username_list=['bangzhongpeng','zhibinren']
        '''
        if not task_creater and user_name not in white_username_list:
            return base.retu('1','系统例行任务不可手动杀死。')
        '''
        if int(task_status) in [5,6,7,8,11]:
            return base.retu('1','任务已经为结束状态')

        if int(task_status) in [1,2,9]:
            #任务状态
            res = utils.update_task_status(file_id,conf.KILLED)
            if not res:
                return base.retu('-1', '任务状态更新失败')
            utils.sendMail(result,task_creater,user_name)
            return base.retu('0','杀死任务成功。')
        tmp_cmd_pre=''
        '''
        if not task_creater and user_name not in white_username_list:
            return base.retu('1','系统例行任务不可手动杀死。')
        else:
            pass
            # tmp_cmd_pre='su inf_shoudong; '
        '''
        ip=socket.gethostbyname(socket.gethostname())
        conf_name=getScheduleNameByIp(ip)
        all_conf_name=getAllScheduleConf()
        if (not result['conf_name']) or (result['conf_name'] not in all_conf_name) :
            result['conf_name']='inf01'
        if result['conf_name']==conf_name:
            res = utils.update_task_status(file_id,conf.KILLED)
            if not res:
                return base.retu('-1', '任务状态更新失败')

            #任务已经开始执行那么杀死任务
            hour='0'
            minute='0'
            log_file_path='%s/%s/run_%s_%s_%s.log' %(conf.LOG_PATH,app_name,stat_date,module_name,file_id)
            #解除任务锁
            lock_file = '%s/%s_%s_%s.lock' %(conf.LOCK_PATH,app_name,module_name,stat_date)
            if len(result)>0 :
                if 'minute'==result['schedule_level'] or 'hour'==result['schedule_level']:
                    t_time=time.strptime(str(result['stat_date']),"%Y-%m-%d %H:%M")
                    hour=str(int(time.strftime("%H",t_time)))
                    minute=str(int(time.strftime("%M",t_time)))
                    module_name=str(result['run_module'])
                    stat_date=str(time.strftime("%Y-%m-%d",t_time))
                    log_file_path='%s/%s/run_%s_%s_%s_%s_%s.log' %(conf.LOG_PATH,app_name,stat_date,hour,minute,module_name,file_id)
                    lock_file = '%s/%s_%s_%s_%s_%s.lock' %(conf.LOCK_PATH,app_name,module_name,stat_date,hour,minute)
            if not os.path.exists(log_file_path):
                return base.retu('1','杀死任务失败。')
            if os.path.exists(lock_file):
                try:
                    l=SingletonLock(lock_file)
                    l.unlock()
                except IOError,e:
                    return base.retu('1','杀死任务失败。')
            log_str=''
            fs=open(log_file_path)
            log_str=fs.read()
            fs.close()
            import re
            r=re.compile(r'(Kill\s*Command\s*=\s*((\/[-a-zA-Z0-9,_\.\s][^\n]*)+))')
            command=r.findall(log_str)
            # command=command[len(command)-1][1]
            command_list=[]
            for cmd in command:
                command_list.append(cmd[1])
            run_command=';'.join(command_list)
            hive_retu=os.system(run_command)

            pid_cmd="ps aux |grep -i 'run_task_single.py -l %s'|grep -v grep| awk '{print $2}'|xargs kill -9"%(str(file_id))
            pid_cmd=tmp_cmd_pre+pid_cmd
            pid_cmd=os.system(pid_cmd)

            #杀死任务通知任务创建人
            utils.sendMail(result,task_creater,user_name)

            if hive_retu ==0:
                return base.retu('0','杀死任务成功。')
            else:
                return base.retu('0','杀死任务成功。')
        else:

            ip=getScheduleConfByName(result['conf_name'],'ip')
            req_url="http://%s:8181/kill_task?serial=%s&app_name=%s&stat_date=%s&module_name=%s&username=%s"%(str(ip),str(file_id),app_name,stat_date,module_name,user_name)
            r=requests.get(req_url)
            return r.text
    def POST(self):
        self.GET()

