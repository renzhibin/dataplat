#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import web, traceback
import json
import mms.lib.app_conf as appOjb
from lib import const,action,base

dir_path = const.dir_path


class SaveHqlParams(action.Action):
    def GET(self):
        pass

    def POST(self):
        user_data = web.input(project_name='',category_name='',hql_name='',group=None,project_conf=None,cate_conf=None,creater='', editor='')
        project_name=user_data.project_name
        category_name=user_data.category_name
        hql_name=user_data.hql_name
        group=user_data.group
        project_conf=user_data.project_conf
        cate_conf=user_data.cate_conf
        creater=user_data.creater
        editor=user_data.editor
        if not project_name or not category_name or not hql_name or not group or not project_conf or not cate_conf:
            return base.retu('2','params error')
        group=json.loads(group)
        project_conf=json.loads(project_conf)
        cate_conf=json.loads(cate_conf)
        try:
            appConf=appOjb.AppConf(project_name)
            status,msg=appConf.save_hql(category_name.lower(),hql_name.lower(),project_conf,cate_conf,group,creater,editor)
            if not status:
                return base.retu("1",msg)
            return base.retu('', '', user_data)

        except Exception as e:
            import traceback
            traceback.print_exc()
            return base.retu('1','保存hql参数异常')

