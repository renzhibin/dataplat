#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

from email.mime.text import MIMEText
import sys
import re

import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql

'''
获取依赖task_list表表列表
'''
def get_stop_tasks(app_id,task_list):
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()

    task_table={}

    sql='''
        select t1.appname app_name,t1.cn_name app_cn_name,
        t1.creater app_creater,
        t1.editor app_editor,
        t2.cn_name v_cn_name,
        t2.creater,
        t2.id,
        t2.metric
        from
        (select appname,cn_name,creater,editor from mms_conf where id=%s)t1
        left join
        (select * from t_visual_table where flag=1 or flag=2)t2
        on t1.appname = t2.project
    '''%(app_id)

    cur.execute(sql)
    columns = cur.description
    tmp_app_inf={}
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        tmp_app_inf=tmp
        if tmp['id']:
            metric_list=[]
            if tmp['metric']:
                metric_list=tmp['metric'].split(',')
            tmp_metric={}
            for m in metric_list:
                m=m[0:m.rindex('.')]
                if not tmp_metric.has_key(m):
                    tmp_metric[m]=None
                    if m in task_list:
                        if not task_table.has_key(m):
                            task_table[m]=[]
                        task_table[m].append(tmp)
    conn.close()
    print task_table.keys()
    if len(tmp_app_inf)>0:

        for m in task_list:
            if m not in task_table.keys():
                tmp_app_inf['v_cn_name']=None
                tmp_app_inf['creater']=None
                tmp_app_inf['id']=None
                tmp_app_inf['metric']=None
                task_table[m]=[tmp_app_inf]

    return task_table


def run(app_id,tasks_str,editor=''):

    stop_tasks=get_stop_tasks(app_id,tasks_str.split(','))
    to_mail_user_dict=[]
    '''
        {创建人:{appname:{报表信息}}}
    '''
    mail_con=''

    removeSuffix = re.compile('@|@.com');
    show_report_url='http://dt..com/report/showreport/'
    for k,v in stop_tasks.items():
        if len(v)>0:
            p_inf=v[0]
            tmp_mail_con='''
                项目 <b>%s（%s）</b>中任务<b>%s</b>被<b>%s</b>手动停止。</br>

            '''
            tmp_mail_con2='''
                受影响报表：%s 。</br></br>
            '''
            v_tables=[]
            for e in v:
                t_b=''
                if e['id']:
                    t_b="<a href='%s%s'>%s</a>"%(show_report_url,e['id'],e['v_cn_name'])
                    v_tables.append(t_b.encode('utf-8'))
                to_mail_user_dict.append(removeSuffix.sub('', e['app_creater']))
                to_mail_user_dict.append(removeSuffix.sub('', e['app_editor']))
                if e['creater']:
                    to_mail_user_dict.append(removeSuffix.sub('', e['creater']))
                to_mail_user_dict.append(removeSuffix.sub('', editor))
            tmp_v_tables_str=','.join(v_tables)
            if not v_tables:
                tmp_mail_con2='%s'
            tmp_mail_con=tmp_mail_con+tmp_mail_con2
            tmp_mail_con=tmp_mail_con%(p_inf['app_cn_name'].encode('utf-8'),p_inf['app_name'].encode('utf-8'),k.encode('utf-8'),editor,','.join(v_tables))
            mail_con+=tmp_mail_con
    if len(to_mail_user_dict)>0:
        from mms_email import MmsEmail
        mmsEmail=MmsEmail()
        # to_mail_user_dict.append('data_alarm')
        to_list=list(set(to_mail_user_dict))
        mail_sub="【监控】data平台项目hql手动停止报警"
        msg=MIMEText(mail_con,"html","utf-8")
        mmsEmail.sendmessage(to_list,mail_sub,msg)
        print '邮件成功'

if __name__=='__main__':
    run('1293','general_activity_new.dress_detail_gmv')
