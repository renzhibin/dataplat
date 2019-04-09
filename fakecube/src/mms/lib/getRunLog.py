#!/usr/bin/env python
#coding=utf-8

import re
import sys
from db_util import DbUtil
import env as conf
from utils import checkScheduleInterval
import app_conf as appObj


def getRunLog(filter_str=''):
    mydb=DbUtil()
    sql="""
         select
            id,
            app_name,
            run_module,
            stat_date,
            status,
            step,
            is_test,
            start_time,
            end_time,
            create_time,
            priority,
            data_size,
            round(load_time_spend,5) as load_time_spend
        from
            %s
        where true %s
        order by create_time desc
        limit %s
    """ %(conf.QUEUE_TABLE,filter_str,conf.LIMIT)
    # sql="select id,app_name,stat_date,start_time,end_time,status,create_time,log_file from mms_run_log where true %s order by start_time desc" %filter_str
    return mydb.select(sql)

def getAppList():
    mydb=DbUtil()
    sql="select appname from mms_conf order by appname"
    return mydb.select(sql)

def getLogMonitor(log_id):
    mydb=DbUtil()
    sql="select log_id,app_name,mob_name,time_spend,data_size from mms_run_monitor where log_id = %s"
    return mydb.select(sql,(log_id,))

def getHqlList(app_name,special_table=None,stat_date=None,modules=None):
    if not stat_date:
        stat_date=time.strftime('%Y-%m-%d',int(int(time.time())-86400))
    cg_list=[]
    hql2table=dict()
    interval2group=dict()
    run_modules=[]
    times2task=dict()
    offset2group=dict()

    appConf=appObj.AppConf(app_name)


    if not appConf.appExist:
        print 'project :%s not exist' %app_name
        return cg_list
    try:
        temp=appConf.appConf
        for cat in temp['project'][0]['categories']:
            for group in cat['groups']:
                hql_type=1
                if group.has_key('hql_type') and group['hql_type']:
                    hql_type=group['hql_type']
                hql=cat['name']+'.'+group['name']
                hql2table[hql]=[]
                if group.has_key('tables'):
                    for table in group['tables']:
                        hql2table[hql].append(table['name'])

                #schedule_interval 调度时间间隔 5/30/60 分钟|0 7_1 &_2 ~7_7 天|30_1 30-2 月
                if group.has_key('schedule_interval') and group['schedule_interval']:
                    interval2group[hql]=group['schedule_interval']

                if group.has_key('run_times') and group['run_times']:
                    times2task[hql]=group['run_times']
                if group.has_key('schedule_interval_offset') and group['schedule_interval_offset']:
                    re_offset=re.compile(r'^([-]?\d+)(day|minute|hour)')
                    offset_res=re_offset.findall(group['schedule_interval_offset'])
                    if len(offset_res)>0:
                        offset_val=int(offset_res[0][0])
                        offset_type=str(offset_res[0][1].encode('utf-8')).strip()
                        offset=0
                        if 'day'==offset_type:
                            offset=int(offset_val*86400)
                        elif 'hour'==offset_type:
                            offset=int(offset_val*3600)
                        elif 'minute'==offset_type:
                            offset=int(offset_val*60)
                        offset2group[hql]=offset
                    else:
                        offset2group[hql]=None
                else:
                    offset2group[hql]=None

        if modules:
            run_modules=modules
        else:
            temp = appConf.get_run_list()
            for tmp_run in temp['run_instance']['group']:
                if special_table is None or special_table in hql2table[tmp_run['name']]:
                    run_modules.append(tmp_run['name'])
        for run_name in run_modules:
            status=False
            ret=()
            one_time_tip=False
            groupoffset=offset2group[run_name]
            if interval2group.has_key(run_name) and interval2group[run_name]:
                interval=interval2group[run_name]
                #interval为0_0,0_1为只执行一次
                one_time_interval=['0_0','0_1']
                if interval in one_time_interval:
                    one_time_tip=True
                    interval='0'
                status,ret=checkScheduleInterval(interval,stat_date,groupoffset,special_table)
            else:
                status,ret=checkScheduleInterval('0',stat_date,groupoffset,special_table)
            if status:
                #如果只执行一次的任务，删除
                if one_time_tip==True:
                    appConf.off_run_task(run_name)
                schedule_level,stat_date_ret=ret
                if 'minute'==schedule_level or 'hour'==schedule_level:
                    import time
                    stat_time=time.mktime(time.strptime(stat_date_ret,"%Y-%m-%d %H:%M"))
                    stat_hour=int(time.strftime('%H',time.localtime(stat_time)))
                    stat_minute=int(time.strftime('%M',time.localtime(stat_time)))
                    #00:00时运行分钟天数据(前一天)
                    if int(conf.CRONTAB_TASK_HOUR)==stat_hour and 0==stat_minute and int(hql_type)!=2:
                        stat_day_minute=time.strftime('%Y-%m-%d',time.localtime(int(stat_time-86400)))
                        cg_list.append((run_name,'day',stat_day_minute))
                    #分钟任务
                    if times2task.has_key(run_name) and times2task[run_name] and int(times2task[run_name])!=0:
                        import time
                        times=int(times2task[run_name])+1
                        for i in range(0,times):
                            tmp_stat_date=time.strftime('%Y-%m-%d %H:%M',time.localtime(stat_date_time-i*3600))
                            cg_list.append((run_name,schedule_level,tmp_stat_date))
                    else:
                        cg_list.append((run_name,schedule_level,stat_date_ret))
                elif 'day'==schedule_level:

                    if times2task.has_key(run_name) and times2task[run_name] and int(times2task[run_name])!=0:
                        import time
                        times=int(times2task[run_name])+1
                        stat_date_time=int(time.mktime(time.strptime(stat_date_ret,'%Y-%m-%d')))
                        for i in range(0,times):
                            tmp_stat_date=time.strftime('%Y-%m-%d',time.localtime(stat_date_time-i*86400))
                            cg_list.append((run_name,'day',tmp_stat_date))
                    else:
                        cg_list.append((run_name,'day',stat_date_ret))

    except:
        import traceback
        traceback.print_exc()

    return cg_list





if __name__=='__main__':
    pass
