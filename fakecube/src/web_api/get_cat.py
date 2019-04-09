import web
from lib import base
import mms.lib.app_conf as appObj
import json

class GetCat():
    def POST(self):
        user_data=web.data()
        param=json.loads(user_data)
        project_name=param['app_name']

        cg_list=[]

        if not project_name:
            return base.retu('-1','no app_name given')
        appConf=appObj.AppConf(project_name)

        if not appConf.appExist:
            return base.retu('-1','no project')

        try:
            temp = appConf.appConf
            for category in temp['project'][0]["categories"]:
                for group in category['groups']:
                    cg_name = '.'.join((category['name'],group['name']))
                    cg_list.append(cg_name)
            return ','.join(cg_list)
        except Exception,ex:
            import traceback
            traceback.print_exc()
            return base.retu('-1',ex.message)
    def GET(self):
        user_data=web.input()
        project_name=user_data.project

        cg_list=[]

        if not project_name:
            return base.retu('-1','no app_name given')

        appConf=appObj.AppConf(project_name)
        if not appConf.appExist:
            return base.retu('-1','no project')

        try:
            temp = appConf.appConf
            for category in temp['project'][0]["categories"]:
                for group in category['groups']:
                    cg_name = '.'.join((category['name'],group['name']))
                    tmp={'cn_name':group['cn_name'],'en_name':cg_name}
                    cg_list.append(tmp)
            return base.retu(0,'',cg_list)
        except Exception,ex:
            import traceback
            traceback.print_exc()
            return base.retu('-1',ex.message)

