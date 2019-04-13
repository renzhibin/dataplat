#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'xingjieliu'
import web

import mms.lib.utils as utils
import  mms.conf.env  as conf
from mms.lib.getHqlByReportId import *
from lib import *
from mms.lib.mms_mysql import MmsMysql

class RunTask(action.Action):

    def GET(self):
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