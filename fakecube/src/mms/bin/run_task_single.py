#!/usr/bin/env python2.7
# coding=utf-8
import argparse
import os
import sys
import re
import socket
import time
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import env as conf
from lock import SingletonLock
from log4me import MyLogger
from fun_replace import FuncReplace
# from test_hive_table import testTable
import logging
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
from mms_conf import MmsConf
from email.mime.text import MIMEText
from mms_sms import MmsSMS
from utils import rm_html
from query_tools_run import query_run
from mms_table_tag import generate_mms_table_tag
import app_conf as appObj
from ResourceTool import ResourceTool
from utils import getScheduleNameByIp
from utils import getScheduleConfByName
from mysql.LocalToMysqlStoreType1 import LocalToMysqlStoreType1
from mysql.LocalToMysqlStoreType2 import LocalToMysqlStoreType2
from mysql.LocalToMysqlStoreType3 import LocalToMysqlStoreType3
from phoenix.HdfsToPhoenix import HdfsToPhoenix

def cmdArgsDef():
    arg_parser = argparse.ArgumentParser()
    arg_parser.add_argument('-l', '--log', help='[help]run queue id', required=True)
    arg_parser.add_argument('-c', '--check', action='store_true', help='[help]run queue id', required=False)


    args = arg_parser.parse_args()

    return args


def get_run_info(queue_id):
    try:
        mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()
        sql = "select app_name,stat_date,status,run_module,step,is_test,schedule_level,priority,creater,ready_time,job_type,params,submitter,task_queue,second_check from %s where id = %s" % (
        conf.QUEUE_TABLE, queue_id)
        cur.execute(sql)

        columns = cur.description

        result = cur.fetchone()

        tmp = {}

        if result:
            for (index, column) in enumerate(result):
                tmp[columns[index][0]] = column
        sql="select count(1) from %s where app_name='%s'and stat_date='%s' and run_module='%s' and status=3 and conf_name='%s'" %(conf.QUEUE_TABLE,tmp['app_name'],tmp['stat_date'],tmp['run_module'],conf_name)
        cur.execute(sql)
        result = cur.fetchone()[0]
        cur.close()
        conn.close()
        return tmp,result
    except:
        logger.exception("get run info error:%s" % queue_id)

    sys.exit(-1)


def get_hql(app_name, module_name, stat_date,stat_hour='0',stat_minute='0'):
    hql = None
    group = None
    cat = None
    set_params=''
    userWhiteList=['pengbangzhong','liangbo','data_alarm']
    appConf=appObj.AppConf(app_name)

    if 'minute'==schedule_level:
        pass
    try:

        config_json =appConf.appConf
        for category in config_json['project'][0]['categories']:
            for gp in category['groups']:
                cg_name = '.'.join((category['name'], gp['name']))

                if module_name == cg_name:
                    cat = category['name']
                    group = gp
                    code_file = '_'.join((category['name'], gp['name']))
                    code_file=code_file.lower()
                    if app_name in white_project_list:
                        stat_date=white_stat_date
                    params = {'metric_group_json': group, 'dt': stat_date,'hour':stat_hour,'minute':stat_minute}

                    hql_attach_content=''
                    if group.has_key('attach') and group['attach']:
                        hql_attach_content=group['attach']
                        hql_attach_content=hql_attach_content+';'

                    hql=appConf.get_hql(category['name'],gp['name'])
                    hql = hql[code_file].strip()
                    hql = replace(hql, params,schedule_level)

                    task_name=''' --%s
                    '''%(submitter)
                    set_params=task_name
                    set_params=hql_attach_content+set_params

                    return (hql, cat, group),set_params
        logger.error('module_name :%s not found in yaml file' % module_name)
    except:
        logger.exception('get_hql failed')

    error_exit(conf.FAILED, 'get hql error')


def error_exit(err_code, err_msg):
    update_queue_status(err_code)

    if err_code == conf.FAILED:
        ip_href=conf.ip_href
        import socket
        local_ip=str(socket.gethostbyname(socket.gethostname()))
        html_a=''
        if ip_href.has_key(local_ip):
            host=ip_href[local_ip]
            if not host:
                sys.exit(-1)
            href='%sget_run_detail?serial=%s&app_name=%s&stat_date=%s&module_name=%s'
            href=href%(host,queue_id,app_name,stat_date,module_name)
            html_a="<a href='%s'>查看日志</a>"%(href)

            from mms_email import MmsEmail
            email_to=['data_alarm@']
            mms_obj=MmsConf()
            res=mms_obj.select(app_name)
            mms_obj.connRead.close()
            mms_obj.connWrite.close()
            #高优先级项目失败短信报警
            # if int(priority) in [8,9,10]:
            alarm_content = '项目：%s 下%s任务运行失败，请及时处理。－Dt平台' % (app_name, module_name)
            mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
            conn = mmsMysql.get_conn()
            cur = mmsMysql.get_cur()
            sql = "select iphone from t_visual_user where user_name = '%s'" % (res[0]['creater'])
            cur.execute(sql)
            phoneNum = cur.fetchone()[0]
            cur.close()
            conn.close()
            mmsSMs = MmsSMS()
            mmsSMs.sendSMS([str(phoneNum)], alarm_content)
            logger.info('sms send')


            email_to.append(str(res[0]['creater']))
            email_obj = MmsEmail()
            content = 'app_name:%s\nmodule:%s </br> stat_date:%s </br> err_msg:%s </br>' % (app_name, module_name, stat_date, err_msg)
            content='%s %s'%(content,html_a)
            content=MIMEText(content,"html","utf-8")
            err_title='【监控】fakecube失败任务'
            retu = email_obj.sendmessage(email_to, err_title, content)
            logger.info('email send %s' % retu)

    logger.info('error exit, err_code:%s err_msg:%s '%(str(err_code),str(err_msg)))
    sys.exit(-1)


def replace(hql, params,schedule_level=None):
    import re

    #特别处理小时天统计
    if schedule_level=='day':
        reg_udf='HOUR'
        r=re.compile(r'([=><]+\s*[\'\"]\s*\$(%s)\(([-a-zA-Z0-9,_ ]+)\)\s*[\'\"])' % reg_udf, re.DOTALL)
        result=r.findall(hql)
        for content in result:
            replace_str= ' is not null '
            hql=hql.replace(content[0],replace_str)

    udf = ['DATE|TEST|HOUR|MONTH']
    reg_udf = '|'.join(udf)
    r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
    result = r.findall(hql)
    obj = FuncReplace()
    for content in result:
        b = getattr(obj, content[1])
        replace_str = b(params, content[2])
        hql = hql.replace(content[0], replace_str)
    return hql


def get_running_cnt():
    try:
        mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()
        cur.execute("select count(1) from %s where status = %s and conf_name='%s'" % (conf.QUEUE_TABLE, conf.RUNNING,conf_name))
        running_cnt = cur.fetchone()[0]
        conn.close()
        return running_cnt
    except:
        logger.exception("get running cnt exception")
        sys.exit(-1)


def update_queue_status(status):
    try:
        mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()

        tmp_sql = "update %s set status = %s"
        if status == conf.RUNNING:
            tmp_sql += ',start_time = now()'
        elif status in (conf.SUCCESS, conf.FAILED, conf.WARNING):
            tmp_sql += ',end_time = now()'
        elif status ==conf.READY:
            tmp_sql += ' ,ready_time= now()'
        tmp_sql += ' where id = %s'

        sql = tmp_sql % (conf.QUEUE_TABLE, status, queue_id)
        #防止手动杀死任务，状态再改变
        if status in (conf.READY,conf.WAITING):
            sql+=' and status!='+str(conf.KILLED)

        cur.execute(sql)
        conn.commit()
        conn.close()
    except:
        logger.exception("get running cnt exception")
        sys.exit(-1)


def get_table_time_depend_list(time_depend,stat_date,hour,minute):
    time_depend_list=[]
    udf = ['DATE|HOUR|MONTH']
    reg_udf = '|'.join(udf)
    r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
    result = r.findall(time_depend)
    if len(result)==2:
        time_depend_type=result[0][1]
        start=int(result[0][2])
        end=int(result[1][2])+1
        if time_depend_type=='DATE':
            dt_stamp=time.mktime(time.strptime(stat_date,"%Y-%m-%d"))
            for i in range(start,end):
                i_stat_date=time.strftime('%Y-%m-%d',time.localtime(dt_stamp+(float)(i)*86400))
                time_depend_list.append({'stat_date':i_stat_date,'check_level':'day','hour':'','minute':''})
        elif time_depend_type=='HOUR':
            join_date='%s %s:%s'%(stat_date,str(hour),str(minute))
            dt_stamp=time.mktime(time.strptime(join_date,"%Y-%m-%d %H:%M"))
            for i in range(start,end):
                tmp_dt_stamp=time.localtime(dt_stamp+(float)(i)*3600)
                i_stat_date=time.strftime('%Y-%m-%d',tmp_dt_stamp)
                i_h=time.strftime('%H',tmp_dt_stamp)
                time_depend_list.append({'stat_date':i_stat_date,'check_level':'hour','hour':i_h,'minute':minute})

    return time_depend_list

def test_table_ready(tables,stat_date,check_level=None,hour='',minute='',second_check=''):
    from test_hive_table import testTable
    flag = True

    for t in tables:
        if t.has_key('ischecktables') and int(t['ischecktables'])==-1:
            continue
        table_name=replace(t['name'],{'dt':stat_date})
        table_name=rm_html(table_name)

        time_depend_list=[]
        not_ready_time=''
        #表依赖时间段如果有time_depend 字段格式为$DATE(0)/$DATE(0)，$HOUR(0)/$HOUR(0)
        if t.has_key('time_depend') and t['time_depend']:
            time_depend=rm_html(t['time_depend'])
            time_depend_list=get_table_time_depend_list(time_depend,stat_date,hour,minute)
        else:
            time_depend_list.append({'stat_date':stat_date,'check_level':check_level,'hour':hour,'minute':minute})
            #如果是回跑任务校验任务最新分区
            if second_check:
                time_depend_list.append({'stat_date': second_check, 'check_level': check_level, 'hour': hour, 'minute': minute})

        if t.has_key('par') and t['par'] !='':
            has_dt = rm_html(t['par'])

            for td in time_depend_list:
                flag = testTable(table_name,has_dt,td['stat_date'],None,td['check_level'],td['hour'],td['minute'])
                not_ready_time=td['stat_date']
                if td['hour']:
                    not_ready_time="%s %s:00"%(str(not_ready_time),str(td['hour']))
                if flag==False:
                    break
            # flag = testTable(table_name,has_dt,stat_date,None,check_level,hour,minute)
        else:
            # flag=testTable(table_name,None,None,stat_date)
            for td in time_depend_list:
                # flag=testTable(table_name,None,None,stat_date,check_level,hour,minute)
                flag = testTable(table_name,None,None,td['stat_date'],td['check_level'],td['hour'],td['minute'])
                not_ready_time=td['stat_date']
                if td['hour']:
                    not_ready_time="%s %s:00"%(str(not_ready_time),str(td['hour']))

                if flag==False:
                    break

        if flag == False:
            logger.error("table :%s date: %s not ready" %(t['name'],str(not_ready_time)))
            update_queue_status(conf.WAITING)
            sys.exit(-1)
        logger.info("table :%s date: %s are ready" %(t['name'],str(not_ready_time)))
    update_queue_status(conf.READY)
    logger.info("all table are ready")

def run_hql(hql, cat, group,status=conf.WAITING):
    try:
        object_mms_conf = MmsConf()
        res = object_mms_conf.select(app_name)
        object_mms_conf.close_connection()
        store_type = res[0]['storetype']
        objname=type2db[store_type]
        objclass = globals()[objname]
        #objclass=getattr(objmodule,objname)
        db=objclass(stat_date, app_name, cat, group, store_type,hql,log_file,schedule_level=schedule_level,stat_hour=stat_hour,stat_minute=stat_minute,exec_type=step)
        if step.strip().lower() == 'all' or step.strip().lower() == 'hive':
            #close long connetion before run hive
            logger.info("close the mysql long connection before execute hive sql")
            db.close_connection()

            running_cnt = get_running_cnt()

            cur_hour=int(time.strftime("%H",time.localtime(time.time())))
            sys_multi_run_limit=conf_max_run_num#conf.MULTI_RUN_LIMIT
            runPriority = res[0]['priority']
            if cur_hour >= 1 and cur_hour <= 7:
                resource = ResourceTool()
                isOverload = resource.check_used_vcore_threshold()
                if isOverload:
                    if runPriority != 10:
                        logger.info("the cluster has been overloaded")
                        sys.exit(-1)
            if cur_hour>=4 and cur_hour<=9:
                sys_multi_run_limit=70
            print running_cnt,sys_multi_run_limit
            sys_multi_run_limit=60
            if running_cnt < sys_multi_run_limit:


                hql=db.getHql()
                hql=hql_params+hql
                suffix = '.'.join((stat_date, app_name, module_name))
                if 'minute'==schedule_level or 'hour'==schedule_level:
                    suffix='.'.join((stat_date,stat_hour,stat_minute, app_name, module_name))

                out_f = conf.OUTPUT_PATH + suffix

                start = time.time()

                logger.info("hql:%s" % hql)

                tmp_hql_file = '%s/%s_%s.sql' %(conf.TMP_SQL_PATH,stat_date,module_name)

                if 'minute'==schedule_level or 'hour'==schedule_level:
                    tmp_hql_file = '%s/%s_%s_%s_%s.sql' %(conf.TMP_SQL_PATH,stat_date,stat_hour,stat_minute,module_name)
                if runPriority == 0:
                    hql = 'set mapreduce.job.queuename=di;' + str(hql)
                with open(tmp_hql_file,'w') as f:
                    f.write(str(hql))
                #hive11_list=['shop_backend_automatic_statistics']
                hive11_list=[]
                if app_name in hive11_list:
                    cmd = "hive -f %s >%s 2>>%s" % (tmp_hql_file, out_f, log_file)
                else:
                    cmd = "hive -f %s >%s 2>>%s" % (tmp_hql_file, out_f, log_file)

                logger.info("cmd:%s" % cmd)
                update_queue_status(conf.RUNNING)
                # 调度类项目打tag
                if group.has_key('hql_type') and group['hql_type'] and int(group['hql_type']) == 2:
                    generate_mms_table_tag(hql, group, stat_date, stat_hour, schedule_level, logger,enable=0)
                index=1
                retu=1
                while index<=int(conf.TASK_FAIL_NUM):
                    retu=os.system(cmd)
                    if retu==0:
                        break
                    else:
                        #如果任务状态为11不在重试
                        tmp_queue_info,tmp_flag=get_run_info(queue_id)
                        if int(tmp_queue_info['status'])==int(conf.KILLED):
                            sys.exit(-1)
                        index=index+1
                        if task_creater or int(index)>3:
                            break
                        mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META)
                        conn=mmsMysql.get_conn()
                        cur=mmsMysql.get_cur()
                        logger.info("项目：%s下任务：%s 第 %s 次执行"%(app_name,module_name,str(index)))
                        update_sql='update %s set repeat_num=%s where id=%s'%(conf.QUEUE_TABLE,index,queue_id)
                        cur.execute(update_sql)
                        conn.commit()
                        conn.close()
                        time.sleep(300)


                end=time.time()
                if retu == 0:
                    if step.strip().lower() == 'hive':
                        update_queue_status(conf.SUCCESS)
                    else:
                        update_queue_status(conf.HIVEEND)
                    if os.stat(out_f).st_size == 0:
                        warn_msg="hive result file is empty!"
                        logger.warn(warn_msg)
                        #error_exit(conf.WARNING,warn_msg)
                    #调度类项目打tag
                    if group.has_key('hql_type') and group['hql_type'] and int(group['hql_type'])==2:
                        generate_mms_table_tag(hql,group,stat_date,stat_hour,schedule_level,logger)

                    logger.info('hql execute success and use %ss' %str(end - start))

                if retu !=0:
                   update_queue_status(conf.HIVEEND)
                   err_msg = "hive execute hql error!"
                   logger.error(err_msg)
                   error_exit(conf.FAILED,err_msg)

            else:
                logger.info('running cnt :%s arrive limit:%s pls wait' % (running_cnt, str(sys_multi_run_limit)))
                sys.exit(-1)

        if step.strip().lower() == 'all' or step.strip().lower() == 'mysql' or step.strip().lower() == 'delete':
            if step.strip().lower() == 'mysql' or step.strip().lower() == 'delete':
                update_queue_status(conf.RUNNING)

            try:
                ret = db.execute(queue_id)
            except Exception, ex:
                import traceback
                traceback.print_exc()
                err_msg = "db execute error: %s" % ex.message
                error_exit(conf.FAILED, err_msg)

            ret_code = ret['ret_code']
            ret_msg = ret['msg']
            if ret_code == conf.SUCCESS:
                update_queue_status(conf.SUCCESS)
            else:
                error_exit(ret_code, ret_msg)

    except SystemExit:
        pass

    except:
        err_msg = 'run_hql exeption'
        logger.exception(err_msg)
        error_exit(conf.FAILED, err_msg)


def get_sys_running_tasks():
    mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    status_sql='select * from %s where creater is null and status=%s'%(conf.QUEUE_TABLE,conf.RUNNING)
    cur.execute(status_sql)
    res=cur.fetchall()
    conn.close()
    return res

def check_memory():
    p_inf=[]
    try:
        p=os.popen('free -m')
        lines=p.readlines()
        p_inf=lines[2].split()[1:4]
    except:
        logger.exception('get os memory inf error ')

    if p_inf and int(p_inf[2])>int(conf.MIN_FREE_MEMORY):
        return True
    logger.info('memory limit:%sM ' % (conf.MIN_FREE_MEMORY))
    logger.info('memory inf total:%s used:%s free %s '%(str(p_inf[0]),str(p_inf[1]),str(p_inf[2])))
    return False

if __name__ == '__main__':
    type2db={1:'LocalToMysqlStoreType1',2:'LocalToMysqlStoreType2',3:'HdfsToPhoenix',4:'LocalToMysqlStoreType2',5:'LocalToMysqlStoreType3'}
    logger = MyLogger.getLogger()
    cmd_args = cmdArgsDef()
    queue_id = cmd_args.log
    checkFlag=cmd_args.check

    ip=socket.gethostbyname(socket.gethostname())
    conf_name=getScheduleNameByIp(ip)

    conf_max_run_num=getScheduleConfByName(conf_name,"max_run_num")
    if not conf_max_run_num:
        conf_max_run_num=0

    queue_info,running_flag = get_run_info(queue_id)
    app_name = queue_info['app_name']
    module_name = queue_info['run_module']
    step = queue_info['step']
    test = queue_info['is_test']
    stat_date = queue_info['stat_date']
    status = queue_info['status']
    schedule_level=queue_info['schedule_level']#运行任务类型minute,hour,day
    priority=queue_info['priority']
    task_creater=queue_info['creater']#任务提交者，例行为空
    task_ready_time=queue_info['ready_time']#任务就绪时间
    job_type=queue_info['job_type']#任务类型
    job_params=queue_info['params']#任务参数配置
    task_queue=queue_info['task_queue']#任务提交列队
    submitter=queue_info['submitter']#任务提交者
    white_stat_date=None#特殊项目时间
    second_check=queue_info['second_check']#二级校验时间



    #job_type为hql 用户查询工具
    if 'hql'==job_type:
        query_run(job_params,queue_id,stat_date)

        sys.exit(-1)

    #user_access_count项目特殊逻辑
    white_project_list=conf.WHITE_PROJECT_LIST.split(',')
    if app_name in white_project_list:
        white_stat_date=stat_date
        stat_date='2015-07-16'

    stat_hour=0
    stat_minute=0
    if 'minute'==schedule_level or 'hour'==schedule_level:
        stat_time=time.strptime(stat_date,"%Y-%m-%d %H:%M")
        stat_hour=str(int(time.strftime("%H",stat_time)))
        stat_minute=str(int(time.strftime("%M",stat_time)))
        stat_date=str(time.strftime("%Y-%m-%d",stat_time))



    if not os.path.exists(conf.TMP_LOCK_PATH):
        os.makedirs(conf.TMP_LOCK_PATH)


    conf.OUTPUT_PATH += app_name + '/'
    # conf.LOG_PATH += app_name +'/'
    conf.LOG_PATH += app_name

    # conf.TMP_SQL_PATH += app_name + '/'
    conf.TMP_SQL_PATH += app_name
    conf.TMP_LOCK_PATH += app_name + '/'


    if test == True:
        conf.ENTRY_TABLE = conf.TEST_ENTRY_TABLE
        conf.TABLE_PREFIX = conf.TEST_TABLE_PREFIX

    else:
        conf.ENTRY_TABLE = conf.PRODUCT_ENTRY_TABLE
        conf.TABLE_PREFIX = conf.PRODUCT_TABLE_PREFIX

    if not os.path.exists(conf.LOG_PATH):
        os.makedirs(conf.LOG_PATH)

    if not os.path.exists(conf.OUTPUT_PATH):
        os.makedirs(conf.OUTPUT_PATH)

    if not os.path.exists(conf.TMP_SQL_PATH):
        os.makedirs(conf.TMP_SQL_PATH)
    log_file = '%s/run_%s_%s_%s.log' %(conf.LOG_PATH,stat_date,module_name,queue_id)
    if 'minute'==schedule_level or 'hour'==schedule_level:
        log_file = '%s/run_%s_%s_%s_%s_%s.log' %(conf.LOG_PATH,stat_date,stat_hour,stat_minute,module_name,queue_id)

    for handler in logger.handlers:
        if isinstance(handler,logging.FileHandler):
            handler.__init__(log_file)
            handler.setFormatter(conf.LOG_FORMAT)

    (hql, cat, group),hql_params = get_hql(app_name, module_name, stat_date,stat_hour,stat_minute)
    #机器内存大于10G时才运行
    memory_tip=check_memory()

    if not memory_tip:
        logger.error('memory is less than 2G ')
        if checkFlag==True:
            update_queue_status(conf.WAITING)
        sys.exit(-1)

    if checkFlag==True:
         #同一项目 同一模块 相同日期不可同时执行
        print 'now running same job num',running_flag
        if  running_flag:
            logger.info("check start")
            try:
                lock_file = '%s/%s_%s_%s.lock' %(conf.LOCK_PATH,app_name,module_name,stat_date)
                if 'minute'==schedule_level or 'hour'==schedule_level:
                    lock_file = '%s/%s_%s_%s_%s_%s.lock' %(conf.LOCK_PATH,app_name,module_name,stat_date,stat_hour,stat_minute)
                l = SingletonLock(lock_file)
                l.nb_lock()
            except IOError,e:
                logger.info('locked by another process,pls wait'+lock_file)
                update_queue_status(conf.WAITING)
                sys.exit(-1)
            l.unlock()
        tables=dict()
        if  group.has_key('tables'):
            tables=group['tables']

        test_table_ready(tables,stat_date,schedule_level,stat_hour,stat_minute,second_check)
    else:
        #logger.info('run start log file path is %s' % log_file)
        try:
            lock_file = '%s/%s_%s_%s.lock' %(conf.LOCK_PATH,app_name,module_name,stat_date)
            if 'minute'==schedule_level or 'hour'==schedule_level:
                lock_file = '%s/%s_%s_%s_%s_%s.lock' %(conf.LOCK_PATH,app_name,module_name,stat_date,stat_hour,stat_minute)
            l = SingletonLock(lock_file)
            l.nb_lock()
            lock_flag=True
        except IOError,e:
            logger.info('locked by another process,but run again'+lock_file)
            lock_flag=False
            # l.unlock()
            # update_queue_status(conf.READY)
            sys.exit(-1)

        run_hql(hql, cat, group,status)
        if lock_flag is True:
            l.unlock()
    #强制退出
    sys.exit(-1)
