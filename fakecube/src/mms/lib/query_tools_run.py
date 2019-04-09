#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import os
import sys
import logging
import time
import json
import re
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.base import MIMEBase
import email

import env as conf
from log4me import MyLogger
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
from fun_replace import FuncReplace

logger=MyLogger.getLogger()

def query_run(params=None,queue_id='',stat_date=None):
    try:
        if params:

            params=json.loads(params)
            start=time.time()

            hql=params['hql']

            replace_params=params['replace_params']
            replace_params_list=replace_params.strip().split('\n')
            tmp_replace_params=','.join(replace_params_list)
            replace_params_list=["'"+ele+"'" for ele in replace_params_list]
            replace_params=','.join(replace_params_list)

            cols_params=params['cols_params']
            task_creater=params['task_creater']
            email_users=params['email_users']
            email_users=email_users.split(',')
            query_tool_name=params['query_tool_name']
            start_time=None
            if params.has_key('starttime'):
                start_time=params['starttime']
            end_time=None
            if params.has_key('endtime'):
                end_time=params['endtime']
            r_p={'replace_params':replace_params,'dt':stat_date,'start':start_time,'end':end_time}
            hql=repalce_params(hql,r_p)


            tmp_sql_file='%stmp_%s_%s.sql'%(conf.TMP_SQL_PATH,str(stat_date),str(queue_id))

            tmp_res_file='%s.%s'%('hql_tools_res',str(queue_id))
            conf.OUTPUT_PATH+='hql_tools/'
            conf.LOG_PATH+='hql_tools/'
            tmp_res_log='run_%s_%s_%s.log'%(stat_date,'hql_tools',str(queue_id))

            if not os.path.exists(conf.LOG_PATH):
                os.makedirs(conf.LOG_PATH)

            if not os.path.exists(conf.OUTPUT_PATH):
                os.makedirs(conf.OUTPUT_PATH)
            out_file='%s%s'%(conf.OUTPUT_PATH,tmp_res_file)

            log_file='%s%s'%(conf.LOG_PATH,tmp_res_log)


            for handler in logger.handlers:
                if isinstance(handler,logging.FileHandler):
                    handler.__init__(log_file)
                    handler.setFormatter(conf.LOG_FORMAT)

            with open(tmp_sql_file,'w') as f:
                f.write(str(hql))
            logger.info("hql:%s"%(hql))
            cmd = "hive -f %s >%s 2>>%s" % (tmp_sql_file, out_file, log_file)
            update_queue_status(conf.RUNNING,queue_id)

            retu=os.system(cmd)
            end=time.time()
            if retu == 0:
                update_queue_status(conf.HIVEEND,queue_id)
                if os.stat(out_file).st_size == 0:
                    warn_msg="hive result file is empty!"
                    logger.warn(warn_msg)
                logger.info('hql execute success and use %ss' %str(end - start))
            if retu !=0:
                update_queue_status(conf.HIVEEND,queue_id)
                err_msg = "hive execute hql error!"
                logger.error(err_msg)
                error_exit(conf.FAILED, err_msg,queue_id,stat_date,task_creater)


            #读取hql结果
            try:
                f = open(out_file)
            except:
                msg="failed to open hive result file"
                logger.exception(msg)
            line_res_list=[]
            for line in f:
                arr=line.split('\t')
                line_res_list.append(arr)



            #csv结果文件
            if not os.path.exists(conf.TMP_DOWNLOAD_PATH):
                os.makedirs(conf.TMP_DOWNLOAD_PATH)
            hql_download_dir=conf.TMP_DOWNLOAD_PATH+'hql_tools/'
            if not os.path.exists(hql_download_dir):
                os.makedirs(hql_download_dir)

            data_csv_path='%s%s'%(hql_download_dir,'%s.%s'%(str(queue_id),'xls'))

            # create_result_csv(cols_params,line_res_list,data_csv_path)

            with open(data_csv_path, 'wb') as f:
                tmp_data_table=create_result_table(cols_params,line_res_list)
                f.write(tmp_data_table)
            f.close()

            #成功发送邮件

            from mms_email import MmsEmail
            mmsEmail=MmsEmail()
            to_list=[]
            to_list.append(task_creater)
            to_list=to_list+email_users
            to_list=list(set(to_list))

            query_time=str(time.strftime("%Y%m%d%H%M%S",time.localtime(time.time())))
            mail_part=MIMEMultipart()

            mail_con="%s工具查询结果附件中。"%(str(query_tool_name))
            mail_con='''
                离线查询工具名称：%s </br>
                查询内容：%s </br>
                数据接收人：%s</br>
            '''%(str(query_tool_name),tmp_replace_params,','.join(email_users))


            msg=MIMEText(mail_con,"html","utf-8")
            # mail_part.set_payload(msg)

            mail_att=MIMEBase('application','octet-stream')

            data_csv=open(data_csv_path,'rb')
            mail_att.set_payload(data_csv.read())
            data_csv.close()
            email.Encoders.encode_base64(mail_att)
            filename='%s %s.xls'%(str(query_tool_name).encode('utf-8'),str(query_time))
            filename=filename.encode('utf-8')
            mail_att.add_header('Content-Disposition','attachment',filename=filename)

            # mail_part.attach(mail_att)
            mail_sub="【离线查询工具】%s查询结果 %s"%(str(query_tool_name),str(query_time))

            mmsEmail.sendmessage(to_list,mail_sub,msg,mail_att)
            logger.info('查询结果已邮件发送到：%s'%(','.join(to_list)))
            update_queue_status(conf.SUCCESS,queue_id)
    except:
        import traceback
        traceback.print_exc()

#根据data产生csv
def create_result_csv(title,data,data_csv_path):

    if not title or not data:
        return ''

    import csv
    tmp_title=[]
    for e in title:
        for k,v in e.items():
            if v:
                tmp_title.append(v)
            else:
                tmp_title.append(k)
    with open(data_csv_path, 'wb') as f:
        writer = csv.writer(f)
        if len(data[0])==len(tmp_title):
            writer.writerow(tmp_title)
        writer.writerows(data)
    f.close()

#根据data产生邮件表格内容
def create_result_table(title,data):
    if not title or not data:
        return ''
    meta='<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'
    table_start="<table border='1'><tbody>"
    table_end="</tbody></table>"

    th='<tr>'
    tr_con=''
    for e in title:
        for k,v in e.items():
            if v:
                th+="<td style='background:cornsilk'>"+v+"</td>"
            else:
                th+="<td style='background:cornsilk'>"+k+"</td>"

    th+="</tr>"

    for e in data:
        tr="<tr>"
        td=""
        for i in e:
            td+="<td>"+i+"</td>"
        tr+=td+"</tr>"
        tr_con+=tr

    table=meta+table_start+th+tr_con+table_end

    return table





def update_queue_status(status,queue_id):
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

        cur.execute(sql)
        conn.commit()
        conn.close()
    except:
        logger.exception("get running cnt exception")
        sys.exit(-1)


def repalce_params(hql,params):
        udf = ['DATE','START','END']
        reg_udf = '|'.join(udf)
        r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
        result = r.findall(hql)
        obj = FuncReplace()
        for content in result:
            if content[1]=='START' and not params['start']:
                continue
            if content[1]=='END' and not params['end']:
                continue
            b = getattr(obj, content[1])
            replace_str = b(params,content[2])
            hql = hql.replace(content[0], replace_str)
        hql=hql.replace('$vars',params['replace_params'])

        return hql


def error_exit(err_code, err_msg,queue_id,stat_date,task_creater):
    update_queue_status(err_code,queue_id)
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
            href=href%(host,queue_id,'hql_tools',stat_date,'hql_tools')
            html_a="<a href='%s'>查看日志</a>"%(href)

            from mms_email import MmsEmail
            email_to=['zhibinren','bangzhongpeng']

            #任务提交人
            email_to.append(task_creater)
            email_obj = MmsEmail()
            content=err_msg
            content='%s %s'%(content,html_a)
            content=MIMEText(content,"html","utf-8")
            err_title='【监控】fakecube失败任务'
            retu = email_obj.sendmessage(email_to, err_title, content)
            logger.info('email send %s' % retu)

    sys.exit(-1)


def check_query_tools_hql(hql):
    import commands,random

    if not os.path.exists(conf.TMP_SQL_PATH):
        os.mkdir(conf.TMP_SQL_PATH)

    now_time=time.strftime("%Y_%m_%d_%H_%M_%S",time.localtime(time.time()))
    random_num = random.randrange(0,10000)

    tmp_sql_file = '%s/tmp_%s_%s.sql' %(conf.TMP_SQL_PATH,now_time,random_num)

    with open(tmp_sql_file,'w') as f:
        f.write('explain EXTENDED %s' %hql)

    cmd = "hive -f %s" %tmp_sql_file

    status, output = commands.getstatusoutput(cmd)

    if status != 0:
        retu = output
    else:
        os.remove(tmp_sql_file)
        retu='success'

    return status, retu












