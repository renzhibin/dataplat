#!/usr/bin/env python2.7
# coding=utf-8

import web, collections
import json
import yaml
import os,re
from lib import *

class CheckName(action.Action):
    def GET(self):
        try:
            #hql_type：1报表类，2调度类 storetype：1老mysql 3hbase  type：project 项目名 group分类名 sql sql名
            user_data = web.input(hql_type='',storetype='',type='',val='')
            type=user_data.type
            hql_type=user_data.hql_type
            storetype=user_data.storetype
            val=user_data.val

            #调度类，老mysql类型不用校验长度

            if type not in ['project','group','hql']:
                return base.retu(1,'type参数不正确。')

            #项目
            if 'project'==type:
                if not self.check_enname(val,hql_type,storetype,checklen=40):
                    return base.retu(1,'项目英文名必须小于40个字符，格式必须为数字、字符串或者下划线')
                if not self.check_projectname(val):
                    return base.retu(2, '项目名被占用,请重新填写项目名')

            elif 'group'==type:
                if not self.check_enname(val,hql_type,storetype):
                    return base.retu(1,'类目名必须小于20个字符,格式必须为数字、字符串或者下划线')
            elif 'hql'==type:
                if not self.check_enname(val,hql_type,storetype):
                    return base.retu(1,'hql名必须小于20个字符,格式必须为数字、字符串或者下划线')

            return base.retu(0,'success')

        except:
            import traceback

            traceback.print_exc()
            return base.retu(1, 'error')


    def POST(self):
        self.GET()
    def check_enname(self, name,hql_type,storetype,checkLength=True,checklen=20):

        if not str(hql_type) or not str(storetype):
            return False
        if checkLength and (int(hql_type) not in [2] and int(storetype) not in [1,4] and len(name)>checklen):
            return  False
        pattern = re.compile(r'^[_a-zA-Z0-9]+$')
        match = pattern.match(name)
        if match:
            return True
        return False


    def check_projectname(self,project_name):
        project_name_white_list=['web_api','all_app','system_log','hql_tools']
        import mms.lib.mms_conf as mms_conf

        object_mms_conf = mms_conf.MmsConf()
        if project_name in project_name_white_list:
            return False
            #生成create time
        result = object_mms_conf.select(project_name)
        if result:
            return False

        return True