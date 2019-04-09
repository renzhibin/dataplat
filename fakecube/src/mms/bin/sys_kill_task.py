#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import os
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import env as conf
import time
from lock import SingletonLock
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql

def sys_kill_task(id,app_name,stat_date,module_name,user_name,status,creater,schedule_level,reset_status=conf.WAITING):
    try:
        if int(status) in [5,6,7,8,11]:
            return
        hour='0'
        minute='0'
        log_file_path='%s/%s/run_%s_%s_%s.log' %(conf.LOG_PATH,app_name,stat_date,module_name,id)
        #解除任务锁
        lock_file = '%s/%s_%s_%s.lock' %(conf.LOCK_PATH,app_name,module_name,stat_date)
        if 'minute'==schedule_level or 'hour'==schedule_level:
            t_time=time.strptime(str(stat_date),"%Y-%m-%d %H:%M")
            hour=str(int(time.strftime("%H",t_time)))
            minute=str(int(time.strftime("%M",t_time)))
            stat_date=str(time.strftime("%Y-%m-%d",t_time))
            log_file_path='%s/%s/run_%s_%s_%s_%s_%s.log' %(conf.LOG_PATH,app_name,stat_date,hour,minute,module_name,id)
            lock_file = '%s/%s_%s_%s_%s_%s.lock' %(conf.LOCK_PATH,app_name,module_name,stat_date,hour,minute)
        if not os.path.exists(log_file_path):
            return
        if os.path.exists(lock_file):
            try:
                l=SingletonLock(lock_file)
                l.unlock()
            except IOError,e:
                return

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

        pid_cmd="ps aux |grep -i 'run_task_single.py -l %s'|grep -v grep| awk '{print $2}'|xargs kill -9"%(str(id))

        pid_cmd=os.system(pid_cmd)
        update_task_status(id,reset_status)

        log_inf=str(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(time.time())))
        task_inf=':%s,%s,%s,%s'%(id,app_name,stat_date,module_name)
        log_inf=log_inf+task_inf
        print log_inf
    except IOError,e:
        import traceback
        traceback.print_exc()


def update_task_status(id,status):
    try:
        mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()
        sql='update %s set status=%s where id=%s'%(conf.QUEUE_TABLE,status,id)
        cur.execute(sql)
        conn.commit()
        conn.close()
    except Exception,ex:
        import traceback
        traceback.print_exc()



def get_run_high_task():
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    sql="select * from mms_run_log where status=3 and creater is not null and priority>=5"

    cur.execute(sql)

    columns=cur.description

    result=[]
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    return result

if __name__=='__main__':
    res=get_run_high_task()
    for e in res:
        sys_kill_task(e['id'],e['app_name'],e['stat_date'],e['run_module'],'bangzhongpeng',e['status'],e['creater'],e['schedule_level'])
