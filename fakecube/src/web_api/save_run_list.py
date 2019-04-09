#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import web

from lib import *
import  mms.conf.env  as conf
import mms.conf.mms_mysql_conf as mmsMysqlConf
from mms.lib.mms_mysql import MmsMysql
from mms.lib.utils import insertTask, getRunTaskList, checkPersonalIsCanRunHql

dir_path = const.dir_path

class SaveRunList(action.Action):

    def POST(self):
        userData=web.input(project=None,start_time=None,end_time=None,creater=None,step=None)
        project=userData.project
        creater=userData.creater
        run_module=userData.run_module
        start_time=userData.start_time
        end_time=userData.end_time
        step = userData.step
        if not project or not start_time or not run_module or not end_time:
            return base.retu('1','参数为空')

        template = getRunTaskList(project, run_module, start_time, end_time, step, creater)
        is_can_run = checkPersonalIsCanRunHql(creater, len(template))
        if is_can_run != True:
            return base.retu('1', is_can_run)
        insertTask(template)

        return base.retu('0','success')

    def GET(self):
        return self.POST()

