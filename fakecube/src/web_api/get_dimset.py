#!/usr/bin/env python2.7
# coding=utf-8

import web
from lib import const,action,base
import mms.lib.app_conf as appOjb


dir_path = const.dir_path


class GetDimset(action.Action):
    def GET(self):
        try:
            project_name = web.input().get('project')
            appConf=appOjb.AppConf(project_name)
            temp=appConf.appConf
            result = {}
            for category in temp['project'][0]["categories"]:
                for group in category["groups"]:
                    schedule_interval=False
                    interval_val_hour=False
                    if group.has_key('schedule_interval') and group['schedule_interval']:
                        #判断是否小时
                        interval=group['schedule_interval']
                        interval_split=interval.split('_')
                        if len(interval_split)==1 and int(interval_split[0])==60:
                            schedule_interval=True
                    if schedule_interval:
                        tmp_group=group["dimensions"]
                        dim_hour={'cn_name':'小时','explain':'小时维度','name':'hour'}
                        # dim_minute={'cn_name':'分钟','explain':'分钟维度','name':'minute'}
                        tmp_group.append(dim_hour)
                        if interval_val_hour:
                            dim_minute={'cn_name':'分钟','explain':'分钟维度','name':'minute'}
                            tmp_group.append(dim_minute)
                        group['dimensions']=tmp_group

                    for d_dim in group["dimensions"]:

                        name = d_dim['name'].lower()
                        result.setdefault(name, dict())

                        if d_dim.has_key('cn_name') and d_dim['cn_name'] and len(d_dim['cn_name']) > 0:
                            result[name]['cn_name'] = d_dim['cn_name']
                        if d_dim.has_key('explain') and d_dim['explain'] and len(d_dim['explain']) > 0:
                            result[name]['explain'] = d_dim['explain']

                    for d_dim in group['dim_sets']:
                        if d_dim['name'] == '()':
                            result.setdefault('all', dict())
                            result['all']['cn_name'] = '总量(系统自动生成)'
                            result['all']['explain'] = '无实际意义，不会展现'
                        else:
                            str_dim = d_dim["name"].strip('()').split(',')
                            l_dim = [e.lower().strip() for e in str_dim]
                            if schedule_interval:
                                l_dim.append('hour')
                                if interval_val_hour:
                                    l_dim.append('minute')
                            for k in l_dim:
                                result[k].setdefault('dim', [])
                                result[k]['dim'].append(l_dim)

            retu_result = []

            func = lambda x, y: x if y in x else x + [y]
            for k, v in result.items():
                dim = ''
                if v.has_key('dim'):
                    dim = reduce(func, [[], ] + v['dim'])
                tmp = {'name': k, 'cn_name': k, 'explain': k, 'dim': dim}
                if v.has_key('explain'):
                    tmp['explain'] = v['explain']
                if v.has_key('cn_name'):
                    tmp['cn_name'] = v['cn_name']

                retu_result.append(tmp)
            retu = base.retu('', '', retu_result)
        except:
            import traceback

            traceback.print_exc()

            retu = base.retu(1, 'no such app')

        return retu

