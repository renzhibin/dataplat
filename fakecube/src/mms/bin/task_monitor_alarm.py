#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
import os,time
os.environ['TZ'] = 'Asia/Shanghai'
time.tzset()
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import env as conf
import time,math,datetime,calendar
from email.mime.text import MIMEText
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
from Message4Me import Message4Me
import app_conf as appObj

dir_path = conf.CONF_PATH
'''
配置常量
'''
FAIL_NUM=5
WAITING_NUM=5
RUNNING_NUM=5
RATIO_NUM=30
OVERTIME=1
BIG_DATA_NUM=500000
TO_EMAIL_LIST=['houyangyang', 'yangyulong', 'yangzongqiang']#di-alarm
#TO_EMAIL_LIST=['di-alarm']

START="start"
CHECK_OK='ok'
CHECK_EXCEPT='except'

'''
每天9点开始任务，每1小时检查一次（如果没有异常情况就停止轮询）
异常情况为：
    任务阻塞数大于10
    任务失败数大于10
    今日任务数与昨日任务数变化率超过30%

如果出现异常情况发送告警邮件
'''


mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
conn=mmsMysql.get_conn()
cur=mmsMysql.get_cur()






base_sql=" (select t2.* from " \
         " (select max(id) id,app_name,run_module run_module from mms_run_log" \
         " where create_time>='%s' and create_time<='%s' group by app_name,run_module,stat_date,creater) t1 " \
         " left join " \
         " (select * from mms_run_log where create_time>='%s' and create_time<='%s')t2 " \
         " on t1.id=t2.id" \
         " )t "

base_system_sql=" (select t2.* from " \
         " (select max(id) id,app_name,run_module run_module from mms_run_log" \
         " where create_time>='%s' and create_time<='%s' and creater is null group by app_name,run_module,stat_date) t1 " \
         " left join " \
         " (select * from mms_run_log where create_time>='%s' and creater is null and create_time<='%s')t2 " \
         " on t1.id=t2.id" \
         " )t "

base_manual_sql=" (select t2.* from " \
         " (select id,app_name,run_module run_module from mms_run_log" \
         " where create_time>='%s' and create_time<='%s' and creater is not null) t1 " \
         " left join " \
         " (select * from mms_run_log where create_time>='%s' and creater is not null and create_time<='%s')t2 " \
         " on t1.id=t2.id" \
         " )t "

base_manual_uniq_sql=" (select t2.* from " \
         " (select max(id) id,app_name,run_module run_module from mms_run_log" \
         " where create_time>='%s' and create_time<='%s' and creater is not null group by app_name,run_module,stat_date) t1 " \
         " left join " \
         " (select * from mms_run_log where create_time>='%s' and creater is not null and create_time<='%s')t2 " \
         " on t1.id=t2.id" \
         " )t "
def get_check_sign(date=None):
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()

    if not date:
        date=str(time.strftime("%Y%m%d",time.localtime(int(time.time())-86400)))

    sql="select log_id stat_date,app_name status from mms_run_monitor where log_id='%s'"%(date)

    cur.execute(sql)

    columns=cur.description

    result=[]
    tmp=dict()
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    return result

#获取所有线上报表
def get_all_online_report():
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()

    sql="select * from t_visual_table where flag=1"

    cur.execute(sql)

    columns=cur.description

    result=[]
    tmp=dict()
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    return result

#获取所有线上运行任务
def get_all_run_tasks():
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    now=int(time.mktime(time.localtime(time.time())))
    sql='select id,date_s,date_e,date_n,creater,appname,create_time,priority,`explain`,cn_name,storetype,if(editor is not null,editor,creater) editor from mms_conf order by priority desc';
    cur.execute(sql)

    columns = cur.description
    result=[]
    for value in cur.fetchall() :
        tmp={}

        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        if  tmp['date_s'] is not None and time.mktime(tmp['date_s'].timetuple()) > now  :
            continue
        if  tmp['date_e'] is not None and time.mktime(tmp['date_e'].timetuple()) < now :
            continue
        result.append(tmp)
    conn.close()
    run_tasks_list=[]
    for e in result:
        project=e['appname']
        appConf=appObj.AppConf(project)
        run_con=appConf.get_run_list()
        tmp_run_tasks_list=['%s.%s'%(project,e['name']) for e in run_con['run_instance']['group']]
        run_tasks_list+=tmp_run_tasks_list
    return run_tasks_list

def update_check_sign(date=None,status=''):

    meta_mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
    meta_conn=meta_mmsMysql.get_conn()
    meta_cur=meta_mmsMysql.get_cur()

    if not date:
        date=str(time.strftime("%Y%m%d",time.localtime(int(time.time())-86400)))
    sql="update mms_run_monitor set app_name='%s' where log_id='%s'"%(status,date)
    if START==status:
        sql="insert into  mms_run_monitor (log_id,app_name) values ('%s','%s')"%(date,status)

    meta_cur.execute(sql)
    meta_conn.commit()
    meta_conn.close()


def get_total_task_num(startTime=None,endTime=None):
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    date_now=datetime.datetime.now().strftime('%Y-%m-%d')
    if not startTime:
        startTime='%s 00:00:00'%(date_now)
    if not endTime:
        endTime='%s 23:59:59'%(date_now)
    sql="select count(1) total_num from mms_run_log where create_time>='%s' and create_time<='%s'"%(startTime,endTime)

    cur.execute(sql)

    columns=cur.description

    result=[]
    tmp=dict()
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    return result


def get_task_list(status=1,startTime=None,endTime=None):
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    date_now=datetime.datetime.now().strftime('%Y-%m-%d')
    if not startTime:
        startTime='%s 00:00:00'%(date_now)
    if not endTime:
        endTime='%s 23:59:59'%(date_now)

    sql="select t.* from "+base_sql+" where t.status=%s "

    sql=sql%(startTime,endTime,startTime,endTime,status)
    if int(status)==int(conf.WAITING):
        sql+=' or t.status=9 '
    cur.execute(sql)

    columns=cur.description

    result=[]
    tmp=dict()
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    return result

def get_overtime_tasks(startTime=None,endTime=None,interval='hour',num=1,sql_type='system'):
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    date_now=datetime.datetime.now().strftime('%Y-%m-%d')
    if not startTime:
        startTime='%s 00:00:00'%(date_now)
    if not endTime:
        endTime='%s 23:59:59'%(date_now)

    today=str(time.strftime("%Y-%m-%d",time.localtime(int(time.time()))))

    tmp_sql=base_system_sql
    if sql_type=='manual':
        tmp_sql=base_manual_sql
    elif sql_type=='all':
        tmp_sql=base_sql

    sql="select t.* from "+tmp_sql+" where (t.status=%s or t.status=%s) and timestampdiff(%s,t.start_time,t.end_time)>%s and start_time>'%s'"
    sql=sql%(startTime,endTime,startTime,endTime,conf.SUCCESS,conf.WARNING,interval,num,today)
    cur.execute(sql)

    columns=cur.description

    result=[]
    tmp=dict()
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    return result


def get_bigdata_tasks(startTime=None,endTime=None,lines=1000000,sql_type='system'):
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    date_now=datetime.datetime.now().strftime('%Y-%m-%d')
    if not startTime:
        startTime='%s 00:00:00'%(date_now)
    if not endTime:
        endTime='%s 23:59:59'%(date_now)

    tmp_sql=base_system_sql
    if sql_type=='manual':
        tmp_sql=base_manual_sql
    elif sql_type=='all':
        tmp_sql=base_sql

    sql="select t.* from "+tmp_sql+" where (t.status=%s or t.status=%s) and t.data_size>=%s"
    sql=sql%(startTime,endTime,startTime,endTime,conf.SUCCESS,conf.WARNING,lines)

    cur.execute(sql)

    columns=cur.description

    result=[]
    tmp=dict()
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    return result


#sql_type=system查询系统例行任务，manual为手动提交任务,all 所有任务
def get_task_status_num(startTime=None,endTime=None,sql_type='system'):
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    date_now=datetime.datetime.now().strftime('%Y-%m-%d')
    if not startTime:
        startTime='%s 00:00:00'%(date_now)
    if not endTime:
        endTime='%s 23:59:59'%(date_now)
    tmp_sql=base_system_sql
    if sql_type=='manual':
        tmp_sql=base_manual_sql
    elif sql_type=='manual_uniq':
        tmp_sql=base_manual_uniq_sql
    elif sql_type=='all':
        tmp_sql=base_sql
    sql="select sum(if(t.status=1,1,0)) wait_num, sum(if(t.status=2,1,0)) ready_num,sum(if(t.status=3,1,0)) running_num,sum(if(t.status=4,1,0)) hived_num,"
    sql+="sum(if(t.status=5,1,0)) success_num,sum(if(t.status=6,1,0)) fail_num,sum(if(t.status=7,1,0)) warning_num,sum(if(t.status=8,1,0)) overtime_num,count(1) total_num, "
    sql+="sum(if(t.status=2,1,0)) ready_num,sum(if(t.status=11,1,0)) kill_num "
    sql+="from "+tmp_sql%(startTime,endTime,startTime,endTime)

    cur.execute(sql)

    columns=cur.description

    result=[]
    tmp=dict()
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    return result

#获取昨天任务下线和任务新增情况
def get_yesterday_on_off_task_num():
    pre_day=time.strftime('%Y-%m-%d',time.localtime(time.time()-86400))
    start_time='%s 00:00:00'%(pre_day)
    end_time='%s 23:59:59'%(pre_day)
    off,on=get_off_on_tasks(start_time,end_time)
    print off,len(off)
    print on,len(on)
    return (len(off),len(on))

#上周五到昨天任务下线和任务新增情况
def get_friday_now_on_off_task_num():
    date_now=datetime.datetime.now()
    pre_friday=date_now#date_now-datetime.timedelta(days=date_now.weekday()+3)

    oneday=datetime.timedelta(days=1)
    if pre_friday.weekday()==calendar.FRIDAY:
        pre_friday-=oneday
    while pre_friday.weekday()!=calendar.FRIDAY:
        pre_friday-=oneday

    pre_day=date_now-datetime.timedelta(days=1)
    pre_day=pre_day.strftime('%Y-%m-%d')

    pre_friday  = pre_friday.strftime('%Y-%m-%d')
    start_time='%s 00:00:00'%(pre_friday)
    end_time='%s 23:59:59'%(pre_day)
    off,on=get_off_on_tasks(start_time,end_time)

    return (len(off),len(on))

#时间段内新增任务和下线任务
def get_off_on_tasks(startTime,endTime):
    import json
    sql='''
        select t.* from
      (select * from t_visual_behavior_log where cdate>='%s' and cdate<='%s') t
      where t.user_action like '%s' or t.user_action like '%s' order by cdate
    '''%(startTime,endTime,'/fakecube/saveproject/%','/fakecube/offtask/%')
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    cur.execute(sql)
    columns=cur.description
    result=[]
    tmp=dict()
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    off_list=[]
    add_list=[]
    new_add_list=[]
    for e in result:
        off_list=list(set(off_list))
        add_list=list(set(add_list))

        params=json.loads(e['param'])
        project=params['project']
        old=params['old_run']
        new=params['new_run']
        all=[]
        if params.has_key('all'):
            all=params['all']

        #下线
        for o in old:
            if o not in new:
                tmp_p_o='%s.%s'%(project,o)
                if tmp_p_o in add_list:
                    pass
                    #add_list.remove(tmp_p_o)
                off_list.append(tmp_p_o)
        #新增
        for n in new:
            if params.has_key('all'):
                all=params['all']
                if n not in all:
                    new_add_list.append('%s.%s'%(project,n))
            else:
                if not old:
                    new_add_list.append('%s.%s'%(project,n))

            if n not in old:
                tmp_p_n='%s.%s'%(project,n)
                if tmp_p_n in off_list:
                    pass
                    #off_list.remove(tmp_p_n)
                new_add_list.append(tmp_p_n)

    return (list(set(off_list)),list(set(new_add_list)))
def get_friday_now_on_off_report_num():
    date_now=datetime.datetime.now()
    pre_friday=date_now#date_now-datetime.timedelta(days=date_now.weekday()+3)
    oneday=datetime.timedelta(days=1)
    if pre_friday.weekday()==calendar.FRIDAY:
        pre_friday-=oneday
    while pre_friday.weekday()!=calendar.FRIDAY:
        pre_friday-=oneday

    pre_friday=pre_friday.strftime('%Y-%m-%d')
    pre_day=date_now-datetime.timedelta(days=1)
    pre_day=pre_day.strftime('%Y-%m-%d')

    start_time='%s 00:00:00'%(pre_friday)
    end_time='%s 23:59:59'%(pre_day)
    off,on=get_off_on_report_num(startTime=start_time,endTime=end_time)
    return (off,on)
def get_yesterday_on_off_report_num():
    pre_day=time.strftime('%Y-%m-%d',time.localtime(time.time()-86400))
    start_time='%s 00:00:00'%(pre_day)
    end_time='%s 23:59:59'%(pre_day)
    off,on=get_off_on_report_num(startTime=start_time,endTime=end_time)
    return (off,on)
def get_off_on_report_num(startTime,endTime):
    deletetip='/report/deletereport/table_id/%'
    addtip='/report/addreport/table_id/%'
    onlinetip='/report/onlinereport/table_id/%'
    sql='''
        select sum(if(t.user_action like '%s',1,0)) deletereport,sum(if(t.user_action like '%s',1,0)) addreport,sum(if(t.user_action like '%s',1,0)) onlinerreport from

        (select distinct user_action from t_visual_behavior_log where cdate>='%s' and cdate<='%s')t
        where t.user_action like '%s' or t.user_action like '%s' or t.user_action like '%s'
    '''%(deletetip,addtip,onlinetip,startTime,endTime,deletetip,addtip,startTime)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    cur.execute(sql)
    columns=cur.description
    result=[]
    tmp=dict()
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    on=0
    off=0
    if len(result)>0:
        result=result[0]
        if result['addreport']:
            on=int(result['addreport'])
        if result['deletereport']:
            off=int(result['deletereport'])
        return off,on
    return (off,on)

def get_menu_tables_num():
    import json
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    sql='''
        select * from t_visual_menu where flag=1

    '''
    cur.execute(sql)
    columns = cur.description
    result=[]
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    t_ids=[]
    for e in result:
        if e['table_id']:
            tmp=e['table_id']
            tmp=json.loads(tmp)
            for i in tmp:
                if i.has_key('id'):
                    if i['id']:
                        t_ids.append(i['id'])
    return len(set(t_ids))

#高优先级项目6点完成情况报警
def high_priority_task_alarm(startTime=None,endTime=None):

    now_hour=str(int(time.strftime("%H",time.localtime(time.time()))))

    #默认6点开始检查
    if conf.TASK_CHECK_HOUR==int(now_hour):
        date_now=datetime.datetime.now().strftime('%Y-%m-%d')
        if not startTime:
            startTime='%s 00:00:00'%(date_now)
        if not endTime:
            endTime='%s 23:59:59'%(date_now)

        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()

        tmp_table=base_system_sql%(startTime,endTime,startTime,endTime)
        sql='''
            select count(1) normal_task_num,
              sum(if(t.priority in (8,9,10),1,0)) special_task_num,
              status
            from
              %s  group by t.status
        '''%(tmp_table)

        cur.execute(sql)

        columns=cur.description

        result=[]
        tmp=dict()
        for value in cur.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)
        conn.close()

        success_special=0
        total_special=0
        success_normal=0
        total_normal=0
        normal_ttl=0
        special_ttl=0

        for item in result:
            if conf.SUCCESS==int(item['status']):
                success_normal=int(item['normal_task_num'])
                success_special=int(item['special_task_num'])

            total_normal=total_normal+int(item['normal_task_num'])
            total_special=total_special+int(item['special_task_num'])

        #如果优先级高任务和优先级低任务成功比例低于一定阀值报警

        if total_normal>0:
            normal_ttl=int((float(success_normal)/float(total_normal))*100)
        if total_special>0:
            special_ttl=int((float(success_special)/float(total_special))*100)

        #TTL值基于天级别插入时间动态计算， 以4点插入时间TTL经验值为基准
        #小时级别＋天级别所占时间比例/总时间比例
        #base  8/12 =  (0-6 小时任务 ＋ 4-6 天级别任务)/(2*6 小时区间)
        base = int(float(8)/float(12)*100)
        if conf.TASK_CHECK_HOUR < conf.RUNNER_CHECK_DAY_HOUR:
            #天级别插入时间滞后 只包括检查时段的小时级别
            divide = conf.TASK_CHECK_HOUR
        else:
            divide = conf.TASK_CHECK_HOUR * 2 - conf.RUNNER_CHECK_DAY_HOUR

        actual = int(float(divide)/float(conf.TASK_CHECK_HOUR * 2)*100)
        proportion = float(actual)/float(base)
        #基本任务在算完比例的基础上降低
        normal_ttl_new = int(conf.NORMAL_TTL * proportion*0.9)
        special_ttl_new = int(conf.SPECIAL_TTL * proportion)

        '''
        if normal_ttl_new>normal_ttl or special_ttl_new>special_ttl or True:
            #发送短信告警
            alarm_content='{}高优先级任务完成占比：预设（{}%）实际（{}%）,所有任务完成占比：预设（{}%）实际（{}%）。－data平台'
            alarm_content=alarm_content.format(str(date_now),str(special_ttl_new),str(special_ttl),str(normal_ttl_new),str(normal_ttl))
            alarm_iphone=['13716186230']
            for i in alarm_iphone:
                m4m=Message4Me()
                m4m.add_to(i)
                m4m.add_var('app_name', 'Data平台')
                m4m.add_var('content', alarm_content)
                m4m.xsend()
            # mmsSMs=MmsSMS()
            # mmsSMs.sendSMS('bangzhongpeng',alarm_content)
            # mmsSMs.sendSMS('zhibinren',alarm_content)
            # mmsSMs.sendSMS('haiyuanhuang',alarm_content)
            print '6点任务完成占比告警'

        print '6点任务完成占比检查'
        '''



#阻塞任务报警高优先级任务9点，低优先级12点
def wait_task_alarm(startTime=None,endTime=None):
    now_hour=str(int(time.strftime("%H",time.localtime(time.time()))))

    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()

    date_now=datetime.datetime.now().strftime('%Y-%m-%d')
    if not startTime:
        startTime='%s 00:00:00'%(date_now)
    if not endTime:
        endTime='%s 23:59:59'%(date_now)

    sql='''
              select t1.*,t2.creater,t2.editor
              from
              (select * from mms_run_log where create_time>='%s' and create_time<='%s' and priority  %s in (8,9,10) and status not in (5,6,7,8,11) and creater is null and schedule_level='day')t1
              left join
              mms_conf t2
              on t1.app_name=t2.appname
        '''

    #9点开始检查高优先级项目阻塞
    if 9==int(now_hour):
        sql=sql%(startTime,endTime,'')
        cur.execute(sql)
        columns=cur.description
        result=[]
        tmp=dict()
        for value in cur.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)
        conn.close()

        for item in result:
            tmp_list=[item['creater'],item['editor']]
            tmp_list=list(set(tmp_list))
            run_module=item['run_module']
            app_name=item['app_name']
            alarm_content='%s 9点项目：%s 下高优先级任务%s 没有结束，请及时处理。－data平台'
            alarm_content=alarm_content%(str(date_now),app_name.encode('utf-8'),run_module.encode('utf-8'))
            mmsSMs=MmsSMS()
            mmsSMs.sendSMS('bangzhongpeng',alarm_content)
            mmsSMs.sendSMS('zhibinren',alarm_content)

            for tmp_e in tmp_list:
                if tmp_e:
                    mmsSMs.sendSMS(tmp_e.encode('utf-8'),alarm_content)

            print '9点高优先级任务阻塞报警'


    #12开始检查普通项目阻塞
    if 12==int(now_hour):
        sql=sql%(startTime,endTime,'not')
        print sql
        cur.execute(sql)
        columns=cur.description
        result=[]
        tmp=dict()
        for value in cur.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)
        conn.close()

        creater_task={}
        for item in result:
            c=item['creater'].encode('utf-8')
            app_name=item['app_name'].encode('utf-8')
            if not creater_task.has_key(c):
                creater_task[c]={}
            if not creater_task[c].has_key(app_name):
                creater_task[c][app_name]=[]

            creater_task[c][app_name].append(item['run_module'])


        from mms_email import MmsEmail
        mmsEmail=MmsEmail()

        for k,v in creater_task.items():
            send_user=[k.encode('utf-8'),'data_alarm']
            mail_content=''
            app_content=''
            for k2,v2 in v.items():
                run_module_tmp=','.join(v2)
                app_content_tmp='<b>项目：%s 下任务%s 没有结束。</b></br>'%(k2.encode('utf-8'),run_module_tmp.encode('utf-8'))
                app_content+=app_content_tmp

            mail_content+=app_content

            to_list=send_user
            mail_sub="【监控】data平台任务阻塞报警 %s"%(date_now)
            mail_content=MIMEText(mail_content,"html","utf-8")
            mmsEmail.sendmessage(to_list,mail_sub,mail_content)
            print '报警'


#如果改用户已经报警过
def save_colleague_status(leave_list):
    tmp=[]
    for i in leave_list:
        tmp.append(('leave',str(i)))
    meta_mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
    meta_conn=meta_mmsMysql.get_conn()
    meta_cur=meta_mmsMysql.get_cur()
    sql="insert into  mms_run_monitor (log_id,app_name,mob_name) values ('0',%s,%s)"
    meta_cur.executemany(sql,tmp)
    meta_conn.commit()
    meta_conn.close()

def get_leave_colleague():
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()

    sql="select mob_name from mms_run_monitor where app_name='leave' and log_id='0'"

    cur.execute(sql)

    result=[]
    for value in cur.fetchall():
        result.append(value[0].encode('utf-8'))
    conn.close()
    return result

def get_colleague_flower():
    flower_mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_DANDELION)
    conn=flower_mmsMysql.get_conn()
    cur=flower_mmsMysql.get_cur()

    sql='''
select t1.spelling username,t1.u_type u_type,t2.task_name  app_name
from
(select spelling,'flower' u_type,author_id from dandelion_author)t1
join
(select * from dandelion_baby where status=2)t2
on t1.author_id=t2.author_id

    '''

    cur.execute(sql)
    columns=cur.description
    result=[]
    tmp=dict()
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()
    return result



#扫描离职同事报警
def scan_colleague_status_alarm():
    now_hour=str(int(time.strftime("%H",time.localtime(time.time()))))
    if 10==int(now_hour):
        mmsMysql_slave=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        conn=mmsMysql_slave.get_conn()
        cur=mmsMysql_slave.get_cur()
        checked_leave_list=get_leave_colleague()
        sql='''
            select distinct t.username,t.u_type,t.app_name app_name,t.cn_name cn_name from
            (
            select distinct creater username,'app' u_type,appname app_name,cn_name cn_name from mms_conf where creater!=''
            union
            select distinct creater username,'report' u_type,cn_name app_name,'cn_name' cn_name from t_visual_table where creater!='' and flag=1
            )t
        '''
        cur.execute(sql)
        columns=cur.description
        result=[]
        tmp=dict()
        for value in cur.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)
        conn.close()
        app_leave_list={}
        report_leave_list={}
        flower_leave_list={}
        flower_result=get_colleague_flower()
        result=result+flower_result
        leave_colleague_map={}
        for i in result:
            user_name=i['username'].encode('utf-8')
            if not user_name:
                continue
            app_name=i['app_name'].encode('utf-8')
            name_type=user_name+'_'+i['u_type']
            if not check_colleague_status(user_name):
                #if name_type not in checked_leave_list:
                    # save_colleague_status(user_name,app_name)
                    #leave_colleague_map[name_type]=name_type
                if 'app'==i['u_type']:
                    if not app_leave_list.has_key(user_name):
                        app_leave_list[user_name]=[]
                    tmp_app_name=str(i['cn_name'].encode('utf-8'))+'('+app_name+')'
                    app_leave_list[user_name].append(tmp_app_name)
                if 'report'==i['u_type']:
                    if not report_leave_list.has_key(user_name):
                        report_leave_list[user_name]=[]
                    report_leave_list[user_name].append(app_name)
                if 'flower'==i['u_type']:
                    if not flower_leave_list.has_key(user_name):
                        flower_leave_list[user_name]=[]
                    flower_leave_list[user_name].append(app_name)
        #save_colleague_status(leave_colleague_map.keys())
        report_mail_content='<b>data平台报表负责人离职名单</b>：<ul>'
        app_mail_content='<b>data平台项目负责人离职名单</b>：<ul>'
        flower_mail_content='<b>flower负责人离职名单</b>：<ul>'
        content='您如果收到该邮件表示您有下属离职且没有交接data平台或者flower的相关工作，请回复该邮件提供交接人员名单，多谢您的配合和理解。</br></br>'
        to_list=['yushigao','data_alarm']#'yushigao','di-alarm'
        for k,v in report_leave_list.items():
            name_tmp=get_superior(str(k))
            if not name_tmp:
                name_tmp=''
            if name_tmp:
                to_list.append(name_tmp)
            tmp='<li><b>'+str(k)+'(上级:'+name_tmp+')</b>负责报表：'+','.join(v)+'</li>'
            report_mail_content+=tmp
        report_mail_content+='</ul>'
        for k,v in app_leave_list.items():

            name_tmp=get_superior(str(k))
            if not name_tmp:
                name_tmp=''
            else:
                to_list.append(name_tmp)
            print name_tmp
            tmp='<li><b>'+str(k)+'(上级:'+name_tmp+')</b>负责项目：'+','.join(v)+'</li>'
            app_mail_content+=tmp
        app_mail_content+='</ul>'
        for k,v in flower_leave_list.items():
            name_tmp=get_superior(str(k))
            if not name_tmp:
                name_tmp=''
            else:
                to_list.append(name_tmp)
            tmp='<li><b>'+str(k)+'(上级:'+str(name_tmp)+')</b>负责流程：'+','.join(v)+'</li>'
            flower_mail_content+=tmp
        flower_mail_content+='</ul>'

        if len(app_leave_list)>0 or len(report_leave_list)>0 or len(flower_leave_list)>0:
            if len(app_leave_list)>0:
                content+=app_mail_content
            if len(report_leave_list)>0:
                content+=report_mail_content
            if len(flower_leave_list)>0:
                content+=flower_mail_content

            from mms_email import MmsEmail
            mmsEmail=MmsEmail()
            mail_sub="【监控】负责人员离职报警"
            print content
            content=MIMEText(content,"html","utf-8")
            print content
            print to_list
            mmsEmail.sendmessage(to_list,mail_sub,content)



#离职返回False
def check_colleague_status(name):
    if name:
        import requests,json

        params={}
        params['mail']=name
        params['token']='a595a9bd9909e72a792bb535379ed477'
        url='http://api.speed.meilishuo.com/user/show'
        response=requests.get(url,params=params)
        if 200==int(response.status_code):
            user_inf=json.loads(response.text)
            if 200==int(user_inf['code']):
                if len(user_inf['data'])>0:
                    if user_inf['data'].has_key('status'):
                        if 1==int(user_inf['data']['status']):
                            return True
        else:
            return True

    return False

def get_superior(name):
    if name:
        import requests,json

        params={}
        params['mail']=name
        params['token']='a595a9bd9909e72a792bb535379ed477'
        url='http://api.speed.meilishuo.com/user/show'
        response=requests.get(url,params=params)
        if 200==int(response.status_code):
            user_inf=json.loads(response.text)
            if 200==int(user_inf['code']):
                if len(user_inf['data'])>0:
                    if user_inf['data'].has_key('direct_leader'):
                        dparams={}
                        direct_leader_id=''
                        if user_inf['data']['direct_leader']:
                            direct_leader_id=int(user_inf['data']['direct_leader'])
                        dparams['id']=direct_leader_id
                        dparams['token']= params['token']
                        response=requests.get(url,params=dparams)
                        if 200==int(response.status_code):
                            user_inf=json.loads(response.text)
                            if len(user_inf['data'])>0:
                                if user_inf['data'].has_key('mail'):
                                        return user_inf['data']['mail'].split('@')[0].encode('utf-8')

    return None

def getHtmlTable(list=None):
    status_map={
        1:'WAITING',
        2:'READY',
        3:'RUNNING',
        4:'HIVEEND',
        5:'SUCCESS',
        6:'FAILED',
        7:'WARNING',
        8:'OVERTIME',
        9:'CHECKING',
        11:'KILLED'
    }
    title_en=['id','app_name','run_module','stat_date','start_time','end_time','status','data_size','load_time_spend','submitter','creater']
    title_cn=['id','项目名','模块','执行日期','开始时间','结束时间','执行结果','导入行数','导入用时','创建人','启动方式']
    if not list:
        list=[]
    table_title_td=""""""
    for item in title_cn:
        table_title_td+="""<th class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">%s</th>"""%(str(item))
    table_title="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">%s</tr>"""%(table_title_td)

    list_tr=""
    tr_row = 1
    for item in list:
        td_tmp=""""""
        for index in title_en:
            if 'status'==index:
                td_tmp+="""<td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">%s</td>"""%(str(status_map[item[index]]))
            elif 'creater'==index:
                if item[index]==None:
                   td_tmp+="""<td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">%s</td>"""%('system')
                else:
                   td_tmp+="""<td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">%s</td>"""%('manual')
            else:
                td_tmp+="""<td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">%s</td>"""%(item[index])
        if tr_row % 2 == 1:
            list_tr+="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0; font-size: 12px;">%s</tr>"""%(td_tmp)
        elif tr_row % 2 == 0:
            list_tr += """<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">%s</tr>""" % (td_tmp)
        tr_row = tr_row + 1

    table="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
                <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
                    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%%;">
                        <tbody>
                            %s %s
                        </tbody>
                    </table>
                </td>
            </tr>"""%(table_title.decode("utf-8"),list_tr.decode("utf-8"))
    return table

#获取所有阻塞状态任务个数
def get_all_wait_tasks():
    mmsMysql_slave=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql_slave.get_conn()
    cur=mmsMysql_slave.get_cur()

    sql='''
        select sum(if(creater is null,1,0)) system_wait,sum(if(creater is not null,1,0)) manual_wait,count(1) all_wait from mms_run_log where status=1
    '''
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








def getEmailHtmlContent(status_list=None,manual_status_list=None,all_status_list=None,status_inf=''):

    if not status_list:
        return ""
    if not manual_status_list:
        return ""
    if not all_status_list:
        return ""
    func=lambda x:x if x else 0
    #运行时间超过1小时
    overtime_list=get_overtime_tasks(num=OVERTIME)
    bigdata_list=get_bigdata_tasks(lines=BIG_DATA_NUM)

    manual_overtime_list=get_overtime_tasks(num=OVERTIME,sql_type='manual')
    manual_bigdata_list=get_bigdata_tasks(lines=BIG_DATA_NUM,sql_type='manual')
    wait_task_num_list=get_all_wait_tasks()
    total_task=get_total_task_num()
    func=lambda x:int(x) if x else 0
    total_success_task=func(status_list[0]['success_num'])+func(status_list[0]['warning_num'])
    total_run_task=func(status_list[0]['ready_num'])+func(status_list[0]['wait_num'])+func(status_list[0]['running_num'])

    manual_total_success_task=func(manual_status_list[0]['success_num'])+func(manual_status_list[0]['warning_num'])
    manual_total_run_task=func(manual_status_list[0]['ready_num'])+func(manual_status_list[0]['wait_num'])+func(manual_status_list[0]['running_num'])
    manual_total_uniq_num=get_task_status_num(sql_type='manual_uniq')
    yes_off,yes_on=get_yesterday_on_off_task_num()
    friday_off,friday_on=get_friday_now_on_off_task_num()

    yes_off_report,yes_on_report=get_yesterday_on_off_report_num()
    friday_off_report,friday_on_report=get_friday_now_on_off_report_num()
    all_hql_num=len(get_all_run_tasks())
    all_report_num=len(get_all_online_report())
    menu_tables_num=get_menu_tables_num()

    system_wait_num=func(wait_task_num_list[0]['system_wait'])
    manual_wait_num=func(wait_task_num_list[0]['manual_wait'])
    overview_text = """
    <table style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0; width: 100%%;" cellspacing="0" cellpadding="0">
    <tbody><tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <td class="alert alert-warning" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 20px; font-size: 14px; color: #fff; font-weight: bold; text-align: left; border-radius: 3px 3px 0 0; background: #ff9f00;">
    <h2 class="title" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; padding: 0; font-weight: 200; line-height: 1.2em; font-size: 28px; margin: 0; color: #fff; text-align: left;">FakeCube 任务运行概况报告 %(reportTime)s</h2>
    </td></tr>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;运行概览</strong>
    </h4></td></tr>
    <!--概览表格区域-->
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%%;">
    <tbody>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        任务整体运行情况
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; font-weight:bold; color:#FF0800"">
        %(runStatus)s
    </td>
    </tr>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        任务总数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(taskTotal)s
    </td>
    </tr>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        去重后任务总数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(distinctTaskTotal)s
    </td>
    </tr>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        线上hql总数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(onlineHqlTotal)s
    </td>
    </tr>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        线上报表总数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(onlineReportTotal)s
    </td>
    </tr>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        菜单下的报表总数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
    %(tablesNumber)s
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    <!--例行/手动表格区域-->
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;任务运行状况</strong>
    </h4></td></tr>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%%;">
    <tbody>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <th class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
        类目
    </th>
    <th class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
        例行任务
    </th>
    <th class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
        手动任务
    </th>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        任务总数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemTaskTotal)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualTaskTotal)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        去重任务总数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemDistinctTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualDistinctTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        失败任务数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemFailTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualFailTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        杀死任务数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemKillTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualKillTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        非结束态任务总数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemRunningTotal)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualRunningTotal)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        <li>就绪任务数</li>
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemReadyTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualReadyTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        <li>今日阻塞任务数</li>
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemTodayWaitingTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualTodayWaitingTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        <li>全部阻塞任务数</li>
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemWaitingTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualWaitingTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        <li>正在运行任务数</li>
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemRunningTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualRunningTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        <li>正在灌库任务数</li>
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemHivedTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualHivedTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        成功任务总数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemSuccessTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualSuccessTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        成功结束任务数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemSuccessStopTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualSuccessStopTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        警告任务数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemWarningTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualWarningTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        大数据任务数(行数超过50万)
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemBigDataTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualBigDataTask)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        超时任务数(运行时间超过1小时)
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(systemOvertimeTask)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(manualOvertimeTask)s
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>


    <!--HQL/报表表格区域-->
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;任务变化状况</strong>
    </h4></td></tr>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%%;">
    <tbody>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">

    <th colspan="2" class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
        昨日HQL变化情况
    </th>
    <th colspan="2" class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
        昨日报表变化情况
    </th>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        下线HQL数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(yesterdayOfflineHql)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        下线报表数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(yesterdayOfflineReport)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        新增HQL数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(yesterdayOnlineHql)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        新增报表数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(yesterdayOnlineReport)s
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>


    <!--HQL/报表(周)-->
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
    <table cellpadding="0" cellspacing="0" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%%;">
    <tbody>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">

    <th colspan="2" class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
        上周五至昨日HQL变化情况
    </th>
    <th colspan="2" class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
        上周五至昨日报表变化情况
    </th>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        下线HQL数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(weekOfflineHql)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        下线报表数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(weekOfflineReport)s
    </td>
    </tr>

    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        新增HQL数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(weekOnlineHql)s
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        新增报表数
    </td>
    <td class="txt-l" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
        %(weekOnlineReport)s
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>


    """ % {
        'reportTime': str(time.strftime("%Y-%m-%d",time.localtime(int(time.time())))),
        'runStatus': status_inf,
        'taskTotal': func(total_task[0]['total_num']),
        'distinctTaskTotal': func(all_status_list[0]['total_num']),
        'onlineHqlTotal': all_hql_num,
        'onlineReportTotal': all_report_num,
        'tablesNumber': str(menu_tables_num),


        'systemTaskTotal': func(status_list[0]['total_num']),
        'systemDistinctTask': func(status_list[0]['total_num']),
        'systemFailTask': func(status_list[0]['fail_num']),
        'systemKillTask' : func(status_list[0]['kill_num']),
        'systemRunningTotal' : total_run_task,
        'systemReadyTask' : func(status_list[0]['ready_num']),
        'systemTodayWaitingTask': func(status_list[0]['wait_num']),
        'systemWaitingTask' : system_wait_num,
        'systemRunningTask' : func(status_list[0]['running_num']),
        'systemHivedTask' : func(status_list[0]['hived_num']),
        'systemSuccessTask' : total_success_task,
        'systemSuccessStopTask' : func(status_list[0]['success_num']),
        'systemWarningTask' : func(status_list[0]['warning_num']),
        'systemBigDataTask' : len(bigdata_list),
        'systemOvertimeTask': len(overtime_list),

        'manualTaskTotal' : func(manual_status_list[0]['total_num']),
        'manualDistinctTask' : func(manual_total_uniq_num[0]['total_num']),
        'manualFailTask' : func(manual_status_list[0]['fail_num']),
        'manualKillTask' : func(manual_status_list[0]['kill_num']),
        'manualRunningTotal' : manual_total_run_task,
        'manualReadyTask' : func(manual_status_list[0]['ready_num']),
        'manualTodayWaitingTask' : func(manual_status_list[0]['wait_num']),
        'manualWaitingTask' : manual_wait_num,
        'manualRunningTask' : func(manual_status_list[0]['running_num']),
        'manualHivedTask' : func(manual_status_list[0]['hived_num']),
        'manualSuccessTask' : manual_total_success_task,
        'manualSuccessStopTask' : func(manual_status_list[0]['success_num']),
        'manualWarningTask' : func(manual_status_list[0]['warning_num']),
        'manualBigDataTask' : len(manual_bigdata_list),
        'manualOvertimeTask' : len(manual_overtime_list),

        'yesterdayOfflineHql' : yes_off,
        'yesterdayOnlineHql' : yes_on,
        'weekOfflineHql' : friday_off,
        'weekOnlineHql' :friday_on,
        'yesterdayOfflineReport' : yes_off_report,
        'yesterdayOnlineReport' : yes_on_report,
        'weekOfflineReport' : friday_off_report,
        'weekOnlineReport' : friday_on_report,
    }


    ready_list_desc="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;就绪任务列表</strong>
    </h4></td></tr>"""
    ready_list=get_task_list(conf.READY)
    ready_table=""
    if len(ready_list)>0:
        ready_table=ready_list_desc.decode("utf-8")+getHtmlTable(ready_list)


    block_list_desc="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;阻塞任务列表</strong>
    </h4></td></tr>"""
    block_list=get_task_list(conf.WAITING)
    block_table=""
    if len(block_list)>0:
        block_table=block_list_desc.decode("utf-8")+getHtmlTable(block_list)


    fail_list_desc="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;失败任务列表</strong>
    </h4></td></tr>"""
    fail_list=get_task_list(conf.FAILED)
    fail_table=""
    if len(fail_list)>0:
        fail_table=fail_list_desc.decode("utf-8")+getHtmlTable(fail_list)


    overtime_list_desc="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;超时任务列表</strong>
    </h4></td></tr>"""
    overtime_table=""
    if len(overtime_list)>0:
        overtime_table=overtime_list_desc.decode("utf-8")+getHtmlTable(overtime_list)


    bigdata_list_desc="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;大数据任务列表</strong>
    </h4></td></tr>"""
    bigdata_table=""
    if len(bigdata_list)>0:
        bigdata_table=bigdata_list_desc.decode("utf-8")+getHtmlTable(bigdata_list)


    warn_list_desc="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;警告任务列表</strong>
    </h4></td></tr>"""
    warn_list=get_task_list(conf.WARNING)
    warn_table=""
    if len(warn_list)>0:
        warn_table=warn_list_desc.decode("utf-8")+getHtmlTable(warn_list)

    running_list_desc="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;正在运行任务列表</strong>
    </h4></td></tr>"""
    running_list=get_task_list(conf.RUNNING)
    running_table=""
    if len(running_list)>0:
        running_table=running_list_desc.decode("utf-8")+getHtmlTable(running_list)

    success_list_desc="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 0;">
    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
    <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
    <strong>&gt;&gt;成功任务列表</strong>
    </h4></td></tr>"""
    success_list=get_task_list(conf.SUCCESS)
    success_table=""
    if len(success_list)>0:

        success_table=success_list_desc.decode("utf-8")+getHtmlTable(success_list)

    tableEndHtml ="""<tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
                    <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 20px 0px;">
                        <p style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0; font-size: 12px; font-weight: normal; margin-bottom: 10px;">
                            感谢您的订阅！任何问题，欢迎联系 数据团队
                            <a href="mailto:di@xiaozhu.com" target="_blank">
                                di@qud<wbr>ian.com
                            </a>
                        </p>
                    </td>
                </tr>

                <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
                    <td class="alert alert-warning alert-footer" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 20px; font-size: 14px; color: #fff; font-weight: bold; text-align: left; border-radius: 0 0 3px 3px; background: #ff9f00; line-height: 33px;">
                        <p class="footer" style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; padding: 0; font-weight: normal; margin-bottom: 10px; margin: 0; text-align: left; color: #fff; font-size: 16px; line-height: 33px;">
                            <span>祝您工作愉快</span>
                        </p>
                    </td>
                </tr>
                </tbody></table>"""

    content="%s %s %s %s %s %s %s %s %s %s"%(overview_text.decode("utf-8"),fail_table,ready_table,block_table,running_table,bigdata_table,overtime_table,warn_table,success_table,tableEndHtml.decode("utf-8"))
    msg=MIMEText(content,"html","utf-8")
    return msg


'''
start,except,ok
'''
def send_normal_mail():
    status_list=get_task_status_num()
    manual_status_list=get_task_status_num(sql_type='manual')
    all_status_list=get_task_status_num(sql_type='all')
    func =lambda x :x if x else 0
    waiting_num=int(func(all_status_list[0]['wait_num']))
    success_num=int(func(all_status_list[0]['success_num']))
    fail_num=int(func(all_status_list[0]['fail_num']))
    today_task_num=int(func(get_total_task_num()[0]["total_num"]))
    running_num=int(func(all_status_list[0]["running_num"]))

    #昨天成功任务数
    now_date=datetime.datetime.now()
    pre_date=now_date-datetime.timedelta(days=1)
    pre_date=pre_date.strftime('%Y-%m-%d')
    pre_start_time='%s 00:00:00'%(pre_date)
    pre_end_time='%s 23:59:59'%(pre_date)
    pre_status_list=get_total_task_num(startTime=pre_start_time,endTime=pre_end_time)

    pre_task_num=func(pre_status_list[0]['total_num'])

    ratio_num=int((math.fabs(pre_task_num-today_task_num)/pre_task_num if pre_task_num>0 else 0)*100)

    status_inf="正常"
    now_hour=str(int(time.strftime("%H",time.localtime(time.time()))))
    print waiting_num,fail_num,running_num
    if waiting_num >= WAITING_NUM or fail_num>=FAIL_NUM or running_num>=RUNNING_NUM:
        status_inf="异常"

        from mms_email import MmsEmail
        mmsEmail=MmsEmail()
        to_list=TO_EMAIL_LIST
        content=getEmailHtmlContent(status_list=status_list,manual_status_list=manual_status_list,all_status_list=all_status_list,status_inf=status_inf)

        stat_date=str(time.strftime("%Y-%m-%d",time.localtime(int(time.time()))))
        mail_sub="【监控】fakecube 任务运行概况报告 %s"%(stat_date)

        mmsEmail.sendmessage(to_list,mail_sub,content)
        print "发送告警邮件成功",str(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(int(time.time()))))
    elif now_hour in ['5','6','7','8','9','10','11','12']:
        from mms_email import MmsEmail
        mmsEmail=MmsEmail()
        to_list=TO_EMAIL_LIST
        content=getEmailHtmlContent(status_list=status_list,manual_status_list=manual_status_list,all_status_list=all_status_list,status_inf=status_inf)

        stat_date=str(time.strftime("%Y-%m-%d",time.localtime(int(time.time()))))
        mail_sub="【监控】fakecube 任务运行概况报告 %s"%(stat_date)
        mmsEmail.sendmessage(to_list,mail_sub,content)
        print "发送告警邮件成功",str(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(int(time.time()))))


def send_except_mail():

    status_list=get_task_status_num()
    manual_status_list=get_task_status_num(sql_type='manual')
    all_status_list=get_task_status_num(sql_type='all')

    func =lambda x :x if x else 0
    waiting_num=int(func(status_list[0]['wait_num']))
    success_num=int(func(status_list[0]['success_num']))
    fail_num=int(func(status_list[0]['fail_num']))
    today_task_num=func(get_total_task_num()[0]["total_num"])
    running_num=int(func(status_list[0]["running_num"]))

    #昨天成功任务数
    now_date=datetime.datetime.now()
    pre_date=now_date-datetime.timedelta(days=1)
    pre_date=pre_date.strftime('%Y-%m-%d')
    pre_start_time='%s 00:00:00'%(pre_date)
    pre_end_time='%s 23:59:59'%(pre_date)
    pre_status_list=get_total_task_num(startTime=pre_start_time,endTime=pre_end_time)

    pre_task_num=func(pre_status_list[0]['total_num'])


    ratio_num=int((math.fabs(pre_task_num-today_task_num)/pre_task_num if pre_task_num>0 else 0)*100)

    if waiting_num >= WAITING_NUM or fail_num>=FAIL_NUM or ratio_num>=RATIO_NUM or running_num>=RUNNING_NUM:
        from mms_email import MmsEmail
        mmsEmail=MmsEmail()
        to_list=TO_EMAIL_LIST
        content=getEmailHtmlContent(status_list=status_list,manual_status_list=manual_status_list,all_status_list=all_status_list,status_inf="异常")

        stat_date=str(time.strftime("%Y-%m-%d",time.localtime(int(time.time()))))
        mail_sub="fakecube 任务运行概括报告 %s"%(stat_date)
        mmsEmail.sendmessage(to_list,mail_sub,content)
        update_check_sign(status=CHECK_EXCEPT)
        print "发送告警邮件成功",str(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(int(time.time()))))
    else:
        update_check_sign(status=CHECK_OK)
        print "检查",str(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(int(time.time()))))
'''
WAITING=1
READY=2
RUNNING=3
HIVEEND=4  #hive执行完成
SUCCESS=5
FAILED=6
WARNING=7
OVERTIME=8
'''



if __name__=="__main__":

    print "定时检查",str(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(int(time.time()))))
    now_hour=str(int(time.strftime("%H",time.localtime(time.time()))))
    if int(now_hour)>4:
        send_normal_mail()
        '''
        check_sign=get_check_sign()
        if len(check_sign)<=0:
            update_check_sign(status=START)
            send_normal_mail()
        else:
            status=check_sign[0]['status']
            if CHECK_EXCEPT==str(status):
                send_normal_mail()
        '''
    #7点高优先任务
    high_priority_task_alarm()
    #9点12点阻塞任务
    wait_task_alarm()
    #10点离职人员
    # scan_colleague_status_alarm()
