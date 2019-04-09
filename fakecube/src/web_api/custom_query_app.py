#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'
import json,time,datetime
import sys,re
import urllib,decimal
import web
from lib import *
from query_app import verifyAppToken_v2


sys.path.append('..')
from mms.lib.custom_query import CustomQuery

class CustomQueryApp(action.Action):
    def GET(self):
        return self.POST()


    def POST(self):
        user_data = web.input(sql='',order='2',date='',edate='',index=0,offset=100,total='',appToken=None,appName=None,search=None,customSort=None,check=None)

        #权限验证
        project_name = user_data.get('project','')
        appToken = user_data.get('appToken','')
        appName = user_data.get('appName','')
        '''
        if not verifyAppToken_v2(appToken,appName,project_name):
            return base.retu(1,'permission denied')
        '''

        sql=user_data.sql
        if not sql:
            return base.retu(1,'no sql')

        date=user_data.date
        edate=user_data.edate
        if not edate:
            edate=date

        #排序列
        customSort=user_data.customSort
        if customSort:
            customSort=urllib.unquote(customSort)
            try:
                customSort = json.loads(customSort)
            except Exception as e:
                return base.retu(1, "bad customSort param")

        #搜索
        search=user_data.search
        if search:
            search=json.loads(user_data.search)

        #分页页码
        index=user_data.index
        index = int(index) - 1
        index = index if index > 0 else 0

        offset=user_data.offset

        #是否查询总条数
        total=True if user_data.total  else False

        #校验
        check=user_data.check
        psecolumns=[]
        if check:
            index=0
            offset=10
            pres=json.loads(self.parseSql(sql))
            if int(pres['status'])!=0:
                return base.retu(1,pres['msg'])
            psecolumns=pres['data']

        query=CustomQuery()

        status,ret=query.getResult(sql=sql,date=date,edate=edate,index=index,offset=offset,search=search,customSort=customSort)

        if not status:
            return base.retu('5',ret)

        #总条数
        total_number=0
        if total:
            status,total_res=query.getResult(sql=sql,date=date,edate=edate,total=total,search=search)
            if not status:
                return base.retu('5',ret)
            total_number = total_res[0]['total']
        else:
            #same as query app
            total_number = 10000000

        return base.retu('', '', ret, {'total': total_number,'showMsg':'','relyMsg':'','colums':psecolumns})


    def parseSql(self,sql):
        import re,traceback
        columns={}
        try:
            tmp=sql.split('from')
            if len(tmp)<2:
                return base.retu('1','sql格式不规范')
            sql='{} from'.format(tmp[0])
            sql=sql.strip().lower()
            r=re.compile(r'^select\s+([\w\W]+)\s+from')
            r_dim=re.compile(r'^([-a-zA-Z0-9_]+)$')
            r_metirc=re.compile(r'^([-a-zA-Z0-9_\s()]+)$')
            con=r.findall(sql)
            con_str=con[0]
            con_arr=con_str.split(',')
            for e in con_arr:
                tmp={}
                e_arr=e.split()
                if len(e_arr)==3:
                    if r_metirc.match(e.strip()):
                        columns[str(e_arr[2])]={'type':'metric'}
                    else:
                        return base.retu('1','sql格式不规范')
                else:
                    if r_dim.match(e.strip()):
                        columns[e.strip()]={'type':'dim'}
                    else:
                        return base.retu('1','sql格式不规范')

        except:
            traceback.print_exc()

        return base.retu('0','success',columns)


