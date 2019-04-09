#!/usr/bin/env python2.7
#coding=utf-8

import os
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import env as conf
import socket
import sys,getpass
from log4me import MyLogger
import argparse
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
import time
from utils import getScheduleNameByIp
from utils import getScheduleConfByName
from sys_kill_task import sys_kill_task

#status={
#1:WAITING
#2:READY
#3:RUNNING
#4:HIVEEND 中间状态
#5:SUCCESS
#6:FAILED
#7:WARNING
# }
def cmdArgsDef():
    arg_parser = argparse.ArgumentParser()
    arg_parser.add_argument('-t', '--type', help='[help]检测类型', required=False)

    args = arg_parser.parse_args()

    return args

class Runner():
    def __init__(self):
        self.mmsMysqlWrite = MmsMysql(mmsMysqlConf.MMS_DB_META)
        self.mmsMysqlRead = MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        self.writeConn=self.mmsMysqlWrite.get_conn()
        self.writeCur=self.mmsMysqlWrite.get_cur()
        self.readConn=self.mmsMysqlRead.get_conn()
        self.readCur = self.mmsMysqlRead.get_cur()
        self.logger = MyLogger.getLogger()

        self.ip=socket.gethostbyname(socket.gethostname())
        self.conf_name=getScheduleNameByIp(self.ip)

        self.func=lambda x:x if x and x!='' else '0'

    def __del__(self):
        if self.readConn.open:
            self.readConn.close()
        if self.writeConn.open:
            self.writeConn.close()

    def get_checking_list_nr(self):
        #query the number on the slave database
        self.readCur.execute("select count(id) from %s where status = %s and conf_name='%s' " % (conf.QUEUE_TABLE, conf.CHECKING,self.conf_name))
        checking_list = self.readCur.fetchall()
        return checking_list[0][0]


    def  get_block_list(self):
        #获取当前checking状态的个数
        checking_list_nr = self.get_checking_list_nr()
        max_check_num=int(self.func(getScheduleConfByName(self.conf_name,"max_check_num")))
        limit_nr = max_check_num#conf.MAX_RUN_JOB_NUM
        if checking_list_nr >= 0:
            limit_nr = max_check_num - checking_list_nr
        if limit_nr < 0:
            limit_nr = 0

        self.readCur.execute("select id,app_name,stat_date,run_module,create_time,schedule_level,last_checked_time  from %s where status = %s and (conf_name='' or conf_name='%s') order by priority desc,last_checked_time asc,create_time,app_name,stat_date desc limit 0,%s " %(conf.QUEUE_TABLE,conf.WAITING,self.conf_name,limit_nr))
        columns = self.readCur.description

        block_list = self.readCur.fetchall()
        result_list=[]

        for r in block_list:
            tmp={}
            for (index,column) in enumerate(r):
                tmp[columns[index][0]]=column
            result_list.append(tmp)

        return result_list

    #大于10分钟的任务还在check 重新置为阻塞
    def reset_checking_tasks(self):

        self.readCur.execute("select * from (select id ,TIMESTAMPDIFF(MINUTE,last_checked_time,now()) diff_minute from %s where status=%s) t where t.diff_minute>=10 "%(conf.QUEUE_TABLE,conf.CHECKING))
        columns = self.readCur.description

        block_list = self.readCur.fetchall()
        update_list=[]
        for r in block_list:
            update_list.append((conf.WAITING,r[0]))
        self.writeCur.executemany(" update  mms_run_log  set  status = %s  where id=%s",update_list)
        self.writeConn.commit()
    #运行任务超过一天杀死该任务同状态重置为杀死
    def reset_running_tasks(self):
        try:
            self.readCur.execute("select * from (select id ,TIMESTAMPDIFF(DAY,start_time,now()) diff_day,app_name,stat_date,run_module,status,creater,schedule_level from %s where status=%s) t where t.diff_day>=1 "%(conf.QUEUE_TABLE,conf.RUNNING))
            columns = self.readCur.description

            block_list = self.readCur.fetchall()
            for r in block_list:
              sys_kill_task(r[0],r[2],r[3],r[4],'bangzhongpeng',r[5],r[6],r[7],reset_status=conf.KILLED)

        except:
            import traceback
            traceback.print_exc()
    def get_running_list(self):

        result_list = []

        #self.cur.execute("select count(1) from %s where status = %s" %(conf.QUEUE_TABLE,conf.RUNNING))
        self.readCur.execute('select count(1) totalcount, sum(if(creater is null,1,0)) syscount,sum(if(creater is not null and priority<5,1,0)) norcount,sum(if(creater is not null and priority>=5,1,0)) highcount from %s where status=%s and conf_name="%s" '%(conf.QUEUE_TABLE,conf.RUNNING,self.conf_name))
        tmp_res=self.readCur.fetchone()
        func=lambda x:x if x else 0
        running_cnt=func(tmp_res[0])
        syscount=func(tmp_res[1])#例行任务的任务
        norcount=func(tmp_res[2])#手动提交任务并且优先级小于5任务
        highcount=func(tmp_res[3])#手动提交任务并且优先级大于5的任务
        nor_high_count=int(norcount)+int(highcount)
        self.logger.info('running cnt :%s' %running_cnt)
        running_list = []
        cur_hour=int(time.strftime("%H",time.localtime(time.time())))
        conf_max_run_num=int(self.func(getScheduleConfByName(self.conf_name,"max_run_num")))
        sys_multi_run_limit=conf_max_run_num#conf.MULTI_RUN_LIMIT
        if cur_hour>=4 and cur_hour<=9:
            sys_multi_run_limit=70

        #例行任务
        if syscount < int(sys_multi_run_limit):
            remain_run_cnt = sys_multi_run_limit - syscount
           # self.cur.execute("select id,app_name,stat_date,run_module from %s where status = %s order by priority desc,create_time,app_name,stat_date desc limit %s" %(conf.QUEUE_TABLE,conf.WAITING,remain_run_cnt))
            #waiting_to_run_list = self.cur.fetchall()

            #if waiting_to_run_list:
             #   running_list.extend(waiting_to_run_list)

            self.readCur.execute("select id,app_name,stat_date,run_module,ready_time,creater,priority,submitter from %s where status = %s and conf_name='%s' and creater is null order by priority desc,ready_time asc,stat_date desc" %(conf.QUEUE_TABLE,conf.READY,self.conf_name))


            columns = self.readCur.description

            tmp_app=['push_board_stat','core_report']
            count=1
            for e in self.readCur.fetchall():
                if count>=int(remain_run_cnt):
                    break
                tmp_app_name=e[1]
                tmp_task_ready_time=int(time.time())
                if e[4]:
                    tmp_task_ready_time=int(time.mktime(e[4].timetuple()))
                tmp_now_time=int(time.time())
                diff_time=int(tmp_now_time-tmp_task_ready_time)
                if tmp_app_name not in tmp_app:
                    running_list.append(e)
                    count=count+1
                else:
                    if diff_time>600:
                        running_list.append(e)
                        count=count+1

        #手动提交任务并且优先级小于5
        if (running_cnt+len(running_list)) < conf_max_run_num:
            remain_run_cnt = conf_max_run_num - running_cnt-len(running_list)

            #手动提交任务优先级大于等于5
            high_run_count=0
            nor_run_count=0
            if highcount<20:
                diff_high_count=20-highcount

                if diff_high_count>=remain_run_cnt:
                    high_run_count=remain_run_cnt
                else:
                    high_run_count=diff_high_count

            self.readCur.execute("select id,app_name,stat_date,run_module,ready_time,creater,priority,submitter from %s where status = %s and conf_name='%s' and creater is not null and priority>=5 order by priority desc,ready_time asc,stat_date desc limit %s" %(conf.QUEUE_TABLE,conf.READY,self.conf_name,str(high_run_count)))

            h_list=self.readCur.fetchall()
            nor_run_count=int(remain_run_cnt)-len(h_list)
            running_list=running_list+list(h_list)

            if nor_run_count>0:
                self.readCur.execute("select id,app_name,stat_date,run_module,ready_time,creater,priority,submitter from %s where status = %s and conf_name='%s' and creater is not null and priority<5 order by priority desc,ready_time asc,stat_date desc limit %s" %(conf.QUEUE_TABLE,conf.READY,self.conf_name,str(nor_run_count)))
                running_list=running_list+list(self.readCur.fetchall())
        for r in running_list:
            tmp={}
            for (index,column) in enumerate(r):
                tmp[columns[index][0]]=column
            result_list.append(tmp)
        return result_list

    def is_run(self,r):
        userWhiteList=['pengbangzhong','liangbo','data_alarm']
        cur_user=getpass.getuser()
        return True

        if (cur_user=='inf' and not r['creater']) or (cur_user=='inf' and r['creater'] and r['submitter'] in userWhiteList) or (cur_user=='inf' and r['creater']=='system'):
            return True
        if (cur_user=='inf_shoudong' and r['creater'] and r['creater']!='system' and r['submitter'] not in userWhiteList):
            return True

        return True

    def run(self):
        try:
            cur_user=getpass.getuser()
            userWhiteList=['pengbangzhong','liangbo','data_alarm']
            running_list = self.get_running_list()
            self.logger.info('running task num:%s'%(str(len(running_list))))
            if running_list:

                 for r in running_list:

                    if self.is_run(r):
                        cmd="nohup python %s/run_task_single.py -l %s >>../../../log/system_log/all_app/%s.txt 2>&1 &" %(conf.BIN_PATH,r['id'],r['id'])

                        self.logger.info('user %s running:%s' %(cur_user,cmd))
                        '''
                        if r['creater']:
                            now_hour=int(time.strftime("%H",time.localtime(time.time())))
                            tip=False
                            #2点到8点只能提交到test04
                            if now_hour>=2 and now_hour<=8:
                                tip=True
                            if r['submitter'] not in userWhiteList and (int(r['priority'])<5 or tip):
                                su_cmd='su inf_shoudong -c '
                                cmd="%s '%s'"%(su_cmd,cmd)
                        '''
                        ret = os.system(cmd)

                        msg="app:%s,module:%s,stat_date:%s for queue id:%s" %(r['app_name'],r['run_module'],r['stat_date'],r['id'])

                        if ret:
                            self.logger.error('%s failed!' %msg)
                        else:
                            self.logger.info('%s start!' %msg)
            else:
                self.logger.info('no queue to run')
        except:
            self.logger.exception("runner exception occurred!")
            self.mmsMysqlRead.conn_close()
            self.mmsMysqlWrite.conn_close()

        #close the connection before exit
        self.mmsMysqlRead.conn_close()
        self.mmsMysqlWrite.conn_close()
        sys.exit(0)

    def check(self):
        import  time
        now_unixstamp=time.time()

        try:
            block_list = self.get_block_list()

            #当前运行检测允许的个数
            #更改改任务状态为检查状态CHECKING
            update_ids=[(conf.CHECKING,self.conf_name,e['id']) for e in block_list]
            self.writeCur.executemany(" update  mms_run_log  set  status = %s,conf_name=%s ,last_checked_time = now() where id=%s",update_ids)
            self.writeConn.commit()

            #大于10分钟的任务还在check 重新置为阻塞
            self.reset_checking_tasks()
            self.reset_running_tasks()
            # for t in block_list:
            #     #更改改任务状态为检查状态CHECKING
            #     self.writeCur.execute(" update  %s  set  status = %s  where id=%s" %(conf.QUEUE_TABLE,conf.CHECKING,t['id']))
            #     self.writeConn.commit()

            #close read connetion before start loop
            self.mmsMysqlRead.conn_close()

            if block_list:
                for r in block_list:
                    create_unxistamp=time.mktime(r['create_time'].timetuple())
                    #天任务5天为超时，小时任务为1天
                    overtime=False

                    if 'minute'==r['schedule_level'] or 'hour'==r['schedule_level']:
                        if int(now_unixstamp-create_unxistamp)>=86400:
                            overtime=True
                    inter=now_unixstamp-create_unxistamp
                    if inter>=5*86400:
                        overtime=True
                    if overtime:
                        self.writeCur.execute(" update  %s  set  status = %s where id=%s" %(conf.QUEUE_TABLE,conf.OVERTIME,r['id']))
                        self.writeConn.commit()
                        self.logger.error('%s overtime!' %r['id'])
                        continue

                    #更新last_checked_time为当前时间
                    self.writeCur.execute(" update  %s  set  last_checked_time = now()  where id=%s" %(conf.QUEUE_TABLE, r['id']))
                    self.writeConn.commit()

                    #sleep before start the process
                    self.logger.info('sleep %s seconds before start checking job id %s' % (conf.TIME_WAITING_PER_PROCESS, r['id']))
                    time.sleep(conf.TIME_WAITING_PER_PROCESS)

                    cmd="nohup python %s/run_task_single.py -l %s  -c>>../../../log/system_log/all_app/%s.txt 2>&1 &" %(conf.BIN_PATH,r['id'],r['id'])
                    self.logger.info('checking:%s' %cmd)
                    ret = os.system(cmd)

                    #更新last_checked_time为当前时间
                    self.writeCur.execute(" update  %s  set  last_checked_time = now()  where id=%s" %(conf.QUEUE_TABLE, r['id']))
                    self.writeConn.commit()

                    msg="app:%s,module:%s,stat_date:%s for queue id:%s" %(r['app_name'],r['run_module'],r['stat_date'],r['id'])

                    if ret:
                        self.logger.error('%s check failed!' %msg)
                    else:
                        self.logger.info('%s check end!' %msg)
            else:
                self.logger.info('no queue to check, exit the checking process')

        except Exception, e:
            self.logger.error(str(e))
            self.logger.exception("check exception occurred!")
            self.mmsMysqlRead.conn_close()
            self.mmsMysqlWrite.conn_close()

        #close the connection before exit
        self.mmsMysqlRead.conn_close()
        self.mmsMysqlWrite.conn_close()
        sys.exit(0)

if __name__ == '__main__':
    cmd_args = cmdArgsDef()
    type=cmd_args.type
    runner = Runner()
    if type=='check':
        now = time.time()
        now_struct=time.localtime(now)
        now_hour=int(time.strftime('%H',now_struct))
        check_hour = conf.RUNNER_CHECK_DAY_HOUR
        if now_hour<check_hour:
            sys.exit(0)

        runner.check()
    else:
        runner.run()
