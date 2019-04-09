#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import web
import mms.lib.app_token_conf as tokenConf

from lib import *




class ApplyProject(action.Action):

    def POST(self):
        self.GET()

    def GET(self):

        app_name=web.input().get("app_name")
        project_name=web.input().get("project_name")
        token_val=web.input().get("token_val")
        if app_name and token_val and project_name:
            obj_token_conf=tokenConf.AppTokenConf()
            status,msg=obj_token_conf.apply_project(token_val,project_name,app_name)

            if status:
                return base.retu(0,msg,{})
            else:
                return base.retu(1,msg,{})

        return base.retu(1,"检查参数")










class ApplyAppToken(action.Action):

    def POST(self):
        self.GET()

    def GET(self):

        app_name=web.input().get("app_name")
        user_name=web.input().get("user_name")

        if not self.verify_user_name(app_name):
            return base.retu(1,"app_name is null",{})

        obj_token_conf=tokenConf.AppTokenConf()


        #app Token已经申请
        result=obj_token_conf.select(app_name)
        if len(result)>0:

            return base.retu(3,app_name+"的token已经存在！",{"user_name":user_name,"app_name":result[0]["app_name"],"token_val":result[0]["token_val"]})
        

        #根据用户名产生token
        import hashlib,random
        md5_str=str(app_name+str(random.randint(0,1000)))

        token_val=str(hashlib.new("md5",md5_str).hexdigest()).upper()

        status,msg=obj_token_conf.save_token(user_name,app_name,token_val)
        if status:
            return base.retu(0,msg,{"user_name":user_name,"token":token_val,"app_name":app_name})
        else:
            return base.retu(1,msg,{})



    def verify_user_name(self,app_name=None):
        if app_name:
            return True

        return False





