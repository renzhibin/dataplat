#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'
import argparse
import os
import re
import subprocess
import time
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import env as conf
from email.mime.text import MIMEText

HADOOP_BIN = '/hadoop/hadoop/bin/hadoop'
TAG_HOME = '/user/data_ready_tag/'
BLACKHOLE = open('/dev/null')

def cmdArgsDef():
    arg_parser = argparse.ArgumentParser()
    arg_parser.add_argument('-g', '--group', help='调度任务名', required=True)
    arg_parser.add_argument('-p', '--project', help='项目名', required=True)
    args = arg_parser.parse_args()

    return args










if __name__=='__main__':
    cmd_args = cmdArgsDef()
    group=cmd_args.group
    project=cmd_args.project
    print '下线table tag项目%s 任务%s'%(project,group)
    code_file=group.replace('.','_')
    sql_file_path = '%s/%s/src/%s.sql' % (conf.CONF_PATH,project, code_file)

    file_object = open(sql_file_path)
    hql = file_object.read().strip().lower()
    file_object.close()

    match_table=re.compile(r'insert\s+(overwrite)?\s+table\s+([\s\S]*?)\s+')
    match_res=match_table.findall(hql)
    if match_res:
        match_res=match_res[0]
        table=match_res[1]

        table=table.split('.')
        db='default'
        par_date=None
        par_hour=None

        if len(table)==2:
            db=table[0]
            table=table[1]
        else:
            table=table[0]
        print '下线table %s'%(table)
        table_tag_dir='%s%s_%s'%(TAG_HOME,db,table)
        get_tag_cmd="%s fs -ls %s"%(HADOOP_BIN,table_tag_dir)
        print get_tag_cmd
        ret_code = subprocess.call(get_tag_cmd,stdout= BLACKHOLE,shell=True)
        if ret_code==0:
            new_tag_name='%s%s_%s_%s'%(TAG_HOME,db,table,str(int(time.time())))
            mv_tag_cmd="%s fs -mv %s %s"%(HADOOP_BIN,table_tag_dir,new_tag_name)
            print mv_tag_cmd
            mv_ret_code=os.system(mv_tag_cmd)
            if mv_ret_code!=0:
                from mms_email import MmsEmail

                mmsEmail=MmsEmail()
                to_list=['bangzhongpeng','zhibinren']
                content='''
                    下线tag表：%s</br>
                    执行命令：%s

                '''%(table,mv_tag_cmd)

                content=MIMEText(content,"html","utf-8")
                stat_date=str(time.strftime("%Y-%m-%d",time.localtime(int(time.time()))))
                mail_sub="【监控】下线tag失败 %s"%(stat_date)

                mmsEmail.sendmessage(to_list,mail_sub,content)

