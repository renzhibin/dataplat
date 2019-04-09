#!/usr/bin/env python
#coding=utf-8
import re
import copy
import time
import sys

import env as conf
from utils import checkScheduleInterval
import app_conf as appObj
from mms_mysql import MmsMysql
import mms_mysql_conf as mmsMysqlConf
from mms_table_tag import remove_mms_table_tag

HADOOP_BIN = '/hadoop/hadoop/bin/hadoop'
TAG_HOME = '/user/data_ready_tag/'

class Node(object):

     def __init__(self):
         self.task = None
         self.child = []

     def __repr__(self):
        return "TASK[app:%s, category:%s, hql:%s]" % (self.task.app_name, self.task.category, self.task.hql)

class Task(object):

    def __init__(self, app_name='', category='', hql='', data_table=''):
        self.app_name = app_name
        self.category = category
        self.hql = hql
        self.data_table = data_table
        self.depend_tables=[]
        self.is_schedule = False
        self.schedule_level = ''
        self.stat_time = ''
        self.priority = ''
        self.editor= ''
        self.sec_date=''

    def __eq__(self, other):
        key1 = self.app_name+"_"+self.category+"_"+self.hql+ "_" + self.schedule_level + "_" + self.stat_time
        key2 = other.app_name+"_"+other.category+"_"+other.hql + "_" + other.schedule_level + "_" + other.stat_time
        return key1 == key2

    def __hash__(self):
        hashed_key = self.app_name+"_"+self.category+"_"+self.hql + "_" + self.schedule_level + "_" + self.stat_time
        return hash(hashed_key)

    def __str__(self):
        return "TASK[app:%s, category:%s, hql:%s, schedule_level:%s, stat_time:%s]" % (self.app_name, self.category, self.hql, self.schedule_level, self.stat_time)

    def __repr__(self):
        return "TASK[app:%s, category:%s, hql:%s, schedule_level:%s, stat_time:%s]" % (self.app_name, self.category, self.hql, self.schedule_level, self.stat_time)

def setTaskProjectConf(task):
    app_name = task.app_name
    category = task.category
    hql = task.hql
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur(MmsMysql.DICTCURSOR_MODE)
    sql = '''select id,date_s,date_e,date_n,creater,appname,priority,storetype,if(editor is not null,editor,creater) editor from mms_conf where appname='%s'
    ''' % (app_name)
    cur.execute(sql)
    ret = cur.fetchone()
    if ret is not None:
        task.priority = ret['priority']
        task.editor = ret['editor']

    sql = '''select is_schedule, data_table_name from mms_app_conf where app_name = '%s' and category_name='%s' and hql_name='%s'
    ''' % (app_name, category, hql)
    cur.execute(sql)
    ret = cur.fetchone()
    if ret is not None:
        is_schedule = bool(int(ret['is_schedule']))
        task.is_schedule = is_schedule
        task.data_table = ret['data_table_name']

    conn.close()

def getDependTableByHql(app_name, category_name, hql_name):
    hql2table=[]
    appConf=appObj.AppConf(app_name)
    run_name = category_name+'.'+hql_name
    try:
        temp=appConf.appConf
        for cat in temp['project'][0]['categories']:
            for group in cat['groups']:
                hql=cat['name']+'.'+group['name']
                if hql == run_name:
                    if group.has_key('tables'):
                        for table in group['tables']:
                            hql2table.append(table['name'])
        return hql2table
    except Exception,ex:
        print ex
        print 'get hql2table failed'

def getHql2Table(app_name):
    hql2table={}
    appConf=appObj.AppConf(app_name)
    temp=appConf.appConf
    for cat in temp['project'][0]['categories']:
        for group in cat['groups']:
            hql_type=1
            if group.has_key('hql_type') and group['hql_type']:
                hql_type=group['hql_type']
            hql=cat['name']+'.'+group['name']
            hql2table[hql]=[]
            if group.has_key('tables'):
                for table in group['tables']:
                    hql2table[hql].append(table['name'])
    return hql2table

def handleTasksByTable(table, stat_date, flag, apps_list):
    ret_list = []
    dependencyTasks= getTaskByTable(table)
    if len(dependencyTasks) !=0:
        ret_list = handleDependencyTasks(dependencyTasks, stat_date, flag, apps_list)
    return ret_list

def handleDependencyTasks(tasks, stat_date, flag, allhql2tables, schedule_map, data_tables):
    ret_list=[]
    if len(tasks) !=0:
        final_result = set()
        up_result = set()
        down_result =set()
        for task in tasks:
            temp_result=set()
            root = Node()
            root.task = task
            taskTree = generateTaskTree(root, allhql2tables)
            if taskTree is not None:
                temp_result=traversalTask(taskTree, temp_result)


            up_result = up_result.union(temp_result)
        final_result = final_result.union(up_result)
        #是否重跑依赖调度任务的任务
        if flag:
            for task in up_result:
                temp2_result = set()
                if task.is_schedule and task.data_table not in data_tables:
                    root = Node()
                    root.task = task
                    depend_task_tree = generateDependencyTree(root, schedule_map)
                    traversalTask(depend_task_tree, temp2_result)
                    down_result = down_result.union(temp2_result)
                    data_tables.append(task.data_table)
            final_result=final_result.union(down_result)
        if len(final_result) !=0:
            for task in final_result:
                depend_list = generateRunConfig(task, stat_date, data_tables)
                ret_list.extend(depend_list)

    #ret_list=list(set(ret_list))
    return ret_list

def getDataTableByTaskName(task):
    table = ''
    app_name = task.app_name
    category = task.category
    hql = task.hql
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur(MmsMysql.DICTCURSOR_MODE)
    sql = '''select data_table_name from mms_app_conf where app_name='%s' and category_name='%s' and hql_name='%s'
    ''' % (app_name, category, hql)
    cur.execute(sql)
    ret = cur.fetchone()
    if ret is not None:
        table = ret['data_table_name']

    conn.close()
    return table


def getScheduleTaskTables():
    tables=[]
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur(MmsMysql.DICTCURSOR_MODE)
    tablessql='''
        select data_table_name from mms_app_conf where data_table_name!=''
    '''
    cur.execute(tablessql)
    ret = cur.fetchall()
    for i in ret:
        tables.append(i['data_table_name'])

    conn.close()
    tables = list(set(tables))
    return tables

def getTaskByTable(table,allhql2tables):
    task = []
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur(MmsMysql.DICTCURSOR_MODE)
    table_sql = "select app_name, category_name,hql_name, is_schedule from mms_app_conf where data_table_name='%s'" % table
    cur.execute(table_sql)
    ret = cur.fetchall()
    if ret is not None and len(ret) !=0:
        for app in ret:
            app_name= app['app_name']
            category_name = app['category_name']
            hql_name = app['hql_name']
            is_schedule = bool(int(app['is_schedule']))
            key_name = app_name+"."+category_name+"."+hql_name
            if not allhql2tables.has_key(key_name):
                continue
            depend_tables = allhql2tables[key_name]
            if table in depend_tables:
                #去掉自依赖的表
                depend_tables.remove(table)
            temp = Task(app_name, category_name, hql_name, table)
            temp.data_table = table
            temp.depend_tables = depend_tables
            temp.is_schedule = is_schedule
            setTaskProjectConf(temp)
            task.append(temp)

    conn.close()
    return task

def generateTaskTree(root, allhql2tables):
    tables = root.task.depend_tables
    for i in range(len(tables)):
        table = tables[i]
        tasks = getTaskByTable(table, allhql2tables)
        if len(tasks) != 0:
            for t in tasks:
                node = Node()
                node.task = t
                root.child.append(node)
                generateTaskTree(node, allhql2tables)

    return root

def generateDependencyTree(root, schedule_map):
    task = root.task
    data_table = getDataTableByTaskName(task)
    #获取依赖此调度任务数据表的任务
    data_tasks = schedule_map[data_table]
    # 去掉自依赖的任务
    if task in data_tasks:
        data_tasks.remove(task)
    if len(data_tasks) !=0:
        for t in data_tasks:
            node = Node()
            node.task = t
            root.child.append(node)
            if t.is_schedule:
                generateDependencyTree(node, schedule_map)
    return root

def getTasksDependsOnDataTable(table, allhql2tables):
    tasks=[]
    for k,v in allhql2tables.iteritems():
        if table in v:
            li = k.split('.')
            appname, category, hql = li[0], li[1], li[2]
            task = Task(appname, category, hql)
            task.is_schedule = getTaskScheduleValue(task)
            tasks.append(task)
    return tasks

def getAllRunHql2Table(app_list):
    allhql2tables= {}
    for app in app_list:
        run_list = []
        appname = app['appname']
        appConf=appObj.AppConf(appname)
        temp_run_list = appConf.get_run_list()
        for tmp_run in temp_run_list['run_instance']['group']:
            run_list.append(tmp_run['name'])

        temp=appConf.appConf
        for cat in temp['project'][0]['categories']:
            for group in cat['groups']:
                hql = cat['name']+'.'+group['name']
                if hql in run_list:
                    key = appname+"."+hql
                    allhql2tables[key]=[]
                    if group.has_key('tables'):
                        for table in group['tables']:
                            allhql2tables[key].append(table['name'])
    return allhql2tables

def getTaskScheduleValue(task):
    app_name = task.app_name
    category = task.category
    hql = task.hql
    is_schedule = False
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur(MmsMysql.DICTCURSOR_MODE)
    sql = '''select is_schedule, data_table_name from mms_app_conf where app_name = '%s' and category_name='%s' and hql_name='%s'
    ''' % (app_name, category, hql)
    cur.execute(sql)
    ret = cur.fetchone()
    if ret is not None:
        is_schedule = bool(int(ret['is_schedule']))
    conn.close()
    return is_schedule


def traversalTask(taskTree, result):
    root = taskTree
    task = root.task
    result.add(task)

    for child in root.child:
        traversalTask(child, result)

    return result


def generateRunConfig(task, stat_date, depend_table):
    app_name = task.app_name
    category = task.category
    hql_name = task.hql
    run_name = category+"."+hql_name
    table = task.data_table
    is_schedule = task.is_schedule
    run_task_exist = False
    times2task=dict()
    offset2group=dict()
    interval2group=dict()
    cg_list = []
    appConf = appObj.AppConf(app_name)

    temp=appConf.appConf
    for cat in temp['project'][0]['categories']:
        for group in cat['groups']:
            temp_hql = cat['name']+"."+group['name']
            if run_name == temp_hql:
                run_task_exist = True
                hql_type=1
                if group.has_key('hql_type') and group['hql_type']:
                    hql_type=group['hql_type']

                #schedule_interval 调度时间间隔 5/30/60 分钟|0 7_1 &_2 ~7_7 天|30_1 30-2 月
                if group.has_key('schedule_interval') and group['schedule_interval']:
                    interval2group[run_name]=group['schedule_interval']

                if group.has_key('run_times') and group['run_times']:
                    times2task[run_name]=group['run_times']
                if group.has_key('schedule_interval_offset') and group['schedule_interval_offset']:
                    re_offset=re.compile(r'^([-]?\d+)(day|minute|hour)')
                    offset_res=re_offset.findall(group['schedule_interval_offset'])
                    if len(offset_res)>0:
                        offset_val=int(offset_res[0][0])
                        offset_type=str(offset_res[0][1].encode('utf-8')).strip()
                        offset=0
                        if 'day'==offset_type:
                            offset=int(offset_val*86400)
                        elif 'hour'==offset_type:
                            offset=int(offset_val*3600)
                        elif 'minute'==offset_type:
                            offset=int(offset_val*60)
                        offset2group[run_name]=offset
                    else:
                        offset2group[run_name]=None
                else:
                    offset2group[run_name]=None

    if run_task_exist:
        status=False
        ret=()
        one_time_tip=False
        groupoffset=offset2group[run_name]
        if interval2group.has_key(run_name) and interval2group[run_name]:
            interval=interval2group[run_name]
            #interval为0_0,0_1为只执行一次
            one_time_interval=['0_0','0_1']
            if interval in one_time_interval:
                one_time_tip=True
                interval='0'
            status,ret=checkScheduleInterval(interval,stat_date,groupoffset,depend_table)
        else:
            status,ret=checkScheduleInterval('0',stat_date,groupoffset,depend_table)
        if status:
            #如果只执行一次的任务，删除
            if one_time_tip==True:
                appConf.off_run_task(run_name)
            schedule_level,stat_date_ret=ret
            if 'minute'==schedule_level or 'hour'==schedule_level:
                import time
                stat_time=time.mktime(time.strptime(stat_date_ret,"%Y-%m-%d %H:%M"))
                stat_hour=int(time.strftime('%H',time.localtime(stat_time)))
                stat_minute=int(time.strftime('%M',time.localtime(stat_time)))
                #00:00时运行分钟天数据(前一天)
                if int(conf.CRONTAB_TASK_HOUR)==stat_hour and 0==stat_minute and int(hql_type)!=2:
                    stat_day_minute=time.strftime('%Y-%m-%d',time.localtime(int(stat_time-86400)))
                    #cg_list.append((run_name,'day',stat_day_minute))
                    task.schedule_level = 'day'
                    task.stat_time = stat_day_minute
                    cg_list.append(task)

                #分钟任务
                if times2task.has_key(run_name) and times2task[run_name] and int(times2task[run_name])!=0:
                    import time
                    times=int(times2task[run_name])+1
                    stat_date_time=int(time.mktime(time.strptime(stat_date_ret,'%Y-%m-%d')))
                    for i in range(0,times):
                        tmp_stat_date=time.strftime('%Y-%m-%d %H:%M',time.localtime(stat_date_time-i*3600))
                        #cg_list.append((run_name,schedule_level,tmp_stat_date))
                        task_copy = copy.deepcopy(task)
                        task_copy.schedule_level = schedule_level
                        task_copy.stat_time = tmp_stat_date
                        cg_list.append(task_copy)
                else:
                    #cg_list.append((run_name,schedule_level,stat_date_ret))
                    task.schedule_level = schedule_level
                    task.stat_time = stat_date_ret
                    cg_list.append(task)

            elif 'day'==schedule_level:

                if times2task.has_key(run_name) and times2task[run_name] and int(times2task[run_name])!=0:
                    import time
                    times=int(times2task[run_name])+1
                    stat_date_time=int(time.mktime(time.strptime(stat_date_ret,'%Y-%m-%d')))
                    for i in range(0,times):
                        tmp_stat_date=time.strftime('%Y-%m-%d',time.localtime(stat_date_time-i*86400))
                        #cg_list.append((run_name,'day',tmp_stat_date))
                        task_copy = copy.deepcopy(task)
                        task_copy.schedule_level = 'day'
                        task_copy.stat_time = tmp_stat_date
                        cg_list.append(task_copy)
                else:
                    #cg_list.append((run_name,'day',stat_date_ret))
                    task.schedule_level = 'day'
                    task.stat_time = stat_date_ret
                    cg_list.append(task)

    return cg_list


def getHqlList(app_name,data_tables, special_table=None,stat_date=None,modules=None,flag=False,allhql2tables={}, schedule_map={}):
    if not stat_date:
        stat_date=time.strftime('%Y-%m-%d',int(int(time.time())-86400))
    cg_list=[]
    interval2group=dict()
    run_modules=[]
    times2task=dict()
    offset2group=dict()
    dependencyTasks = []

    appConf=appObj.AppConf(app_name)


    if not appConf.appExist:
        print 'project :%s not exist' %app_name
        return cg_list
    try:
        temp=appConf.appConf
        for cat in temp['project'][0]['categories']:
            for group in cat['groups']:
                hql_type=1
                if group.has_key('hql_type') and group['hql_type']:
                    hql_type=group['hql_type']
                hql=cat['name']+'.'+group['name']

                #schedule_interval 调度时间间隔 5/30/60 分钟|0 7_1 &_2 ~7_7 天|30_1 30-2 月
                if group.has_key('schedule_interval') and group['schedule_interval']:
                    interval2group[hql]=group['schedule_interval']

                if group.has_key('run_times') and group['run_times']:
                    times2task[hql]=group['run_times']
                if group.has_key('schedule_interval_offset') and group['schedule_interval_offset']:
                    re_offset=re.compile(r'^([-]?\d+)(day|minute|hour)')
                    offset_res=re_offset.findall(group['schedule_interval_offset'])
                    if len(offset_res)>0:
                        offset_val=int(offset_res[0][0])
                        offset_type=str(offset_res[0][1].encode('utf-8')).strip()
                        offset=0
                        if 'day'==offset_type:
                            offset=int(offset_val*86400)
                        elif 'hour'==offset_type:
                            offset=int(offset_val*3600)
                        elif 'minute'==offset_type:
                            offset=int(offset_val*60)
                        offset2group[hql]=offset
                    else:
                        offset2group[hql]=None
                else:
                    offset2group[hql]=None

        if modules:
            run_modules=modules
        else:
            temp = appConf.get_run_list()
            for tmp_run in temp['run_instance']['group']:
                run_name = tmp_run['name']
                if len(special_table)==0:
                    run_modules.append(run_name)
                else:
                    special_tables = set(special_table)
                    key = app_name+'.'+ run_name
                    task_hql_tables = set(allhql2tables[key])
                    inter_tables = special_tables.intersection(task_hql_tables)
                    if len(inter_tables)!=0:
                        li = run_name.split('.')
                        cat,hql = li[0],li[1]
                        task = Task(app_name, cat, hql)
                        task.depend_tables = list(task_hql_tables)
                        setTaskProjectConf(task)
                        dependencyTasks.append(task)

        for run_name in run_modules:
            status=False
            ret=()
            one_time_tip=False
            if offset2group.has_key(run_name):
                li = run_name.split('.')
                category = li[0]
                hql_name = li[1]
                task = Task(app_name,category, hql_name)
                setTaskProjectConf(task)
                groupoffset=offset2group[run_name]
                if interval2group.has_key(run_name) and interval2group[run_name]:
                    interval=interval2group[run_name]
                    #interval为0_0,0_1为只执行一次
                    one_time_interval=['0_0','0_1']
                    if interval in one_time_interval:
                        one_time_tip=True
                        interval='0'
                    status,ret=checkScheduleInterval(interval,stat_date,groupoffset,special_table)
                else:
                    status,ret=checkScheduleInterval('0',stat_date,groupoffset,special_table)
                if status:
                    #如果只执行一次的任务，删除
                    if one_time_tip==True:
                        appConf.off_run_task(run_name)
                    schedule_level,stat_date_ret=ret
                    if 'minute'==schedule_level or 'hour'==schedule_level:
                        import time
                        stat_time=time.mktime(time.strptime(stat_date_ret,"%Y-%m-%d %H:%M"))
                        stat_hour=int(time.strftime('%H',time.localtime(stat_time)))
                        stat_minute=int(time.strftime('%M',time.localtime(stat_time)))
                        #00:00时运行分钟天数据(前一天)
                        if int(conf.CRONTAB_TASK_HOUR)==stat_hour and 0==stat_minute and int(hql_type)!=2:
                            stat_day_minute=time.strftime('%Y-%m-%d',time.localtime(int(stat_time-86400)))
                            #cg_list.append((run_name,'day',stat_day_minute))
                            task.schedule_level = 'day'
                            task.stat_time = stat_day_minute
                            cg_list.append(task)
                        #分钟任务
                        if times2task.has_key(run_name) and times2task[run_name] and int(times2task[run_name])!=0:
                            import time
                            times=int(times2task[run_name])+1
                            stat_date_time=int(time.mktime(time.strptime(stat_date_ret,'%Y-%m-%d %H:%M')))
                            sec_date=time.strftime('%Y-%m-%d %H:%M',time.localtime(stat_date_time))
                            for i in range(0,times):
                                tmp_stat_date=time.strftime('%Y-%m-%d %H:%M',time.localtime(stat_date_time-i*3600))
                                #cg_list.append((run_name,schedule_level,tmp_stat_date))
                                task_copy = copy.deepcopy(task)
                                task_copy.schedule_level = schedule_level
                                task_copy.stat_time = tmp_stat_date
                                if sec_date != tmp_stat_date:
                                    task_copy.sec_date = sec_date
                                cg_list.append(task_copy)
                        else:
                            #cg_list.append((run_name,schedule_level,stat_date_ret))
                            task.schedule_level = schedule_level
                            task.stat_time = stat_date_ret
                            cg_list.append(task)

                    elif 'day'==schedule_level:

                        if times2task.has_key(run_name) and times2task[run_name] and int(times2task[run_name])!=0:
                            import time
                            times=int(times2task[run_name])+1
                            stat_date_time=int(time.mktime(time.strptime(stat_date_ret,'%Y-%m-%d')))
                            sec_date = time.strftime('%Y-%m-%d',time.localtime(stat_date_time))
                            for i in range(0,times):
                                tmp_stat_date=time.strftime('%Y-%m-%d',time.localtime(stat_date_time-i*86400))
                                #cg_list.append((run_name,'day',tmp_stat_date))
                                task_copy = copy.deepcopy(task)
                                task_copy.schedule_level = 'day'
                                task_copy.stat_time = tmp_stat_date
                                if sec_date!=tmp_stat_date:
                                    task_copy.sec_date = sec_date
                                cg_list.append(task_copy)
                        else:
                            task.schedule_level = 'day'
                            task.stat_time = stat_date_ret
                            cg_list.append(task)

        if len(dependencyTasks) !=0:
            depend_list = handleDependencyTasks(dependencyTasks, stat_date, flag, allhql2tables, schedule_map, special_table)
            cg_list.extend(depend_list)
            cg_list = list(set(cg_list))
    except:
        import traceback
        traceback.print_exc()
        print app_name
        print temp

    return cg_list

def removeTag(task):
    #调度任务重跑删掉对应tag
    if task.is_schedule and task.data_table !='':
        table = task.data_table
        schedule_level = task.schedule_level
        stat_time = task.stat_time
        stat_hour=0
        if 'hour'==schedule_level:
            time_struct=time.strptime(stat_time,"%Y-%m-%d %H:%M")
            stat_hour=str(int(time.strftime("%H",time_struct)))
            stat_date=str(time.strftime("%Y-%m-%d",time_struct))
        elif 'day' == schedule_level:
            stat_date= stat_time
        remove_mms_table_tag(table, stat_date, stat_hour, schedule_level)