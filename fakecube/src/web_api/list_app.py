#!/usr/bin/env python2.7
#coding=utf-8

import web,sys,json,yaml,os
import  mms.lib.mms_conf as mms_conf

from lib import const
from lib import action
from lib import base
dir_path = const.dir_path

def showhtml(file_list):
    render = web.template.render('templates/')

    return render.list_app(file_list)

class ListApp(action.Action):
    def GET(self):


        user_data=web.input(showtype='json',type='mysql')
        type_name = user_data.type
        show=user_data.showtype
        try:
            object_mms_conf=mms_conf.MmsConf()
            result=object_mms_conf.select()
            retu_reuslt=[]
            for tmp  in result:
                tmp['project']=tmp['appname']
                del tmp['appname']
                if  tmp['storetype']==4:
                    tmp['hql_type']=2
                else:
                    tmp['hql_type']=1
                retu_reuslt.append(tmp)
        except:
            import  traceback
            print traceback.print_exc()
            return base.retu(1,'mysql error')

        return base.retu('','',retu_reuslt)

