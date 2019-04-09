#!/usr/bin/env python2.7
# coding=utf-8

import web, re, traceback
import json
import time
import os
import mms.lib.mms_conf as mms_conf
import mms.lib.task_stop_alarm as task_stop_alarm
import mms.lib.app_conf as appObj

from lib import *

dir_path = const.dir_path


class SaveProject(action.Action):

    def check_enname(self, name,checkLength=True,checklen=20):

        if checkLength and (self.storetype and self.storetype not in [1,4] and len(name)>checklen):
            return  False
        pattern = re.compile(r'^[_a-zA-Z0-9]+$')
        match = pattern.match(name)

        if match:
            return True
        return False

    def getUdfNameAndPara(self, udf_s):
        udf = ['DATE|MONTH']
        reg_udf = '|'.join(udf)
        r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
        result = r.findall(udf_s)

        return result


    def one_time_run_task(self,project,run_group,submitter):
        obj_mms_conf=mms_conf.MmsConf()
        task_record=obj_mms_conf.get_task_run_record(project,run_group)
        stat_date=str(time.strftime("%Y-%m-%d",time.localtime(time.time())))
        submitter=submitter
        #没有记录说明没有运行
        if len(task_record)<=0:
            #app_name,stat_date,run_module,submitter
            obj_mms_conf.save_task_running(project,stat_date,run_group,submitter)

    def POST(self):
        try:
            user_data = web.input(project='', id='', stat_date='')
            self.storetype=0
            from pprint import pprint
            from collections import OrderedDict

            project = user_data.project
            id = user_data.id

            if not project:
                return base.retu(1, 'parament project is empty')
            yaml_content = json.loads(project)
            try:
                project_name = yaml_content['project'][0]['name']
            except:
                print traceback.format_exc()
                return base.retu(2, 'project is wrong')

            if project_name == 'template_metric' or project_name == '':
                return base.retu(3, 'Please fill out the project name')
            if not self.check_enname(project_name,False):
                return base.retu(4, '项目英文名必须为数字、字符串或者下划线')

            appConf=appObj.AppConf(project_name)
            object_mms_conf = mms_conf.MmsConf()

            if id and id>0:
                self.storetype=appConf.storetype
            else:
                project_name_white_list=['web_api','all_app','system_log','hql_tools']
                if project_name in project_name_white_list:
                    return base.retu(10, '项目名被占用,请重新填写项目名')
                '''
                if appConf.appExist:
                    return base.retu(10, '项目名被占用,请重新填写项目名')
                '''
            project_cn_name = yaml_content['project'][0]['cn_name']
            project_explain = yaml_content['project'][0]['explain']

            post_storetype=2
            if yaml_content['project'][0].has_key('hql_type') and int(yaml_content['project'][0]['hql_type'])==2:
                post_storetype=4
                self.storetype=post_storetype
            elif yaml_content['project'][0].has_key('storetype') and  yaml_content['project'][0]['storetype']!=-1:
                post_storetype=int(yaml_content['project'][0]['storetype'])


            if not yaml_content.has_key('run'):
                return base.retu(5, 'run is empty')
            if not yaml_content['project'][0].has_key('categories'):
                return base.retu(5, 'must have categories')
            para_run_content = yaml_content['run']

            if not para_run_content.has_key('run_instance'):
                return base.retu(5, '执行sql列表中至少选中1个sql任务')

            empty_check_list = {1:['hql', 'metrics', 'dim_sets', 'tables', 'dimensions', 'cn_name'],2:[]}

            run_list = []
            one_time_task_list=[]
            new_all_tasks=[]
            for categories_index in range(0, len(yaml_content['project'][0]['categories'])):
                categories = yaml_content['project'][0]['categories'][categories_index]
                if not categories.has_key('groups'):
                    return base.retu(6, '分类下面没有任何sql', categories)
                if not self.check_enname(categories['name']):
                    return base.retu(4, '类目名' + categories[
                        'name'] + '必须小于20个字符,格式必须为数字、字符串或者下划线')
                for i in range(0, len(categories['groups'])):
                    custom_cdate = 0
                    groups_content = categories['groups'][i]
                    sql_name = categories['name'] + '_' + groups_content['name']
                    cate_group_name=categories['name'] + '.' + groups_content['name']
                    new_all_tasks.append(cate_group_name)

                    if groups_content.has_key('custom_cdate'):
                        custom_cdate = int(groups_content['custom_cdate'])

                    if not self.check_enname(groups_content['name']):
                        return base.retu(4, 'hql名' + groups_content[
                            'name'] + '必须小于20个字符,格式必须为数字、字符串或者下划线')
                    run_list.append({'name': sql_name})

                    if groups_content.has_key('hql_type'):
                        hql_type=int(groups_content['hql_type'])
                    elif yaml_content['project'][0].has_key('hql_type') :
                        hql_type=int(yaml_content['project'][0]['hql_type'])
                    else:
                        hql_type=1
                    groups_content['hql_type']=hql_type
                    for tmp in empty_check_list[hql_type]:
                        if not groups_content.has_key(tmp) or not groups_content[tmp]:
                            if custom_cdate == 1 and tmp == 'dimensions':
                                groups_content[tmp]=[]
                            else:
                                return base.retu(5, sql_name + " " + tmp + ' is empty')

                    #判断是否包含偏移量如果有偏移量验证是否符合规范
                    if groups_content.has_key('schedule_interval_offset'):
                        re_offset=re.compile(r'^([-]?\d+)(day|minute|hour)')
                        offset_match=re_offset.match(groups_content['schedule_interval_offset'])
                        if not offset_match:
                            return base.retu('5','偏移量格式填写不正确。')

                    if custom_cdate == 1:
                        if groups_content.has_key('custom_start') and groups_content['custom_start']:
                            custom_start = groups_content['custom_start']
                        if groups_content.has_key('custom_end') and groups_content['custom_end']:
                            custom_end = groups_content['custom_end']

                        func_name = []
                        para_list = []

                        for udf in [custom_start,custom_end]:
                            result = self.getUdfNameAndPara(udf)
                            if len(result) != 0:
                                name,para = result[0][1], result[0][2]
                                func_name.append(str(name))
                                para_list.append(int(para))
                            else:
                                return base.retu('5', '自定义函数 DATE(0)/MONTH(0) 格式不正确')

                        #udf函数要求一致
                        if func_name[0] != func_name[1]:
                            return base.retu('5', '自定义函数DATE/MONTH要求开始与结束函数类型一致')


                        #验证日期大小
                        if para_list[0] > para_list[1]:
                            return base.retu('5', '自定义数据删除起始时间晚于结束时间，请重新设置')

                    #保存hive表中英文名
                    if groups_content.has_key('tables') and groups_content['tables']:
                        tables_list=groups_content['tables']
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
                                    return base.retu(5,t_name+'检测依赖时间格式错误，正确格式：（$DATE(0)/$DATE(0)或$HOUR(0)/$HOUR(0)）')
                                if str(result[0][1])!=str(result[1][1]):
                                    return base.retu(5,t_name+'检测依赖时间格式错误，正确格式：（$DATE(0)/$DATE(0)或$HOUR(0)/$HOUR(0)）')
                                if int(result[0][2])!=0 or int(result[1][2])!=0:
                                    if int(result[0][2])>int(result[1][2]):
                                        return base.retu(5,t_name+'起始时间不可大于终止时间')
                        object_mms_conf.save_table_names(tables=tables_list)
                    #判断该任务是否是立刻执行
                    if groups_content.has_key('schedule_interval') and groups_content['schedule_interval']:
                        if groups_content['schedule_interval']=='0_1':
                            one_time_task_list.append(cate_group_name)
                            self.one_time_run_task(project_name,cate_group_name,para_run_content['creater'])

                    del groups_content['hql']
                    yaml_content['project'][0]['categories'][categories_index]['groups'][i] = groups_content
            old_all_tasks=[]
            #获取项目中所有任务
            if id and id>0:
                old_yaml_content=appConf.appConf
                for old_index in range(0, len(old_yaml_content['project'][0]['categories'])):
                    old_category=old_yaml_content['project'][0]['categories'][old_index]
                    old_cate_name=old_category['name']
                    for i in range(0, len(old_category['groups'])):
                        old_groups_content = old_category['groups'][i]
                        old_group_name=old_groups_content['name']
                        old_all_tasks.append('%s.%s'%(old_cate_name,old_group_name))

            run_content = {'run_instance': {'conf': '', 'group': []}}
            run_content['run_instance']['conf'] = project_name
            #删除立刻执行任务
            tmp_run_groups=[]
            for e in para_run_content['run_instance']['group']:
                tmp_run_task=e['name']
                if tmp_run_task not in one_time_task_list:
                    tmp_run_groups.append(e)

            para_run_content['run_instance']['group']=tmp_run_groups
            run_content['run_instance']['group'] = para_run_content['run_instance']['group']

            #检查运行任务是否停止，停止报警
            editor_run_list=[e['name'] for e in run_content['run_instance']['group']]
            user_action='/fakecube/saveproject/%s'%(project_name)
            log_params={'old_run':[],'new_run':editor_run_list,'project':project_name,"all":old_all_tasks}
            tmp_date_s = para_run_content['date_s']
            tmp_date_e = para_run_content['date_e']
            tmp_now=int(time.time())

            if tmp_date_s:
                tmp_date_s=int(time.mktime(time.strptime(tmp_date_s,'%Y-%m-%d')))
            if tmp_date_e:
                tmp_date_e=int(time.mktime(time.strptime(tmp_date_e,'%Y-%m-%d')))

            project_is_stop=True

            if id and id>0:
                run_con=appConf.get_run_list()
                run_tasks_list=[e['name'] for e in run_con['run_instance']['group']]
                if (tmp_date_s and tmp_date_s>tmp_now) or (tmp_date_e and tmp_date_e<tmp_now):
                    editor_run_list=[]
                    if appConf.appExist:
                       if  (appConf.startDate is not None and int(time.mktime(appConf.startDate.timetuple())) > tmp_now) or (appConf.endDate is not None and int(time.mktime(appConf.endDate.timetuple())) < tmp_now)  :
                            project_is_stop=False
                #保存任务修改日志
                log_params={'old_run':run_tasks_list,'new_run':editor_run_list,'project':project_name,'all':old_all_tasks}

                func=lambda x: False if x in editor_run_list else True
                diff_list=filter(func,run_tasks_list)
                if len(diff_list)>0 and project_is_stop:
                    if int(hql_type)==2:
                        #如果调度类，消除tag
                        for tmp_e in diff_list:
                            delete_tag="nohup python /home/inf/fakecube/src/mms/bin/delete_table_tag.py -p %s -g %s>>/home/inf/logs/off_table_tag.log 2>&1 &"%(project_name,tmp_e)
                            #os.system(delete_tag)
                    creater_tmp = para_run_content['creater']
                    try:
                        task_stop_alarm.run(id,','.join(diff_list),creater_tmp)
                    except:
                        import traceback
                        traceback.print_stack()
            else:
                new_obj_log_mms_conf=mms_conf.MmsConf()
                if (tmp_date_s and tmp_date_s>tmp_now) or (tmp_date_e and tmp_date_e<tmp_now):
                    new_log_params={'old_run':[],'new_run':editor_run_list,'project':project_name,'all':new_all_tasks}
                    new_obj_log_mms_conf.save_editor_task_log(para_run_content['creater'],user_action,json.dumps(new_log_params))
                else:
                    new_log_params={'old_run':new_all_tasks,'new_run':editor_run_list,'project':project_name,'all':new_all_tasks}
                    new_obj_log_mms_conf.save_editor_task_log(para_run_content['creater'],user_action,json.dumps(new_log_params))

            #新增项目保存新增任务日志
            if project_is_stop:
                obj_log_mms_conf=mms_conf.MmsConf()
                obj_log_mms_conf.save_editor_task_log(para_run_content['creater'],user_action,json.dumps(log_params))

            creater = para_run_content['creater']
            date_s = para_run_content['date_s']
            date_e = para_run_content['date_e']
            app_json_conf=json.dumps(yaml_content)
            app_param_list=[]
            try:
                if date_s == '':
                    date_s = '00-00-00'
                if date_e == '':
                    date_e = '00-00-00'
                if id and id > 0:
                    #项目英文名不允许修改
                    authuser=''
                    if yaml_content['project'][0].has_key('authuser'):
                        authuser=yaml_content['project'][0]['authuser']
                    app_param_list=[date_s,date_e,project_cn_name,project_explain,post_storetype,creater,authuser,app_json_conf,id]
                    appConf.update_app(app_param_list)

                    create_time = time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))
                    app_param_list_log = [create_time,project_name,creater,date_s,date_e,project_cn_name,project_explain,post_storetype,app_json_conf]
                    appConf.save_app_log(app_param_list_log)
                else:
                    create_time=time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))
                    app_param_list=[create_time,project_name,creater,date_s,date_e,project_cn_name,project_explain,post_storetype,app_json_conf,project_cn_name,project_explain,date_s,date_e,app_json_conf]
                    appConf.save_app(app_param_list)

                    app_param_list_log = [create_time,project_name,creater,date_s,date_e,project_cn_name,project_explain,post_storetype,app_json_conf]
                    appConf.save_app_log(app_param_list_log)
            except:
                import traceback

                print traceback.print_exc()
                return base.retu(10, 'mysql error')


            return base.retu('', '', user_data)
        except:
            import traceback
            print traceback.print_exc()
            return base.retu(10,'save project error')




    def GET(self):
        self.POST()