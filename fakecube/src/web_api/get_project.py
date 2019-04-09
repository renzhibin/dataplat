#!/usr/bin/env python2.7
#coding=utf-8

import web
import json
import mms.lib.app_conf as appObj
from lib import const,action,base


dir_path = const.dir_path
	   
class GetProject(action.Action):
    def GET(self):
        user_data=web.input(project='example_metric')
        project_name = user_data.project
        render = web.template.render('templates/')

        appConf=appObj.AppConf(project_name)
        yaml_content=appConf.appConf
        run_content=appConf.get_run_list()

        for categories_index in range(0,len(yaml_content['project'][0]['categories'])):
            categories=yaml_content['project'][0]['categories'][categories_index]
            for i  in range(0,len(categories['groups'])):
            #for groups_content in categories['groups']:
                 groups_content=categories['groups'][i]
                 hql=appConf.get_hql(categories['name'],groups_content['name'])
                 sql_name=categories['name']+'_'+groups_content['name']
                 sql_name=sql_name.lower()
                 groups_content['hql']=hql[sql_name]
                 yaml_content['project'][0]['categories'][categories_index]['groups'][i]=groups_content

        yaml_content['run']=run_content
        return render.get_project(json.dumps(yaml_content,default=base.defaultencode))





class GetScheduleInterval(action.Action):

    def POST(self):
        self.GET()

    def GET(self):
        #5/30/60 分钟|0 7_1 &_2 ~7_7 天|30_1 30-2 月
        one_time_list=[]
        one_time_list.append({'key':'0_0','value':'只执行一次(天级别例行调度)','offset':'-1day'})
        one_time_list.append({'key':'0_1','value':'只执行一次(配置结束立即执行)','offset':'-1day'})
        minute_list=[]
        # minute_list.append({'key':'5','value':'5分钟'})
        # minute_list.append({'key':'15','value':'15分钟'})
        # minute_list.append({'key':'30','value':'每30分钟','offset':'-30minute'})
        minute_list.append({'key':'60','value':'每小时','offset':'-1hour'})

        day_hour_list=[]
        for i in range(0,24):
            val = '每天' + str(i) + '点'
            key = '1_' + str(i)
            day_hour_list.append({'key':str(key), 'value':str(val), 'offset':'-1day'})

        week_list=[]
        week_list.append({'key':'7_1','value':'每周一','offset':'-1day'})
        week_list.append({'key':'7_2','value':'每周二','offset':'-1day'})
        week_list.append({'key':'7_3','value':'每周三','offset':'-1day'})
        week_list.append({'key':'7_4','value':'每周四','offset':'-1day'})
        week_list.append({'key':'7_5','value':'每周五','offset':'-1day'})
        week_list.append({'key':'7_6','value':'每周六','offset':'-1day'})
        week_list.append({'key':'7_7','value':'每周日','offset':'-1day'})
        month_list=[]
        for i in range(1,31):
            val='每月'+str(i)+'号'
            key=str('30_')+str(i)
            month_list.append({'key':str(key),'value':str(val),'offset':'-1day'})

        day_list=[]
        day_list.append({'key':'0','value':'每天','offset':'-1day'})
        resutl_list=one_time_list+minute_list+day_list+day_hour_list+week_list+month_list

        return base.retu('0','success',resutl_list)


class GetFieldType(action.Action):
    def POST(self):
        return self.GET()

    def GET(self):
        type_list=[]
        type_list.append({'key':'varchar','value':'字符串(100字符)'})
        type_list.append({'key':'varchar200','value':'字符串(200字符)'})
        type_list.append({'key':'varchar1024','value':'字符串(1024字符)'})
        type_list.append({'key':'decimal','value':'数字'})

        return base.retu('0','success',type_list)


