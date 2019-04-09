#!/usr/bin/env python2.7
#coding=utf-8

import os
import time
import socket

import web
import requests

import mms.conf.env as conf
from mms.lib.mms_mysql import MmsMysql
import mms.conf.mms_mysql_conf as mmsMysqlConf
from mms.lib.utils import getScheduleNameByIp
from mms.lib.utils import getScheduleConfByName
from mms.lib.utils import getAllScheduleConf


class GetRealLogDetail:

    def GET(self):
        user_data = web.input(serial = '', app_name = '', stat_date = '', stat_time = '', module_name = '')
        file_id = user_data.serial
        app_name = user_data.app_name
        stat_date = user_data.stat_date
        stat_time = user_data.stat_time
        module_name = user_data.module_name


        web.header('Content-type', 'text/html')
        web.header('Transfer-Encoding', 'chunked')

        if not file_id:
            return 'not file name given'

        log_file_path='%s/%s/%s/run_%s_%s_%s.log' %(conf.LOG_PATH, app_name, stat_date, stat_time, module_name, file_id)

        if not os.path.exists(log_file_path):
            return 'file not found'
        log_file = open(log_file_path)
        log_cont = log_file.read()
        br = '<br>'

        tmp = ''
        for line in log_cont.split('\n'):
           tmp += line + br

        return tmp