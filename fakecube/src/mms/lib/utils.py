#!/usr/bin/env python2.7
# coding=utf-8
import ConfigParser
from decimal import Decimal
import hashlib
from email.mime.text import MIMEText
import re
import time
import sys
import yaml

import env as conf
import mmsEnv as mmsEnv
import app_conf as appObj
from mms_mysql import MmsMysql
import mms_mysql_conf as mmsMysqlConf
from mms_email import MmsEmail
import OtherDatasourceConfig as dsconfig
from get_mms_mysql_conf import get_mms_mysql_conf
from log4me import MyLogger
logger = MyLogger.getLogger()

#5:SUCCESS
#6:FAILED
#7:WARNING


def err_json(errmsg,errcode=conf.FAILED,logger=None):
    if logger is not None:
          logger.exception(errmsg)

    return {'ret_code':errcode,'msg':errmsg,'num':0}


def data_json(msg,ret_code=conf.SUCCESS):
    return {'ret_code':ret_code,'msg':msg}

def warn_json(msg,warn_code=conf.WARNING):
    return {'ret_code':warn_code,'msg':msg}

def rm_html(content):
    import re
    r=re.compile(r'<[^>]+>',re.S)
    content=r.sub('',content)
    return content.strip()

#下线任务
def off_run_task(run_file_path,off_group):
    try:
        f=open(run_file_path)
        run_obj=yaml.load(f)
        run_instance=run_obj['run_instance']
        run_groups=run_instance['group']
        tmp_run_groups=[]
        for g in run_groups:
            if g['name']!=off_group:
                tmp_run_groups.append({'name':g['name']})

        run_content = {'run_instance': {'conf': '', 'group': []}}
        run_content['run_instance']['conf'] = run_instance['conf']
        run_content['run_instance']['group'] = tmp_run_groups

        file_object = open(run_file_path, 'w')
        file_object.write(yaml.safe_dump(run_content, default_flow_style=False, encoding=('utf-8'), allow_unicode=True))

    except:
        pass


#获取项目下所有指标信息
def get_project_metric(project_name,metric=None):
    metric_inf_dict={}
    appConf=appObj.AppConf(project_name)
    temp=appConf.appConf
    for category in temp['project'][0]['categories']:
        category["name"]=category["name"].lower()
        for group in category['groups']:
            group['name']=group['name'].lower()
            cg_name='.'.join((category['name'],group['name']))
            for e in group['metrics']:
                tmp_metric_name='.'.join((project_name,cg_name,e['name']))
                metric_inf_dict[tmp_metric_name]=e

    return metric_inf_dict

#获取项目下所有hql配置信息
def get_project_group_inf(project_name,group=None):
    group_inf_dict={}
    if not project_name:
        return group_inf_dict
    appConf=appObj.AppConf(project_name)
    if not appConf.appExist:
        return group_inf_dict
    temp = appConf.appConf
    for category in temp['project'][0]['categories']:
        category["name"]=category["name"].lower()
        for group in category['groups']:
            group['name']=group['name'].lower()
            cg_name='.'.join((category['name'],group['name']))
            tmp_group_inf={}
            if group.has_key('schedule_interval') and group['schedule_interval']:
                tmp_group_inf['schedule_interval']=group['schedule_interval']
            if group.has_key('schedule_interval_offset') and group['schedule_interval_offset']:
                tmp_group_inf['schedule_interval_offset']=group['schedule_interval_offset']
            if group.has_key('custom_cdate') and group['custom_cdate']:
                tmp_group_inf['custom_cdate']=group['custom_cdate']
            group_inf_dict['%s.%s'%(project_name,cg_name)]=tmp_group_inf
    return group_inf_dict

def mms_md5(str):
    if not str:
        return ''
    m=hashlib.md5()
    m_str=m.update(str)
    return m.hexdigest()

def getMysqlConfigByAppName(db_name, type, weight='1'):
    try:
        cf=ConfigParser.ConfigParser()
        cf.read(mmsEnv.STORE_DB_PATH)
        if cf.has_section(db_name):
            key = type
            if type == 'slave':
                key = type+str(weight)
            db_name = cf.get(db_name, key)
    except:
        raise KeyError, 'no db name in store.conf file'

    if hasattr(mmsMysqlConf, db_name):
        db_config = getattr(mmsMysqlConf, db_name)
    else:
        raise KeyError, 'no db config in mms_mysql_conf'

    return db_config

def getDBConfigByGroups(groups):
    db_map=dsconfig.TABLE_DB_MAP
    db=[]
    config=None
    groups=list(set(groups))
    for e in groups:
        if db_map.has_key(e):
            db.append(db_map[e])
    db=list(set(db))
    #暂时不支持垮库
    if len(db)>1:
        raise Exception,'不支持垮库查询'
    if len(db)==1:
        db_config_dict=dsconfig.DB_CONFIG
        if not db_config_dict.has_key(db[0]):
            raise Exception, '请配置{}数据源'.format(db[0])
        return db_config_dict[db[0]]
    return config

def getDBConfigByName(db):
    #通过dbname获取数据库配置文件
    config=get_mms_mysql_conf(db)
    return config
def getScheduleNameByIp(ip):
    name=''
    ip=ip.strip()
    if ip:
        try:
            cf=ConfigParser.ConfigParser()
            cf.read(mmsEnv.SCHEDULE_CONF_PATH)
            secs=cf.sections()
            for sec in secs:
                if cf.has_option(sec,'ip'):
                    if cf.get(sec,'ip')==ip:
                        return sec
        except:
            return name
    return name

def getScheduleConfByIp(ip,op):
    opv=''
    if ip:
        try:
            cf=ConfigParser.ConfigParser()
            cf.read(mmsEnv.SCHEDULE_CONF_PATH)
            secs=cf.sections()
            for sec in secs:
                if cf.has_option(sec,'ip'):
                    if cf.get(sec,'ip')==ip:
                        if cf.has_option(sec,op):
                            return cf.get(sec,op)
                        else:
                            return cf.get('common',op)
        except:
            return opv
    return opv

def getScheduleConfByName(name,op):
    opv=''
    name=name.strip()
    op=op.strip()
    if name:
        try:
            cf=ConfigParser.ConfigParser()
            cf.read(mmsEnv.SCHEDULE_CONF_PATH)
            if cf.has_option(name,op):
                return cf.get(name,op)
            else:
                return cf.get('common',op)
        except:
            return opv
    return opv

def getAllScheduleConf():
    cf=ConfigParser.ConfigParser()
    cf.read(mmsEnv.SCHEDULE_CONF_PATH)
    secs=cf.sections()
    return secs


def update_task_status(id,status,conf_name=''):
    try:
        mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()
        sql='update %s set status=%s,end_time=now() where id=%s'%(conf.QUEUE_TABLE,status,id)
        if conf_name:
            sql='update %s set status=%s,end_time=now(),conf_name="%s" where id=%s'%(conf.QUEUE_TABLE,status,conf_name,id)

        cur.execute(sql)
        conn.commit()
        conn.close()
        return True
    except Exception,ex:
        import traceback
        traceback.print_exc()
        return False

def sendMail(result,task_creater,user_name, type=0):
    type2dict={0:'杀死',1:'置为就绪'}
    email_to=['bi-service-mail']
    if task_creater is not None and task_creater !='manual' and task_creater != user_name:
        email_to.append(str(task_creater))
    email_to.append(str(user_name))
    email_obj = MmsEmail()
    content = '''
        项目%s下任务%s（任务ID为： %s，统计时间：%s）被%s手动%s。
    '''%(str(result['app_name'].encode('utf-8')),str(result['run_module'].encode('utf-8')),str(result['id']),str(result['stat_date'].encode('utf-8')),str(user_name), str(type2dict[type]))
    content=MIMEText(content,"html","utf-8")
    title='【监控】data平台手动%s任务通知' % type2dict[type]
    email_obj.sendmessage(email_to, title, content)

def getRunTaskList(project, run_module, start_time, end_time, step, creater):
    #查询该用户非结束任务数
    appConf=appObj.AppConf(project)

    yaml_content = appConf.appConf
    module_group=dict()
    if not isinstance(run_module, list):
        run_module_list=run_module.split(',')
    else:
        run_module_list = run_module

    hadoop_queue='inf'
    manual='manual'
    if step is None or step=="":
        step = 'all'

    for module in run_module_list:
        category_group=module.split('.')
        category=category_group[0]
        group=category_group[1]

        for categories_index in range(0,len(yaml_content['project'][0]['categories'])):
            categories=yaml_content['project'][0]['categories'][categories_index]

            if str(category).strip().lower()==str(categories['name']).strip().lower():
                for i  in range(0,len(categories['groups'])):
                    groups_content=categories['groups'][i]
                    if str(group).strip().lower()==str(groups_content['name']).strip().lower():
                        schedule_inf=('','day','day')
                        if groups_content.has_key('hql_type') and groups_content['hql_type']:
                            if int(groups_content['hql_type'])==2:
                                manual='system'
                        if not groups_content.has_key('schedule_interval'):
                            #属于天级别
                            schedule_inf=('','day','day')
                        if groups_content.has_key('schedule_interval') and groups_content['schedule_interval']:
                            r = re.compile(r'^(\d+)(_(\d+)+)?$')
                            res=r.findall(str(groups_content['schedule_interval']))
                            if res:
                                i=res[0]
                                if i[2] and i[0]:
                                    #天
                                    if 30==int(i[0]):
                                        schedule_inf=(str(i[2]),'month','day')
                                    elif 7==int(i[0]):
                                        schedule_inf=(str(i[2]),'week','day')
                                elif i[0]:
                                    if 0==int(i[0]):
                                        schedule_inf=('','day','day')
                                    else:
                                        #分钟
                                        schedule_inf=(str(i[0]),'minute','minute')
                            else:
                                #天
                                schedule_inf=('','day','day')


                        module_group[module]=schedule_inf

    template=[]

    start_seconds=int(time.mktime(time.strptime(str(start_time),"%Y-%m-%d %H:%M")))
    end_seconds=int(time.mktime(time.strptime(str(end_time),"%Y-%m-%d %H:%M")))


    for key,val in module_group.items():
        schedule_interval,schedule_type,schedule_level=val
        if 'minute'==schedule_level:
            for now in range(end_seconds,start_seconds-60,-60):
                minute=int(time.strftime('%M',time.localtime(now)))
                stat_date=time.strftime('%Y-%m-%d %H:%M',time.localtime(now))
                mod=minute%int(schedule_interval)
                if 0==int(mod):
                    #增加分钟任务
                    if 60==int(schedule_interval):
                        template.append((project,stat_date,'1',key,step,'0','0','hour',manual,creater,hadoop_queue,''))
                    else:
                        template.append((project,stat_date,'1',key,step,'0','0','minute',manual,creater,hadoop_queue,''))
        elif 'day'==schedule_level:
            start_seconds=int(time.mktime(time.strptime(time.strftime('%Y-%m-%d',time.localtime(start_seconds)),'%Y-%m-%d')))
            end_seconds=int(time.mktime(time.strptime(time.strftime('%Y-%m-%d',time.localtime(end_seconds)),'%Y-%m-%d')))
            for now in range(end_seconds,start_seconds-86400,-86400):
                stat_date=str(time.strftime('%Y-%m-%d',time.localtime(now)))

                template.append((project,stat_date,'1',key,step,'0','0', schedule_level,manual,creater,hadoop_queue,''))
    return template


def checkPersonalIsCanRunHql(creater, run_count):
    mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META)
    select_sql = "select * from %s where submitter='%s' and creater is not null and status not in (5,6,7,8,11) " % (
    conf.QUEUE_TABLE, creater)
    select_cur = mmsMysql.get_cur()
    select_cur.execute(select_sql)
    select_res = select_cur.fetchall()
    select_task_num = len(select_res)

    total_tasks_num = select_task_num + run_count
    if total_tasks_num > int(conf.MANUAL_RUN_TASK_NUM) and creater not in conf.MANUAL_USER_WHITE_LIST:
        if total_tasks_num > conf.MANUAL_SPECIAL_RUN_TASK_NUM and creater in conf.MANUAL_USER_SPECIAL_LIST:
            return '普通用户提交非结束任务个数不能超过%s个' % (conf.MANUAL_SPECIAL_RUN_TASK_NUM)
        elif creater not in conf.MANUAL_USER_SPECIAL_LIST:
            return '普通用户提交非结束任务个数不能超过%s个' % (conf.MANUAL_RUN_TASK_NUM)
    return True

def insertTask(template):
    #app_name,stat_date,status,run_module,step,is_test,priority,schedule_level,submitter,task_queue
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur(mmsMysql.DICTCURSOR_MODE)
    #禁止重新插入
    invald = []
    sql = "select status from mms_run_log where app_name='%s' and stat_date='%s' and run_module='%s'"
    for t in template:
        app_name = t[0]
        stat_date = t[1]
        run_module = t[3]
        cur.execute(sql % (app_name, stat_date, run_module))
        ret=cur.fetchone()
        if ret is not None and ret.has_key('status'):
            result = int(ret['status'])
            if result == conf.READY or result == conf.WAITING:
                invald.append(t)

    if len(invald)!=0:
        for i in invald:
            template.remove(i)

    sql="insert into %s (app_name,stat_date,status,run_module,step,is_test,priority,schedule_level,creater,submitter,task_queue,second_check) " %conf.QUEUE_TABLE
    sql+="values (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)"

    cur.executemany(sql,template)
    conn.commit()
    conn.close()

def defaultencode(obj):
    if isinstance(obj, Decimal):
        # Subclass float with custom repr?
        return fakefloat(obj)

    import calendar, datetime,time

    if isinstance(obj, datetime.datetime):
        if obj.utcoffset() is not None:
            obj = obj - obj.utcoffset()
        millis = int(
            calendar.timegm(obj.timetuple())

        )
        return time.strftime("%Y-%m-%d",time.localtime(millis))
    raise TypeError(repr(obj) + " is not JSON serializable")
class fakefloat(float):
    def __init__(self, value):
        self._value = value
    def __repr__(self):
        return str(self._value)

def checkScheduleInterval(schedule_interval='',stat_date=None,groupoffset=None,special_table=None):
    #5/30/60 分钟|0 7_1 &_2 ~7_7 天|30_1 30-2 月
    crontab_hour=conf.CRONTAB_TASK_HOUR
    crontab_minute = conf.CRONTAB_TASK_MINUTE
    # stat_date='2015-06-01 05:00'
    # now=time.mktime(time.strptime(stat_date,"%Y-%m-%d %H:%M"))
    now=time.time()
    now_struct=time.localtime(now)
    now_minute=int(time.strftime('%M',now_struct))
    now_hour=int(time.strftime('%H',now_struct))
    now_day=int(time.strftime('%d',now_struct))
    now_weekday=int(time.strftime('%w',now_struct))
    stat_date_time=int(time.mktime(time.strptime(stat_date,'%Y-%m-%d')))
    if now_minute>0 and now_minute<=1:
        now_minute=0
    #if groupoffset:
    stat_date_time=stat_date_time+groupoffset
    #else:
    #    stat_date_time=stat_date_time-86400
    stat_date=time.strftime('%Y-%m-%d',time.localtime(stat_date_time))
    import re
    r = re.compile(r'^(\d+)(_(\d+)+)?$')
    res=r.findall(str(schedule_interval))
    if res:
        i=res[0]
        if i[2] and i[0]:
            if 30==int(i[0]):
                #月
                if int(i[2])==now_day:
                    if now_hour==int(crontab_hour) and now_minute<=int(crontab_minute):
                        return True,('day',stat_date)
            elif 7==int(i[0]):
                #星期
                if 0==now_weekday:
                    now_weekday=7
                if int(i[2])==now_weekday:
                    if now_hour==int(crontab_hour) and now_minute<=int(crontab_minute):
                        return True,('day',stat_date)

            elif 1==int(i[0]):
                #每天几点
                hour = int(i[2])
                if (isinstance(special_table, list) and len(special_table)!=0) or (now_hour==int(hour) and now_minute<=int(crontab_minute)):
                    #每天
                    return True,("day",stat_date)
                return False,(None,None)

        elif i[0]:

            if 0==int(i[0]):
                if (isinstance(special_table, list) and len(special_table)!=0) or (now_hour==int(crontab_hour) and now_minute<=int(crontab_minute)):
                    #每天
                    return True,("day",stat_date)
                return False,(None,None)

            #分钟
            mod=now_minute%int(i[0])
            if 0==mod:

                pre_now=now-int(i[0])*60
                if groupoffset:
                    pre_now=now+groupoffset
                stat_date=time.strftime('%Y-%m-%d %H:%M',time.localtime(pre_now))
                if 60==int(i[0]):
                    return True,('hour',stat_date)
                return True,('minute',stat_date)

    return False,(None,None)


def checkMysqlSynchDelay(db_name,weight=1,delay_second=0):
    '''
    延迟返回True 正常Flase
    db_name:查询延迟数据库
    weight:数据库weight值
    delay_second:延迟秒
    '''
    try:
        slave_config = getMysqlConfigByAppName(db_name, 'slave',weight=weight)
        data_slave=MmsMysql(slave_config)
        data_slave_conn=data_slave.get_conn()
        data_slave_cur=data_slave.get_cur()
        sql='show slave status'
        data_slave_cur.execute(sql)
        columns = data_slave_cur.description
        slave_res = data_slave_cur.fetchone()
        data_slave_conn.close()
        if not slave_res:
            return False
        second_behind=0
        for (index,column) in enumerate(slave_res):
            if columns[index][0]=='Seconds_Behind_Master':
                second_behind=column
                break
        if int(second_behind)==0:
            return False
        elif int(second_behind)>=int(delay_second):
            logger.warn('check database {} master-slave synch delay {} s'.format(db_name,str(second_behind)))
            return True
    except:
        logger.exception('check database {} master-slave exception'.format(db_name))
        return False


if __name__=='__main__':
    print getScheduleConfByIp('10.6.3.103','max_run_num')
    print getAllScheduleConf()