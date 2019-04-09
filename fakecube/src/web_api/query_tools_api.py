#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import json
import time
import re
import glob

import web

from lib import *
import mms.conf.mms_mysql_conf as mmsMysqlConf
from mms.lib.mms_mysql import MmsMysql
from mms.lib.query_tools_run import check_query_tools_hql

from mms.lib.query_tools_run import repalce_params
import  mms.conf.env  as conf


class RunQueryToolTask(action.Action):
    def GET(self):
        return self.POST()

    def POST(self):
        param_data=web.input(id='',replace_params='',cols_params='',email_users='',task_creater='',hql='',cn_name='',starttime=None,endtime=None)
        tools_id=param_data.id
        replace_params=param_data.replace_params
        cols_params=json.loads(param_data.cols_params)
        # tool_creater=param_data.tool_creater
        task_creater=param_data.task_creater
        email_users=param_data.email_users
        hql=param_data.hql
        query_tool_name=param_data.cn_name
        starttime=param_data.starttime
        endtime=param_data.endtime
        if not hql.strip():
            return base.retu('1','hql参数为空。')
        #参数验证
        #replace_params以逗号分割，不能超过1000个
        replace_params_list=replace_params.split(',')
        if len(replace_params_list)>1000:
            return base.retu('1','替换参数不可超过1000个。')

        stat_date=time.strftime("%Y-%m-%d",time.localtime(time.time()))


        #获取工具配置参数
        task_id=self.run_task(hql,cols_params,task_creater,email_users,stat_date,replace_params,query_tool_name,starttime,endtime)


        tmp_res_log='run_%s_%s_%s.log'%(stat_date,'hql_tools',str(task_id))
        log_file='%s%s'%('%s%s'%(conf.LOG_PATH,'hql_tools/'),tmp_res_log)
        with open(log_file,'a') as f:
            f.write("离线查询任务已提交，等待调度中...</br>数据生成后会发送至数据接收人邮箱。</br>如需获取当前任务进度请刷新当前页面。")
        res_obj={}
        res_obj['serial']=task_id
        res_obj['app_name']='hql_tools'
        res_obj['stat_date']=stat_date
        res_obj['module_name']='hql_tools'
        return base.retu('0','success',res_obj)



    def run_task(self,hql,cols_params,creater,email_users,stat_date,replace_params,query_tool_name,starttime,endtime):

        params=dict()
        params['hql']=hql
        params['cols_params']=cols_params
        params['email_users']=email_users
        params['replace_params']=replace_params
        params['task_creater']=creater
        params['query_tool_name']=query_tool_name
        params['starttime']=starttime
        params['endtime']=endtime
        params=json.dumps(params)

        insert_id=0
        mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()

        cur.execute("insert into mms_run_log (app_name,stat_date,status,creater,job_type,params,run_module,conf_name)values(%s,%s,%s,%s,%s,%s,%s,%s)",('hql_tools',stat_date,'2',creater,'hql',params,'hql_tools','inf01'))
        insert_id=conn.insert_id()
        conn.commit()
        conn.close()

        return insert_id



class GetQueryToolsProfile(action.Action):
    def GET(self):
        param_data=web.input(hql=None)
        hql=param_data.hql
        if not hql:
            return base.retu('1','hql不可为空。')
        cols_list=[]
        hql=str(hql.strip().lower())
        #判断包含$vars变量
        if '$vars' not in hql:
            return base.retu('1','hql需包含$vars变量。')

        r_p={'replace_params':"'demo'",'dt':'2015-07-21','start':'2015-07-21','end':'2015-07-21'}
        hql=repalce_params(hql,r_p)
        status,content=check_query_tools_hql(hql)

        if status!=0:
            return base.retu('1',content)
        hql=re.sub(r'\s+[-]+(.+)\s+',' ',hql)
        # col_r=re.compile(r'^select\s+([-a-zA-Z0-9,_\s\']+)\s+from')
        col_r=re.compile(r'^select\s+([\s\S]*?)\s+from')
        col_res=col_r.findall(hql.strip().lower())
        if len(col_res)>0:
            cols=col_res[0]
            cols=cols.split(',')
            for col in cols:
                tmp_c_l=col.strip().split()

                len_c_l=len(tmp_c_l)
                '''
                if len_c_l==3:
                    cols_list.append(tmp_c_l[2])
                elif len_c_l==2:
                    cols_list.append(tmp_c_l[1])
                elif len_c_l==1:
                    cols_list.append(tmp_c_l[0])
                else:
                    cols_list.append()
                    return base.retu('1','hql格式错误。')
                '''
                cols_list.append(tmp_c_l[len_c_l-1])
            return base.retu('0','success',cols_list)

        return base.retu('1','hql格式错误。')


    def POST(self):
        self.GET()


class QueryToolsDownloadCsv(action.Action):
    def GET(self):
        param_data=web.input(tools_id=None)
        tools_id=param_data.tools_id
        # if not tools_id:
        #     return base.retu('1','参数错误。')

        BUF_SIZE = 262144
        hql_download_dir=conf.TMP_DOWNLOAD_PATH+'hql_tools/'
        data_csv_path='%s%s'%(hql_download_dir,'%s.%s'%(str(tools_id),'csv'))

        filep=glob.glob(data_csv_path)

        if len(filep) == 0:
            return 'not found'

        tmp=''
        with open(filep[0]) as logf:
            for line in logf:
               tmp+=line+'<br>'

        return tmp

        '''
        try:
            file_name='query_result.csv'
            f=open(data_csv_path,'rb')
            web.header('Content-Type','application/octet-stream')
            web.header('Content-disposition', 'attachment; filename=%s.dat' % file_name)
            while True:
                c=f.read(BUF_SIZE)
                if c:
                    yield c
                else:
                    break

        except Exception,e:
            yield '下载失败。'
        finally:
            if f:
                f.close()
        '''
    def POST(self):
        self.GET()


if __name__=='__main__':
    pass