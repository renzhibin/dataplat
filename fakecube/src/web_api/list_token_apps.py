#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import web
import mms.lib.app_token_conf as tokenConf
from lib import *

class GetTokenApps(action.Action):
    def POST(self):
        self.GET()

    def GET(self):

        user_name=web.input().get("user_name")
        if not user_name:
            return base.retu(1,"用户名为空")

        obj_token_conf=tokenConf.AppTokenConf()

        app_list=obj_token_conf.select(user_name=user_name)

        return base.retu(0,"success",{"app_list":app_list})



class GetAppProjects(action.Action):
    def POST(self):
        self.GET()
    def GET(self):
        app_name=web.input().get("app_name")

        if not app_name:
            return base.retu(1,"应用名为空！")

        obj_token_conf=tokenConf.AppTokenConf()

        project_str=obj_token_conf.select(app_name)
        result=[]
        if len(project_str)>0:
            project_list=str(project_str[0]["project_name"]).split(",")

            for line in project_list:
                if line and "0"!=line and "None"!=line:
                    result.append(line)

        return base.retu(0,"project list",{"project_list":result})

