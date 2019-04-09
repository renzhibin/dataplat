#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'
import sys
import json
import time
import re
import copy
import traceback
import mmsEnv as mmsEnv
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
import utils
'''
    项目配置信息
'''
class AppConf():
    '''
        get_hql True 获取所有项目所有配置包括hql，False只返回配置信息
    '''
    def __init__(self,appName):
        mmsMysqlWrite=MmsMysql(mmsMysqlConf.MMS_DB_META)
        mmsMysqlRead=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        self.curWrite=mmsMysqlWrite.get_cur()
        self.connWrite=mmsMysqlWrite.get_conn()
        self.curRead = mmsMysqlRead.get_cur()
        self.connRead = mmsMysqlRead.get_conn()

        self.appName=appName
        self.appConf={}
        self.storetype=2 #1 老mysql 2 新mysql 3 hbase 4 调度类（hql_type=2
        self.appExist=False #app_是否存在
        self.hqlType=1
        self.startDate='0000-00-00 00:00:00'
        self.endDate='0000-00-00 00:00:00'
        self.allAppConf={}#mms_conf中项目所有配置信息
        self.init()

    def __del__(self):
        if self.connRead.open:
            self.connRead.close()

        if self.connWrite.open:
            self.connWrite.close()


    def init(self):

        sql='''
           select * from mms_conf where appname='%s'
        '''%(self.appName)

        self.curRead.execute(sql)
        columns = self.curRead.description
        value=self.curRead.fetchone()
        tmp={}
        if value:
            self.appExist=True
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column

            self.allAppConf.update(tmp)
            self.storetype=tmp['storetype']
            self.startDate=tmp['date_s']
            self.endDate=tmp['date_e']

            if tmp.has_key('conf') and tmp['conf']:
                self.appConf=json.loads(tmp['conf'])




    def get_hql(self,category_name='',group_name=''):
        sql='''
            select * from mms_app_conf where app_name='%s' and is_delete=0
        '''%(self.appName)

        if category_name and group_name:
            sql+=" and category_name='%s' and hql_name='%s' "%(category_name,group_name)

        self.curRead.execute(sql)
        columns = self.curRead.description
        hqls={}
        for value in self.curRead.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column
            hql_name='_'.join((tmp['category_name'],tmp['hql_name']))
            hql_name=hql_name.lower()
            json_params=json.loads(tmp['other_params'])
            tmp_hql=''
            if json_params.has_key('hql'):
                tmp_hql=json_params['hql']
            hqls[hql_name]=tmp_hql
        return hqls
    '''
        项目配置信息
    '''
    def get_app_conf(self):
        pass

    '''
        运行任务列表
    '''
    def get_run_list(self):
        run_list={'run_instance':{'group':[]}}
        run_list.update(self.allAppConf)
        if self.appConf.has_key('run'):
            run_list.update(self.appConf['run'])
        if run_list.has_key('conf') and run_list['conf']:
            run_list.pop('conf')
        return run_list


    def off_run_task(self,task_name):
        run_list=self.get_run_list()
        tmp_run=[]

        for g in run_list['run_instance']['group']:
            if g['name']!=task_name:
                tmp_run.append({'name':g['name']})
        run_list['run_instance']['group']=tmp_run

        self.appConf['run']=run_list
        sql='''
            update mms_conf set conf=%s where appname=%s
        '''
        self.curWrite.execute(sql,(json.dumps(self.appConf,default=utils.defaultencode),self.appName))
        self.connWrite.commit()

    '''
        保存
        @:param list
    '''
    def save_app(self,params):
        sql='''
            INSERT INTO  mms_conf(create_time,appname,creater,date_s,date_e,cn_name,`explain`,`storetype`,conf) values(%s,%s,%s,%s,%s,%s,%s,%s,%s)
            ON DUPLICATE KEY UPDATE cn_name=%s,`explain`=%s,date_s=%s,date_e=%s,conf=%s
        '''
        self.curWrite.execute(sql,params)
        return self.connWrite.commit()

    '''
        保存 log
        @:param list
    '''
    def save_app_log(self,params):
        sql_log = '''
            INSERT INTO  mms_conf_log (`create_time`, `appname`, `creater`, `date_s`, `date_e`, `cn_name`, `explain`, `storetype`, `conf`) values (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        '''
        self.curWrite.execute(sql_log, params)
        return self.connWrite.commit()

    '''
        更新
    '''
    def update_app(self,params):
        sql='''
            update mms_conf set date_s=%s,date_e=%s,cn_name=%s,`explain`=%s,`storetype`=%s,editor=%s,authuser=%s,conf=%s where id=%s
        '''
        self.curWrite.execute(sql,params)
        return self.connWrite.commit()


    def save_hql(self,category_name,hql_name,project_conf,cate_conf,group,creater,editor = ''):
        if not self.appExist:
            self.appConf={"project":[{"name":"","cn_name":"","hql_type":0,"storetype":0,"categories":[]}]}

        if project_conf.has_key('hql_type') and int(project_conf['hql_type'])==2:
            project_conf['storetype']=4
            self.storetype=4
        elif project_conf.has_key('storetype') and project_conf['storetype']!=-1:
            self.storetype=project_conf['storetype']

        empty_check_list = {1:['hql', 'metrics', 'dim_sets', 'tables', 'dimensions', 'cn_name'],2:[]}
        custom_cdate=0
        if group.has_key('custom_cdate'):
            custom_cdate=int(group['custom_cdate'])
        hql_type=1
        if group.has_key('hql_type'):
            hql_type=int(group['hql_type'])
        elif self.appConf['project'][0].has_key('hql_type'):
            hql_type=self.appConf['project'][0]['hql_type']

        group['hql_type']=hql_type

        for tmp in empty_check_list[hql_type]:
            if not group.has_key(tmp) or not group[tmp]:
                if custom_cdate==1 and tmp=='dimensions':
                    group[tmp]=[]
                else:
                    return False,"%s is empty "%(tmp)
        #判断是否包含偏移量如果有偏移量验证是否符合规范
        if group.has_key('schedule_interval_offset'):
            re_offset=re.compile(r'^([-]?\d+)(day|minute|hour)')
            offset_match=re_offset.match(group['schedule_interval_offset'])
            if not offset_match:
                return False,'偏移量格式填写不正确。'

        if custom_cdate == 1:
            if group.has_key('custom_start') and group['custom_start']:
                custom_start = group['custom_start']
            if group.has_key('custom_end') and group['custom_end']:
                custom_end = group['custom_end']
            func_name = []
            para_list = []
            for udf in [custom_start,custom_end]:
                result = self.getUdfNameAndPara(udf)
                if len(result) != 0:
                    name,para = result[0][1], result[0][2]
                    func_name.append(str(name))
                    para_list.append(int(para))
                else:
                    return False,'自定义函数 DATE(0)/MONTH(0) 格式不正确'
            #udf函数要求一致
            if func_name[0] != func_name[1]:
                return False,'自定义函数DATE/MONTH要求开始与结束函数类型一致'
            #验证日期大小
            if para_list[0] > para_list[1]:
                return False,'自定义数据删除起始时间晚于结束时间，请重新设置'

        #保存hive表中英文名
        if group.has_key('tables') and group['tables']:
            tables_list=group['tables']
            #检查table依赖时间是否填写正确
            for t in tables_list:
                if t.has_key('time_depend') and t['time_depend']:
                    time_depend=t['time_depend']
                    par=t['par']
                    t_name=t['name']
                    udf = ['DATE|HOUR|MONTH']
                    reg_udf = '|'.join(udf)
                    r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
                    result = r.findall(time_depend)
                    if len(result)!=2:
                        return False,t_name+'检测依赖时间格式错误，正确格式：（$DATE(0)/$DATE(0)或$HOUR(0)/$HOUR(0)）'
                    if str(result[0][1])!=str(result[1][1]):
                        return False,t_name+'检测依赖时间格式错误，正确格式：（$DATE(0)/$DATE(0)或$HOUR(0)/$HOUR(0)）'
                    if int(result[0][2])!=0 or int(result[1][2])!=0:
                        if int(result[0][2])>int(result[1][2]):
                            return False,t_name+'起始时间不可大于终止时间'

        self.appConf["project"][0].update(project_conf)

        #mms_conf mms_app_conf 信息同步
        new_hql=True
        new_cate=True
        tip_category_index=0
        tmp_group=copy.copy(group)
        tmp_group.pop('hql')
        for cate_index in range(0, len(self.appConf['project'][0]['categories'])):
            category = self.appConf['project'][0]['categories'][cate_index]
            if category['name']==category_name:
                new_cate=False
                tip_category_index=cate_index
                self.appConf['project'][0]['categories'][cate_index].update(cate_conf)
                for i in range(0, len(category['groups'])):
                    groups_content = category['groups'][i]
                    if groups_content['name']==hql_name:
                        new_hql=False
                        self.appConf['project'][0]['categories'][cate_index]['groups'][i].update(tmp_group)

        if new_cate:
            app_count = self.get_app_hql_count(self.appName)
            if int(app_count) >= mmsEnv.MMS_CONF_MAX_APP_CONF:
                return False, '项目创建任务数不能大于' + str(mmsEnv.MMS_CONF_MAX_APP_CONF)
            new_cate_dict={"name":'',"cn_name":"","explain":"","groups":[]}
            new_cate_dict.update(cate_conf)
            new_cate_dict['groups'].append(tmp_group)
            self.appConf['project'][0]['categories'].append(new_cate_dict)
        elif new_hql:
            app_count = self.get_app_hql_count(self.appName)
            if int(app_count) >= mmsEnv.MMS_CONF_MAX_APP_CONF:
                return False, '项目创建任务数不能大于' + str(mmsEnv.MMS_CONF_MAX_APP_CONF)
            self.appConf['project'][0]['categories'][tip_category_index]['groups'].append(tmp_group)
        try:
            app_sql='''
                INSERT INTO mms_conf(appname,cn_name,`explain`,`storetype`,conf,create_time,creater) VALUES (%s,%s,%s,%s,%s,%s,%s)
                ON DUPLICATE KEY UPDATE cn_name=%s,`explain`=%s,conf=%s
            '''
            create_time=time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))
            app_params=[self.appName,project_conf['cn_name'],project_conf['explain'],self.storetype,json.dumps(self.appConf),create_time,creater,project_conf['cn_name'],project_conf['explain'],json.dumps(self.appConf)]

            group_sql='''
                INSERT INTO mms_app_conf(app_name,category_name,hql_name,dimensions,metrics,other_params, is_schedule, data_table_name, creater, editor) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
                ON DUPLICATE KEY UPDATE dimensions=%s,metrics=%s,other_params=%s, is_schedule=%s, data_table_name=%s, editor=%s
            '''
            group_params=self.get_hql_params(self.appName,category_name,hql_name,group, hql_type)
            group_params.insert(8, creater)
            group_params.insert(9, editor)
            group_params.append(editor)

            group_sql_log = '''
                INSERT INTO mms_app_conf_log (app_name, category_name, hql_name, dimensions, metrics, other_params, is_schedule, data_table_name, creater, editor) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            '''

            self.curWrite.execute(app_sql,app_params)
            self.curWrite.execute(group_sql,group_params)
            del group_params[10:16]
            self.curWrite.execute(group_sql_log, group_params)
            self.connWrite.commit()
        except:
           traceback.print_exc()
           return False,'mysql error'

        return True,"success"

    def get_app_hql_count(self, app_name):
        select_sql = '''
                    select format((length(conf) - length(REPLACE (conf, 'schedule_interval_offset', '')))/24,0) as app_count from mms_conf where appname='%s'
                ''' % (app_name)
        self.curRead.execute(select_sql)
        columns = self.curRead.description
        tmp = dict()
        value = self.curRead.fetchone()

        if value:
            for (index, column) in enumerate(value):
                tmp[columns[index][0]] = column
        if len(tmp) == 0 :
            return 0
        return tmp['app_count']


    #获取hql配置信息
    def get_hql_params(self,app_name,category_name,hql_name,group, hql_type):

        params_list=[app_name,category_name,hql_name]
        is_schedule = '0'
        table_name = ''

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
                if not json_dim:
                    json_dim={}
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
               if not json_met:
                    json_met={}
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

        if hql_type == 2:
            is_schedule='1'
            #store the data table name for the insert hql
            if group.has_key('hql') and group['hql']:
                hql = group['hql'].strip().lower()
                match_table=re.compile(r'insert\s+(overwrite)?\s+table\s+([\s\S]*?)\s+(partition\s*\(([\s\S]*?)\))?')

                match_res=match_table.findall(hql)
                if not match_res:
                    table_name=''
                else:
                    match_res=match_res[0]
                    table_name=match_res[1]

            else:
                table_name=''

        else:
            is_schedule='0'
            table_name=''

        params_list.append(is_schedule)
        params_list.append(table_name)

        params_list.append(json.dumps(dims))
        params_list.append(json.dumps(met))
        params_list.append(json.dumps(group))
        params_list.append(is_schedule)
        params_list.append(table_name)

        return params_list

    def getUdfNameAndPara(self, udf_s):
        udf = ['DATE|MONTH']
        reg_udf = '|'.join(udf)
        r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
        result = r.findall(udf_s)

        return result


    def getScheduleIntervalForHql(self, category_name, group_name):
        temp=self.appConf
        exp_hql = category_name+'.'+group_name
        schedule=''
        for cat in temp['project'][0]['categories']:
            for group in cat['groups']:
                hql=cat['name']+'.'+group['name']
                if hql==exp_hql:
                    #schedule_interval 调度时间间隔 5/30/60 分钟|0 7_1 &_2 ~7_7 天|30_1 30-2 月
                    if group.has_key('schedule_interval') and group['schedule_interval']:
                        schedule=group['schedule_interval']
        return schedule