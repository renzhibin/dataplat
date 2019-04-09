#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import web
import json
import mms.lib.app_token_conf as tokenConf

from lib import *


class GetTokenApps(action.Action):
    def POST(self):
        self.GET()

    def GET(self):

        user_name=web.input().get("user_name")
        is_super = web.input().get("is_super")
        if not user_name:
            return base.retu(1,"用户名为空")

        obj_token_conf=tokenConf.AppTokenConf()
        if not is_super:
            app_list = obj_token_conf.select(user_name=user_name)
        else :
            app_list = obj_token_conf.select(user_name=None)


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
        table_list={}

        if len(project_str)>0:
            project_list=str(project_str[0]["project_name"]).split(",")
            for line in project_list:
                if line and "0"!=line and "None"!=line:
                    result.append(line)
                    table_list[line] = ''


        if project_str[0]["table_id"] is not None:
            table_conf = json.loads(project_str[0]["table_id"])

            for line in table_list:
                if line in table_conf:
                    table_list[line] = table_conf[line]

        return base.retu(0,"project list",{"project_list":result,"table_list":table_list})

class ChangeAppProjects(action.Action):

    def POST(self):
        self.GET()

    def GET(self):
        app_id=web.input().get("app_id").encode('utf-8')
        status=web.input().get("status").encode('utf-8')
        obj_token_conf = tokenConf.AppTokenConf()

        if  not app_id :
            return base.retu(1, "应用ID为空")
        if (status) == '1' :
            state,msg = obj_token_conf.update_status(app_id, status)
        elif (status) == '0':
            state, msg = obj_token_conf.update_status(app_id, status)

        if state:
            return base.retu(0,msg,{})
        else:
            return base.retu(1,msg,{})

        return base.retu(1,"检查参数")

class SaveReportJson(action.Action):

    def POST(self):
        self.GET()

    def GET(self):
        app_name=web.input().get("app_name").encode('utf-8')
        arr_json=web.input().get("arr_json")
        arr_json=json.dumps(arr_json)
        user_name=web.input().get("user_name").encode('utf-8')
        obj_token_conf = tokenConf.AppTokenConf()

        if  not app_name :
            return base.retu(1, "应用名称为空")
        if not arr_json:
            return base.retu(1, "报表为空")
        if not user_name:
            return base.retu(1, "用户名为空")

        status, msg = obj_token_conf.save_report_json(app_name, arr_json, user_name)
        if status:
            return base.retu(0,msg,{})
        else:
            return base.retu(1,msg,{})

        return base.retu(1,"检查参数")


class GetReportList(action.Action):
    def POST(self):
        self.GET()

    def GET(self):
        app_name=web.input().get("app_name").encode('utf-8')
        user_name=web.input().get("user_name").encode('utf-8')
        obj_token_conf = tokenConf.AppTokenConf()

        if  not app_name :
            return base.retu(1, "应用名称为空")
        if not user_name:
            return base.retu(1, "用户名为空")
        list = obj_token_conf.select(app_name, '', user_name)
        return base.retu(0, "success", {"app_list": list})

class CheckReportList(action.Action):
    def POST(self):
        self.GET()

    def GET(self):
        app_name = web.input().get("app_name").encode('utf-8')
        app_token = web.input().get("app_token").encode('utf-8')
        obj_token_conf = tokenConf.AppTokenConf()

        if not app_name:
            return base.retu(1, "应用名称为空")
        if not app_token:
            return base.retu(1, "Token为空")
        list = obj_token_conf.select(app_name, app_token, '')
        return base.retu(0, "success", {"app_list": list})