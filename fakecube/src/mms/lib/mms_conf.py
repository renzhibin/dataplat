#!/usr/bin/env python2.7
#coding=utf-8
import time
import sys
import json

import env as conf
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql

dir_path = conf.CONF_PATH
class MmsConf(object):
    def __init__(self):
        mmsMysqlWrite=MmsMysql(mmsMysqlConf.MMS_DB_META)
        mmsMysqlRead=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        self.curWrite=mmsMysqlWrite.get_cur()
        self.connWrite=mmsMysqlWrite.get_conn()
        self.curRead = mmsMysqlRead.get_cur()
        self.connRead = mmsMysqlRead.get_conn()

    def close_connection(self):
        if self.connRead.open:
            self.connRead.close()

        if self.connWrite.open:
            self.connWrite.close()

    def __del__(self):
        if self.connRead.open:
            self.connRead.close()

        if self.connWrite.open:
            self.connWrite.close()

    def  select(self,appname='',id=None):
         sql='''select id,date_s,date_e,date_n,creater,appname,create_time,priority,`explain`,cn_name,storetype,editor,authtype,authuser,mysql_weight, store_db from mms_conf'''
         if appname:
            sql+= ''' where appname='%s'
         '''%(appname);
         if  id:
             sql+= ''' where id='%s'
         '''%(id);
         sql+=' order by create_time desc'
         self.curRead.execute(sql)
         result=[]
         columns = self.curRead.description
         tmp=dict()
         for value in self.curRead.fetchall():
                tmp={}
                for (index,column) in enumerate(value):
                    tmp[columns[index][0]] = column
                result.append(tmp)
         return  result
    def  update(self,params,id):
        # UPDATE `mms_conf` SET `date_n` = '2014' WHERE `id` = '875';
        sql_prefix='''UPDATE `mms_conf` SET'''
        sql_suffix='WHERE `id` = \'%s\''%(id)
        sql=''
        for k,v in params.items():
            sql+=''' `%s` ="%s" ,'''%(k,v)
        sql=sql_prefix+sql[:-1]+sql_suffix
        print sql
        self.curWrite.execute(sql)
        return self.connWrite.commit()
    def  insert(self,appname,creater,date_s,date_e,cn_name='',explain='',storetype=2):
        create_time=time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))
        sql='''
             insert  mms_conf(appname,creater,date_s,date_e,create_time,cn_name,`explain`,`storetype`) values('%s','%s','%s','%s','%s','%s','%s','%s')
        '''%(appname,creater,date_s,date_e,create_time,cn_name,explain,storetype)
        print sql
        self.curWrite.execute(sql)
        return self.connWrite.commit()

    '''
        保存表中文英文名
    '''
    def save_table_names(self,tables):
        if not tables:
            return

        sql="insert into mms_table_name (en_name,cn_name) values (%s,%s) " \
            " ON DUPLICATE KEY UPDATE cn_name=values(cn_name) "
        result=[]

        for table in tables:
            r_tmp=[]
            table_en_name=table['name']
            r_tmp.append(table_en_name)
            if table.has_key('cn_name') and table['cn_name']:
                table_cn_name=str(table['cn_name']).decode('utf-8')
                r_tmp.append(table_cn_name)
                result.append(tuple(r_tmp))
        if len(result)>0:
            self.curWrite.executemany(sql,result)
            self.connWrite.commit()

    '''
        查询所有映射表名
    '''

    def get_all_table_name(self):

        sql="select en_name en_name,cn_name cn_name from mms_table_name"

        self.curRead.execute(sql)
        tmp=dict()
        for value in self.curRead.fetchall():
            tmp[str(value[0]).strip().lower()]=value[1]

        return tmp

    '''
        获取报表配置信息
    '''
    def getTableInfoById(self,table_id):
        sql="select * from t_visual_table where id='%s'"%(table_id)
        self.curRead.execute(sql)
        result = []

        columns = self.curRead.description
        tmp = dict()
        for value in self.curRead.fetchall():
            tmp = {}
            for (index, column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)
        return result


    '''
        查看该该任务是否有运行记录
    '''
    def  get_task_run_record(self,project_name,run_module):
         sql="select * from mms_run_log where app_name='%s' and run_module='%s'"%(project_name,run_module)
         self.curRead.execute(sql)
         result=[]
         columns = self.curRead.description
         tmp=dict()
         for value in self.curRead.fetchall():
                tmp={}
                for (index,column) in enumerate(value):
                    tmp[columns[index][0]] = column
                result.append(tmp)
         return  result

    '''
        保存任务到列队，立即执行
    '''
    def save_task_running(self,app_name,stat_date,run_module,submitter):
        # (app_name,stat_date,run_module,schedule_level,creater,submitter,task_queue)
        task_queue='inf'
        schedule_level='day'
        status=conf.WAITING
        template=(app_name,stat_date,run_module,schedule_level,submitter,task_queue,status)

        sql="insert into %s (app_name,stat_date,run_module,schedule_level,submitter,task_queue,status)" %conf.QUEUE_TABLE
        sql+="values (%s,%s,%s,%s,%s,%s,%s)"
        self.curWrite.execute(sql,template)
        self.connWrite.commit()


    def get_project_success_task(self,sql):
         self.curRead.execute(sql)
         result=[]
         no_success={}
         success={}
         columns = self.curRead.description
         #task,app_name,id,status

         for value in self.curRead.fetchall():
            status=int(value[3])
            id=value[2]
            if status==5 or status==7:
                success[value[0]]={"id":id}
            else:
                no_success[value[0]]={"id":id}
         for k,v in success.items():
            v_id=v['id']
            if no_success.has_key(k):
                no_v=no_success[k]
                if int(v_id)>int(no_v['id']):
                    result.append(k)
            else:
                result.append(k)
         return  result

    '''
        保存修改项目新增减少运行任务日志
    '''
    def save_editor_task_log(self,user_name,user_action,params):
        cdate=str(time.strftime("%Y-%m-%d %H:%M",time.localtime(time.time())))
        template=(cdate,user_name,user_action,params)
        sql="insert into %s (cdate,user_name,user_action,param)"%('t_visual_behavior_log')
        sql+="values (%s,%s,%s,%s)"
        self.curWrite.execute(sql,template)
        self.connWrite.commit()

    '''
        获取项目中维度配置信息
    '''
    def get_app_dimensions_params(self,app_name):

        sql='''
            select dimensions from mms_app_conf where app_name='%s'
        '''%(app_name)

        self.curRead.execute(sql)
        result=[]
        columns = self.curRead.description
        tmp=dict()
        for value in self.curRead.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)
        '''
            {dimensions:[
                {
                    name:'',
                    cn_name:'',
                    explain:'',
                    type:''
                }
                ]
            }
        '''
        dims_params={}
        for e in result:
            e=json.loads(e['dimensions'])
            if isinstance(e,dict) and e.has_key('dimensions') and isinstance(e['dimensions'],list):
                dims=e['dimensions']
                for dim in dims:
                    dims_params[dim['name'].lower()]=dim
        return dims_params

    def get_app_all_metrics_params(self,app_name):
        sql='''
            select metrics,app_name,category_name,hql_name from mms_app_conf where app_name='%s'
        '''%(app_name)
        self.curRead.execute(sql)
        result=[]
        columns = self.curRead.description
        tmp=dict()
        for value in self.curRead.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)
        metric_params={}
        for e in result:
            category_name=e['category_name']
            hql_name=e['hql_name']
            e=json.loads(e['metrics'])
            if isinstance(e,dict) and e.has_key('metrics') and isinstance(e['metrics'],list):
                metrics=e['metrics']
                for metric in metrics:
                    metric_params['%s_%s_%s'%(category_name.lower(),hql_name.lower(),metric['name'].lower())]=metric
        return  metric_params

    '''
        获取项目hql 指标配置信息
    '''
    def get_app_metrics_params(self,app_name,category_name,hql_name):
        sql='''
            select metrics from mms_app_conf where app_name='%s' and category_name='%s' and hql_name='%s'
        '''%(app_name,category_name,hql_name)
        self.curRead.execute(sql)
        result=[]
        columns = self.curRead.description
        tmp=dict()
        for value in self.curRead.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)
        metric_params={}
        for e in result:
            e=json.loads(e['metrics'])
            if isinstance(e,dict) and e.has_key('metrics') and isinstance(e['metrics'],list):
                metrics=e['metrics']
                for metric in metrics:
                    metric_params['%s_%s_%s'%(category_name.lower(),hql_name.lower(),metric['name'].lower())]=metric

        return  metric_params

    '''
        保存或更新任务信息
    '''
    def save_update_hql_inf(self,app_name,category_name,hql_name,group):
        sql='''
            INSERT INTO mms_app_conf(app_name,category_name,hql_name,dimensions,metrics,other_params) VALUES (%s,%s,%s,%s,%s,%s)
            ON DUPLICATE KEY UPDATE dimensions=%s,metrics=%s,other_params=%s
        '''
        params_list=[app_name,category_name,hql_name]


        select_sql='''
            select * from mms_app_conf where app_name='%s' and category_name='%s' and hql_name='%s'
        '''%(app_name,category_name,hql_name)
        self.curRead.execute(select_sql)
        columns = self.curRead.description
        tmp=dict()
        value=self.curRead.fetchone()

        if value:
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column


        dims={}
        met={}
        if group.has_key('dimensions') and group['dimensions']:
            tmp_dim_dict=dict()
            if value and tmp.has_key('dimensions') and tmp['dimensions']:
                json_dim=json.loads(tmp['dimensions'])
                if json_dim.has_key('dimensions') and json_dim['dimensions']:
                    json_dim=json_dim['dimensions']
                    for tmp_e in json_dim:
                        e_name=tmp_e['name']
                        tmp_dim_dict[e_name]=tmp_e
            for group_e in group['dimensions']:
                e_name=group_e['name']
                tmp_dim_dict[e_name]=group_e
            dims['dimensions']=tmp_dim_dict.values()
        if group.has_key('metrics') and group['metrics']:
           tmp_met_dict=dict()
           if value and tmp.has_key('metrics') and tmp['metrics']:
               json_met=json.loads(tmp['metrics'])
               if json_met.has_key('metrics') and json_met['metrics']:
                   json_met=json_met['metrics']
                   for tmp_e in json_met:
                       e_name=tmp_e['name']
                       tmp_met_dict[e_name]=tmp_e
           for group_e in group['metrics']:
               e_name=group_e['name']
               tmp_met_dict[e_name]=group_e

           met['metrics']=tmp_met_dict.values()
        params_list.append(json.dumps(dims))
        params_list.append(json.dumps(met))
        params_list.append(json.dumps(group))

        params_list.append(json.dumps(dims))
        params_list.append(json.dumps(met))
        params_list.append(json.dumps(group))

        self.curWrite.execute(sql,params_list)
        self.connWrite.commit()

if '__main__' == __name__:
    #remove
    pass
