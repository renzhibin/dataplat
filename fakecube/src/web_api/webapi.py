#!/usr/bin/env python2.7
#coding = utf-8

import web
import os
import sys
sys.path.append('..')
sys.path.insert(0,'/home/ec2-user/bi.analysis/fakecube/src/mms/conf')
sys.path.insert(0,'/home/ec2-user/bi.analysis/fakecube/src/mms/lib')
sys.path.insert(0,'/home/ec2-user/bi.analysis/fakecube/src/mms/lib/db')
import  mms.conf.env  as conf

from list_app import ListApp
from get_app_conf import GetAppConf
from query_app import QueryApp
from get_metric import GetMetric
from get_dimset import GetDimset
from get_run_log import GetRunLog
from get_log_detail import GetLogDetail
from get_real_log_detail import GetRealLogDetail
from get_log_monitor import RunMonitorInfo
from get_cat import GetCat


urls = (

         '/query_form/?', 'QueryForm',
         '/save_form/?' ,'SaveForm',
         '/get_dimset/?', 'GetDimset',
         '/get_metric/?', 'GetMetric',
         '/list_app/?', 'ListApp',
         '/get_app_conf/?', 'GetAppConf',
	     '/query_app/?', 'QueryApp',
         '/get_project/?', 'get_project.GetProject',
         '/get_profile/?','get_profile.GetProfile',
         '/save_project/?','save_project.SaveProject',
        # '/get_run_log/?','GetRunLog',
        # '/rerun/?','ReRun',
         '/get_run_detail/?','GetLogDetail',
         '/get_run_detail_real/?','GetRealLogDetail',
         '/run_monitor_info/?','RunMonitorInfo',
         '/get_cat/?','GetCat',
	     '/apply_app_token/?','apply_app_token.ApplyAppToken',
         '/apply_project/?','apply_app_token.ApplyProject',
         '/get_app_token_change/?','list_token_apps.ChangeAppProjects',
         '/get_app_token_list/?','list_token_apps.GetTokenApps',
         '/get_app_projects/?','list_token_apps.GetAppProjects',
         '/get_project_dim_conf/?','get_project_dim_conf.GetProjectDimConf',
         '/get_schedule_interval/?','get_project.GetScheduleInterval',
         '/save_run_list/?','save_run_list.SaveRunList',
         '/kill_task/?','kill_task.KillTask',
         '/check_name/?','check_name.CheckName',
         '/run_query_tool_task/?','query_tools_api.RunQueryToolTask',
         '/get_query_tools_profile/?','query_tools_api.GetQueryToolsProfile',
         '/get_field_type/?','get_project.GetFieldType',
         '/save_hql_params/?','save_hql_params.SaveHqlParams',
         '/custom_query_app/?','custom_query_app.CustomQueryApp',
         '/set_ready/?', 'set_ready.SetTaskReady',
         '/run_task/?', 'run_task.RunTask',
         '/update_table_status/?','table_tag_interface.SetTableStatus',
         '/get_table_status/?','table_tag_interface.GetTableStatus',
         '/get_table_status_tag/?','table_tag_interface.GetTableStatusByTag',
         '/get_topo_data/?', 'topo_manage_interface.GetTopoData',
         '/get_topo_condition/?', 'topo_manage_interface.GetTopoCondition',
         '/save_topo_run_list/?', 'topo_manage_interface.SaveRunList',
         '/save_report_json/?','list_token_apps.SaveReportJson',
        '/get_list_report/?','list_token_apps.GetReportList',
        '/get_check_report/?','list_token_apps.CheckReportList',

	)


import sys, logging
from wsgilog import WsgiLog
from lib import  config

class Log(WsgiLog):
    def __init__(self, application):
            WsgiLog.__init__(
            self,
            application,
            logformat = config.logformat,
            datefmt = config.datefmt,
            tofile = True,
            file = config.file,
            interval = config.interval,
            backups = config.backups,
            #tostream=True,
            toprint=True
            )




web.config.debug = True
app = web.application(urls, globals())
application=app.wsgifunc(Log)
