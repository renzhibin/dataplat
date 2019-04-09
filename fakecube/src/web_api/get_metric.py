#!/usr/bin/env python2.7
# coding=utf-8

import web
import yaml
from lib import const,action,base
import mms.lib.app_conf as appObj

dir_path = const.dir_path


class GetMetric(action.Action):
    def GET(self):
        try:
            user_data = web.input(project='', dim_set='')
            project_name = user_data.project
            dim_set = user_data.dim_set
            dim_set=dim_set.lower()
            if dim_set == '()' or dim_set == '' or dim_set == 'all':
                dim_set = ''

            appConf=appObj.AppConf(project_name)
            temp = appConf.appConf

            dim_list = dim_set.split(',')
            schedule_interval=False
            hour_exist=True
            tmp_dim_list=dim_set.split(',')
            #是否包含小时维度
            if 'minute' in dim_list and 'hour' not in dim_list:
                hour_exist=False
            if 'hour' in dim_list:
                dim_list.remove('hour')
                schedule_interval=True
            if 'minute' in dim_list:
                dim_list.remove('minute')
                schedule_interval=True

            dim_set = set(dim_list)
            result = []
            for category in temp['project'][0]["categories"]:
                category["name"]=category["name"].lower()
                for group in category["groups"]:

                    group["name"]=group["name"].lower()
                    cg_name = '.'.join((category["name"], group["name"]))
                    for e in group["dim_sets"]:
                        dims = ()
                        if e["name"][0] == '(' and e["name"][-1] == ")":
                            temp = e["name"][1:-1].split(',')
                            temp = [e.strip().lower() for e in temp]
                            temp_set = set(temp)
                            dims = temp_set
                        else:
                            temp_single = set([e["name"].lower()])
                            dims = temp_single
                        if dim_set == dims:
                            if schedule_interval:
                                if group.has_key('schedule_interval') and group['schedule_interval']:
                                    import re
                                    interval=group['schedule_interval']
                                    r = re.compile(r'^(\d+)(_(\d+)+)?$')
                                    res=r.findall(str(interval))
                                    if res:
                                        i=res[0]
                                        if i[0] and 0!=int(i[0]) and not i[2] and hour_exist:
                                            #分钟任务选择小时必须要选择分钟
                                            if 60!=int(i[0]) and 'minute' not in tmp_dim_list:
                                                continue
                                            if 60==int(i[0]) and 'minute' in tmp_dim_list:
                                                continue

                                            for e in group["metrics"]:
                                                result.append(cg_name + '.' + e["name"].lower())
                            else:
                                for e in group["metrics"]:
                                    result.append(cg_name + '.' + e["name"].lower())
            retu = base.retu('', '', result)

        except:
            retu = base.retu(1, 'no such app')

        return retu

