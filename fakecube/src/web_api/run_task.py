#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'xingjieliu'
import web
import mms.lib.utils as utils
import  mms.conf.env  as conf
from mms.lib.getHqlByReportId import *
from lib import *
from mms.lib.mms_mysql import MmsMysql
import requests
import traceback
import json

class RunTask(action.Action):

    def GET(self):
        user_data = web.input(report_id="", user_name="", start_time="", end_time="", ext_json="")
        report_id = user_data.report_id
        user_name = user_data.user_name
        start_time = user_data.start_time
        end_time = user_data.end_time
        step = user_data.ext_json
        
        if not start_time or not end_time:
            return base.retu("1", "任务开始时间和结束时间未设置")

        hql_list = getHqlByTableId(report_id)
        app_module_dict = {}

        if len(hql_list) == 0:
            return base.retu("1", "获取任务列表失败")
        else:
            for hql in hql_list:
                pl = hql.split('.')
                app_name = pl[0]
                run_module = '.'.join(pl[1:])
                if app_module_dict.has_key(app_name):
                    app_module_dict[app_name].append(run_module)
                else:
                    app_module_dict[app_name] = [run_module]

        # 计算出插入全部的
        url = 'http://scheduler.qudian.com/job/run_job'
        cube_id = ''
        pos = user_name.index('@')
        name = user_name[0:pos]
        for app_name, run_module_list in app_module_dict.iteritems():
            for run_module in run_module_list:
                cube_id = getMmsAppConfIdByAppName(app_name, run_module)
                cube_id = cube_id[0]['id']
        params = {
            'unq_job_name': 'cube_' + str(cube_id),
            'run_start_time': start_time,
            'run_end_time': end_time,
            'creater': name,
            'ext_json': step
        }

        flag, result = self.httpPostByDict(url, params)
        if flag == False:
            return base.retu("1", "di接口请求失败")
        result = json.loads(result)
        if result['status'] != '0':
            return base.retu("1", result['msg'])
        return base.retu("0", "当前报表下的任务已经加入运行队列")

    def httpPostByDict(self, url, params_dict):
        flag = False
        result = ''
        try:
            res = requests.post(url,
                                data=params_dict)
            if res.status_code == 200:
                flag = True
                result = res.text
        except Exception:
            traceback.print_exc()
        return flag, result

    def GET_back(self):
        user_data=web.input(report_id="", user_name="", start_time="", end_time="")
        report_id = user_data.report_id
        user_name = user_data.user_name
        start_time = user_data.start_time
        end_time = user_data.end_time

        if not start_time or not end_time:
            return base.retu("1", "任务开始时间和结束时间未设置")

        hql_list = getHqlByTableId(report_id)
        app_module_dict={}

        if len(hql_list) == 0:
            return base.retu("1", "获取任务列表失败")
        else:
            for hql in hql_list:
                pl = hql.split('.')
                app_name = pl[0]
                run_module='.'.join(pl[1:])
                if app_module_dict.has_key(app_name):
                    app_module_dict[app_name].append(run_module)
                else:
                    app_module_dict[app_name] = [run_module]

        # 计算出插入全部的
        template_sum = 0
        template_insert_total = []
        for app_name, run_module_list in app_module_dict.iteritems():
            template = utils.getRunTaskList(app_name, run_module_list, start_time, end_time, 'all', user_name)
            if len(template)!=0:
                template_sum += len(template)
                template_insert_total.append(template)

        is_can_run = utils.checkPersonalIsCanRunHql(user_name, template_sum)
        if is_can_run != True:
            return base.retu('1', is_can_run)
        # 插入数据库
        for template_insert in template_insert_total:
            utils.insertTask(template_insert)

        return base.retu("0", "当前报表下的任务已经加入运行队列")


    def POST(self):
        return self.GET()