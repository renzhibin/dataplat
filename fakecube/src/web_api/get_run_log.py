#!/usr/bin/env python2.7
#coding=utf-8

import web

from mms.lib.getRunLog import getRunLog,getAppList


class GetRunLog():
    def GET(self):
        log_list=getRunLog()
        app_list=getAppList()

        render=web.template.render('templates/')
        return render.get_run_log(app_list,log_list)

    def POST(self):
        user_data=web.data()
        param =  dict(p.split('=') for p in user_data.split('&'))
        stat_date = param.get('stat_date',None)
        ret_status = param.get('ret_status',None)
        app_name = param.get('app_name',None)
        # start_time = param.get('start_time',None)

        # start_date = param.get('start_date',None)

        filter_str=""

        if stat_date:
            filter_str += " and stat_date = '%s'" %stat_date
        if ret_status:
            filter_str += " and status = %s" %ret_status
        if app_name:
            filter_str += " and app_name = '%s'" %app_name
        # if start_time:
        #     filter_str += " and start_time rlike '%s'" %start_time.strip()

        # if start_date:
        #     filter_str += " and start_time rlike '%s'" %start_date

        log_list=getRunLog(filter_str)
        app_list=getAppList()

        render = web.template.render('templates/')
        return render.get_run_log(app_list,log_list)

