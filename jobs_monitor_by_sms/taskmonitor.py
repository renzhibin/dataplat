#!usr/bin/python
#encoding=utf-8
########################################################
#author:songsiqi
#create:2017005
#function:schedule monitor
########################################################
import MySQLdb
import os
import ConfigParser
import time
from datetime import datetime
from email import encoders
from email.header import Header
from email.mime.text import MIMEText
from email.utils import parseaddr, formataddr
import smtplib
import requests

#获取每个调度元数据的数据库信息
def GetDBInfo(dbConfigGroupKey, dbConfig):
    cf = ConfigParser.ConfigParser()
    cf.read(dbConfig)
    host = cf.get(dbConfigGroupKey,"host")
    user = cf.get(dbConfigGroupKey,"username")
    passwd = cf.get(dbConfigGroupKey,"password")
    db = cf.get(dbConfigGroupKey,"database")
    port = cf.get(dbConfigGroupKey,"port")
    db_info = []
    db_info.append(host)
    db_info.append(user)
    db_info.append(passwd)
    db_info.append(db)
    db_info.append(port)
    return db_info

#连接数据库
def ConnDB(db_info):
    v_host=db_info[0]
    v_user=db_info[1]
    v_passwd=db_info[2]
    v_db=db_info[3]
    v_port=int(db_info[4])
    conn = MySQLdb.connect(host=v_host,user=v_user,passwd=v_passwd,db=v_db,port=v_port)
    cur = conn.cursor()
    return conn,cur

#发送短信
def sendMsg(contact_list,message):
    url='http://10.161.138.17/alarm/sms'
    to_str=','.join(contact_list)
    header={"Host":"bi.service..com"}
    data={}
    data['mobile']=to_str
    data['content']=message
    resp=requests.get(url,params=data,headers=header)
    if resp.status_code==200:
        print 'send message success.'
    else:
        print 'send message failed.msg:{}'.format(resp.text)
def getDelayJobIdsByJobsConfig(jobsConfig):
    delayList = {}
    nowTimestamp = time.time()
    for shouldFinishTime in jobsConfig:
        shouldFinishTimestamp = getTodayCustomeTimeStampByHourMinuteSecStr(shouldFinishTime)
        if (nowTimestamp < shouldFinishTimestamp):
            continue
        todayShoudDateTime = time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(shouldFinishTimestamp))
        delayList[todayShoudDateTime] = {}
        for project in jobsConfig[shouldFinishTime]:
            delayAppNameAndModules = getDelayModuleNameByAppName(project['app_name'])
            if len(delayAppNameAndModules) == 0:
                continue
            delayList[todayShoudDateTime]['app_name'] = delayAppNameAndModules['app_name']
            delayList[todayShoudDateTime]['modules'] = delayAppNameAndModules['modules']
    return delayList
def getDelayModuleNameByAppName(appName):
    global dbConfig
    global dbConfigGroupKey
    modules = []
    dbInfo = GetDBInfo(dbConfigGroupKey, dbConfig)
    connect, cursor = ConnDB(dbInfo)
    getBlockJobsSql = '''select run_module from mms_run_log where app_name ='{}'  and status=1

    '''.format(appName)
    cursor.execute(getBlockJobsSql)
    blockModules = cursor.fetchall()
    if not blockModules:
        return {}
    for moduleTuple in blockModules:
        for module in moduleTuple:
            modules.append(module)
    return {'app_name' : appName, 'modules' : modules}
def getTodayCustomeTimeStampByHourMinuteSecStr(hourMinuteSecStr):
    nowDateTime = datetime.now()
    todayCustomeDateTime = nowDateTime.strftime('%Y-%m-%d ' + hourMinuteSecStr)
    return time.mktime(time.strptime(todayCustomeDateTime,'%Y-%m-%d %H:%M:%S'))
if __name__=="__main__":
    global dbConfig
    global dbConfigGroupKey
    dbConfig = "/home/apple/bi.analysis/jobs_monitor_by_sms/db.conf"
    dbConfigGroupKey = "metric_meta"
    jobsConfig = {
	    '02:30:00':[
    		{
    		    'app_name' : 'trade'
    		}
	    ],
        '03:30:00':[
            {
                'app_name' : 'subject_index'
            }
        ],
        '04:30:00':[
            {
                'app_name' : 'core_data'
            }
        ]
    }
    phoneList = ['18810502506', '18518437958', '18910687895']
    delayList = getDelayJobIdsByJobsConfig(jobsConfig)
    for shouldFinishDateTime in delayList:
        
        smsContent = ''
        if delayList[shouldFinishDateTime]:
            smsContent = smsContent + shouldFinishDateTime + ' 仍在阻塞的项目名称: ' + delayList[shouldFinishDateTime]['app_name'] + '('
            smsContent = smsContent + '模块名称: ' + ', '.join(delayList[shouldFinishDateTime]['modules'])
            smsContent = smsContent + ')'
        if smsContent != '' :
            sendMsg(phoneList, smsContent)

