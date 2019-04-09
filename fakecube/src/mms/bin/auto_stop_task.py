#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'
import os,sys
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import env as conf
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
import mms_conf as mms_conf
import app_conf as appObj
from email.mime.text import MIMEText

import time,phpserialize,json,re,urllib

dir_path = conf.CONF_PATH#'/Users/MLS/Desktop/workspace/fakecube/app'

def auto_stop_task_list():
    print str(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(time.time())))+'：自动下线任务'
    online_table_tasks=get_all_online_table_task()
    query_7days_tasks=get_7days_query_task()
    all_tasks=get_all_app_tasks()
    all_tasks_sched=get_all_app_tasks_sched()
    check_list=need_check_task_list()
    days7_nosuccess_list=get_7days_nosuccess_task()
    for key,val in all_tasks.items():
        #如果不是线上报表依赖任务并且也不是7天内查询任务,关闭此任务
        if ((key not in online_table_tasks and key not in query_7days_tasks.keys()) or key in days7_nosuccess_list) and key in check_list:
            log_inf='：project:%s run_module:%s'%(val['app_name'].encode('utf-8'),val['run_group'].encode('utf-8'))
            print str(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(time.time())))+log_inf
            reason=1#
            if key in days7_nosuccess_list:
                reason=2
            stop_run_task(val,key,reason)

    for key,val in all_tasks_sched.items():
        #调度类项目7天没有成功记录关闭此任务
        if key in days7_nosuccess_list and key in check_list:
            log_inf='：project:%s run_module:%s'%(val['app_name'].encode('utf-8'),val['run_group'].encode('utf-8'))
            print str(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(time.time())))+log_inf
            reason=2
            stop_run_task(val,key,reason)


#停止运行任务
def stop_run_task(taskInfo=None,key=None,reason=1):
    appConf=appObj.AppConf(taskInfo['app_name'])

    run_group=taskInfo['run_group'].encode('utf-8')

    run_obj=appConf.get_run_list()
    run_instance=run_obj['run_instance']
    run_groups=run_instance['group']

    depend_table=[]

    if all_tables.has_key(key):
        for k,v in all_tables[key].items():
            tmp_a="<a href='http://data.meiliworks.com/report/showreport/%s'>%s</a>"%(v,k)
            depend_table.append(tmp_a.encode('utf-8'))

    monitor_con='您创建或编辑的项目<b>%s（%s）</b>中的任务<b>%s</b>已做下线处理。'%(taskInfo['cn_name'].encode('utf-8'),taskInfo['app_name'].encode('utf-8'),run_group.encode('utf-8'))
    off_reason='该任务没有线上报表依赖并且最近7天未通过开发者中心提供的接口查询该任务产生的数据。'
    if int(reason)==2:
        off_reason='任务在连续7天未成功结束（成功或警告）。'
    recover_way="请进入<a href='http://data.meiliworks.com/project/index'>项目管理页面</a>,在‘搜索’查询框输入项目中文名（<b>%s</b>）或者英文名（<b>%s</b>）进行查询，点击编辑进入项目编辑页在‘执行sql列表栏’里重新选中该任务，最后保存项目。"%(taskInfo['cn_name'].encode('utf-8'),taskInfo['app_name'].encode('utf-8'))
    table_str=str(','.join(depend_table))
    mail_content='''
        监控内容： %s</br>
        触发原因： %s</br>
        恢复方法： %s</br>

    '''%(monitor_con,off_reason,recover_way)

    new_log_run_groups=[]
    old_log_run_groups=[]
    #下线处理
    for g in run_groups:
        old_log_run_groups.append(g['name'])
        if g['name']!=run_group:
            new_log_run_groups.append(g['name'])

    appConf.off_run_task(run_group)

    #保存任务下线日志
    log_obj_mms_conf=mms_conf.MmsConf()
    user_action='/fakecube/offtask/%s'%(taskInfo['app_name'])
    log_params={'old_run':old_log_run_groups,'new_run':new_log_run_groups,'project':taskInfo['app_name']}
    log_obj_mms_conf.save_editor_task_log(taskInfo['creater'],user_action,json.dumps(log_params))



    to_list=['data_alarm']
    if taskInfo['creater']:
        to_list.append(taskInfo['creater'])
    if taskInfo['editor']:
        to_list.append(taskInfo['editor'])
    from mms_email import MmsEmail
    mmsEmail=MmsEmail()
    mail_sub="【监控】data平台项目无效hql下线通知"
    msg=MIMEText(mail_content,"html","utf-8")
    mmsEmail.sendmessage(to_list,mail_sub,msg)
'''
七天内查询报表依赖调度任务
'''
def get_7days_query_task():
    online_tasks_dict={}
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    now=int(time.time())
    days7=int(now-604800)
    pre_7days=time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(days7))
    sql="select id,cdate,token_name,project_name,`group`,metric from t_query_data_log where token_name!='%s' and cdate>='%s'"%('data',pre_7days)

    cur.execute(sql)
    columns = cur.description
    result=[]
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()

    for e in result:
        project=e['project_name']
        metric=e['metric']
        if project and metric:
            metric_list=metric.split(',')
            for m in metric_list:
                tmp=project+'.'+m[0:m.rindex('.')]
                online_tasks_dict[tmp]='on'


    return online_tasks_dict

#查询7天没有（成功，警告）
def get_7days_nosuccess_task():
    days7_nosuccess_list=[]
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    now=int(time.time())
    days7=int(now-604800)
    days1=int(now-86400)
    pre_7days=time.strftime('%Y-%m-%d',time.localtime(days7))
    pre_1days=time.strftime('%Y-%m-%d',time.localtime(days1))
    sql='''
        select t.app_name,t.run_module,t.schedule_level,t.create_num from
        (
        select app_name,run_module,schedule_level,count(distinct stat_date) create_num,sum(if(status in (5,7),1,0)) succ_num from mms_run_log where create_time>'%s 00:00:00' and creater is null and app_name is not null and schedule_level='day' group by app_name,run_module,schedule_level
        )t
        where t.succ_num=0 and create_num>=7
        union
        select t1.app_name,t1.run_module,t1.schedule_level,t1.create_num from
        (
        select app_name,run_module,schedule_level,count(distinct stat_date) create_num,sum(if(status in (5,7),1,0)) succ_num from mms_run_log where create_time>'%s 00:00:00' and creater is null and app_name is not null and schedule_level='hour' group by app_name,run_module,schedule_level
        )t1 where t1.succ_num=0 and create_num>=24
    '''%(pre_7days,pre_7days)

    cur.execute(sql)
    columns = cur.description
    result=[]
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()

    for e in result:
        app_name=e['app_name']
        run_module=e['run_module']
        days7_nosuccess_list.append('%s.%s'%(app_name,run_module))

    return list(set(days7_nosuccess_list))




#获取所有调度类
def get_all_app_tasks_sched():
    all_app_run_tasks={}
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    #如果是调度类项目就不参与
    sql="select * from mms_conf where storetype=4"#storetype=4
    cur.execute(sql)
    columns = cur.description
    result=[]
    now=int(time.time())
    for value in cur.fetchall():
        tmp={}

        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        if  tmp['date_s'] is not None and time.mktime(tmp['date_s'].timetuple()) > now  :
            continue
        if  tmp['date_e'] is not None and time.mktime(tmp['date_e'].timetuple()) < now :
            continue
        result.append(tmp)

    conn.close()

    for e in result:
        app_name=e['appname']
        creater=e['creater']
        editor=e['editor']
        cn_name=e['cn_name']
        id=e['id']
        path=''
        appConf=appObj.AppConf(app_name)
        run_obj=appConf.get_run_list()
        run_instance=run_obj['run_instance']
        run_groups=run_instance['group']
        for g in run_groups:
            run_group=g['name']
            run_group_key=app_name+'.'+run_group
            tmp={'app_name':app_name,'cn_name':cn_name,'path':path,'run_group':run_group,'creater':creater,'editor':editor,'id':id}
            all_app_run_tasks[run_group_key]=tmp

    return all_app_run_tasks



#获取所有项目任务不包含调度类
def get_all_app_tasks():
    all_app_run_tasks={}
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    #如果是调度类项目就不参与
    sql="select * from mms_conf where storetype!=4"#storetype=4
    cur.execute(sql)
    columns = cur.description
    result=[]
    now=int(time.time())
    for value in cur.fetchall():
        tmp={}

        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        if  tmp['date_s'] is not None and time.mktime(tmp['date_s'].timetuple()) > now  :
            continue
        if  tmp['date_e'] is not None and time.mktime(tmp['date_e'].timetuple()) < now :
            continue
        result.append(tmp)

    conn.close()

    for e in result:
        app_name=e['appname']
        creater=e['creater']
        editor=e['editor']
        cn_name=e['cn_name']
        id=e['id']
        path=''
        appConf=appObj.AppConf(app_name)
        run_obj=appConf.get_run_list()
        run_instance=run_obj['run_instance']
        run_groups=run_instance['group']
        for g in run_groups:
            run_group=g['name']
            run_group_key=app_name+'.'+run_group
            tmp={'app_name':app_name,'cn_name':cn_name,'path':path,'run_group':run_group,'creater':creater,'editor':editor,'id':id}
            all_app_run_tasks[run_group_key]=tmp

    return all_app_run_tasks

def get_all_online_table_task():
    online_tasks_dict=[]
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    sql='select id,cn_name,project,`group`,metric,creater,flag,params from t_visual_table where flag=1'
    cur.execute(sql)
    columns = cur.description
    result=[]
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()

    udf=['max','min','sum','avg','count']
    reg_udf='|'.join(udf)
    r=re.compile(r'((%s)\((.*?)(>>.*)?\))'%reg_udf)
    allr=re.compile(r'((.*?)(\+|\-|\*|/|$)(?!>))')
    udc_task_list=[]
    for e in result:
        params=e['params']
        project=e['project']
        metric=e['metric']

        if project and metric:
            metric_list=metric.split(',')
            for m in metric_list:
                tmp=project+'.'+m[0:m.rindex('.')]
                online_tasks_dict.append(tmp)
        try:
            params=phpserialize.loads(params)
            if params.has_key('tablelist') and params['tablelist']:
                params_table_list=params['tablelist']
                for key,params_table in params_table_list.items():
                    if params_table.has_key('sql') and params_table['sql']:
                        print params_table['title']
                        sql=params_table['sql']
                        udf = ['TABLE']
                        reg_udf = '|'.join(udf)
                        r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
                        result = r.findall(sql)
                        for e in result:
                            pro_dims=str(e[2]).split(',')
                            project=pro_dims[0]
                            dims=pro_dims[1:]
                            dims.sort()
                            dim_key=''
                            if len(dims) == 1 and 'all' in dims:
                                dim_key=project
                            else:
                                dim_key='%s,%s'%(project,','.join(dims))
                            dim2tasks=get_app_dim2tasks(project)
                            if dim2tasks.has_key(dim_key):
                                online_tasks_dict+=dim2tasks[dim_key]
                    if params_table.has_key('metric') and params_table['metric']:
                        table_metric_list=params_table['metric'].split(',')
                        for t_m in table_metric_list:
                            t_tmp=project+'.'+t_m[0:t_m.rindex('.')]
                            online_tasks_dict.append(t_tmp)

                    if params_table.has_key('udcconf') and params_table['udcconf']:
                        udcconf=json.loads(urllib.unquote(params_table['udcconf']))
                        if isinstance(udcconf,(list)):
                            for u in udcconf:
                                if u.has_key('expression') and u['expression']:
                                    udc=u['expression']
                                    allresult=allr.findall(udc)
                                    for all in allresult:
                                        res_name=all[1]
                                        content=''
                                        tmp_res=r.findall(res_name)
                                        if tmp_res:
                                            result=tmp_res[0]
                                            if result:
                                                content=result[2]
                                        else:
                                            content=res_name
                                        dim_metric=content.split('->')
                                        if len(dim_metric)==2:
                                            split_metric=dim_metric[1].split('.')
                                            if len(split_metric)==4:
                                                tmp=str(dim_metric[1][0:dim_metric[1].rindex('.')])
                                                udc_task_list.append(tmp)
                                            else:
                                                tmp=project+'.'+str(dim_metric[1][0:dim_metric[1].rindex('.')])
                                                udc_task_list.append(tmp)

            if params.has_key('table') and params['table']:
                params_table=params['table']
                if params_table.has_key('metric') and params_table['metric']:
                    table_metric_list=params_table['metric'].split(',')
                    for t_m in table_metric_list:
                        t_tmp=project+'.'+t_m[0:t_m.rindex('.')]
                        online_tasks_dict.append(t_tmp)

                if params_table.has_key('udcconf') and params_table['udcconf']:
                    udcconf=json.loads(urllib.unquote(params_table['udcconf']))
                    if isinstance(udcconf,(list)):
                        for u in udcconf:
                            if u.has_key('expression') and u['expression']:
                                udc=u['expression']
                                allresult=allr.findall(udc)
                                for all in allresult:
                                    res_name=all[1]
                                    content=''
                                    tmp_res=r.findall(res_name)
                                    if tmp_res:
                                        result=tmp_res[0]
                                        if result:
                                            content=result[2]
                                    else:
                                        content=res_name
                                    dim_metric=content.split('->')
                                    if len(dim_metric)==2:
                                        split_metric=dim_metric[1].split('.')
                                        if len(split_metric)==4:
                                            tmp=str(dim_metric[1][0:dim_metric[1].rindex('.')])
                                            udc_task_list.append(tmp)
                                        else:
                                            tmp=project+'.'+str(dim_metric[1][0:dim_metric[1].rindex('.')])
                                            udc_task_list.append(tmp)
            if params.has_key('chart') and params['chart']:
                params_chart_list=params['chart']
                for p_chart in params_chart_list.values():
                    if p_chart.has_key('metric') and p_chart['metric']:
                        chart_metric_list=p_chart['metric'].split(',')
                        for t_m in chart_metric_list:
                            t_tmp=project+'.'+t_m[0:t_m.rindex('.')]
                            online_tasks_dict.append(t_tmp)

                    if p_chart.has_key('udcconf') and p_chart['udcconf']:
                        udcconf=json.loads(urllib.unquote(p_chart['udcconf']))
                        if isinstance(udcconf,(list)):
                            for u in udcconf:
                                if u.has_key('expression') and u['expression']:
                                    udc=u['expression']
                                    allresult=allr.findall(udc)
                                    for all in allresult:
                                        res_name=all[1]
                                        content=''
                                        tmp_res=r.findall(res_name)
                                        if tmp_res:
                                            result=tmp_res[0]
                                            if result:
                                                content=result[2]
                                        else:
                                            content=res_name
                                        dim_metric=content.split('->')
                                        if len(dim_metric)==2:
                                            split_metric=dim_metric[1].split('.')
                                            if len(split_metric)==4:
                                                tmp=str(dim_metric[1][0:dim_metric[1].rindex('.')])
                                                udc_task_list.append(tmp)
                                            else:
                                                tmp=project+'.'+str(dim_metric[1][0:dim_metric[1].rindex('.')])
                                                udc_task_list.append(tmp)



        except:
            import traceback
            # traceback.print_exc()
    return_list=udc_task_list+online_tasks_dict
    return_list=list(set(return_list))
    return return_list


def get_all_table_task():
    tasks_dict={}
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    sql='select id,cn_name,project,`group`,metric,creater,flag from t_visual_table'
    cur.execute(sql)
    columns = cur.description
    result=[]
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()

    for e in result:
        project=e['project']
        metric=e['metric']
        id=e['id']
        cn_name=e['cn_name']
        flag=e['flag']
        if project and metric:
            metric_list=metric.split(',')
            for m in metric_list:
                tmp=project+'.'+m[0:m.rindex('.')]
                if not tasks_dict.has_key(tmp):
                    tasks_dict[tmp]={}
                tasks_dict[tmp][cn_name]=id
    return tasks_dict

def need_check_task_list():
    tasks_list=[]
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    sql='select count(distinct stat_date) count_num,app_name,run_module from mms_run_log where stat_date>=date_sub(curdate(),interval 3 day) and creater is null group by app_name,run_module'
    cur.execute(sql)
    columns = cur.description
    result=[]
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()

    for e in result:
        app_name=e['app_name']
        run_module=e['run_module']
        count_num=int(e['count_num'])
        if count_num>=3:
            tmp=app_name+'.'+run_module
            tasks_list.append(tmp)
    return tasks_list

#项目维度组合对应任务
def get_app_dim2tasks(project_name=None):
    dim2task={}
    if project_name:
        appConf=appObj.AppConf(project_name)
        temp=appConf.appConf
        result = {}
        for category in temp['project'][0]["categories"]:
            cat_name=category['name']
            for group in category["groups"]:
                group_name=group['name']
                for d_dim in group['dim_sets']:
                    task_name='%s.%s.%s'%(project_name,cat_name,group_name)
                    key_name=''
                    if d_dim['name']=='()':
                        key_name=project_name
                    else:
                        str_dim=d_dim['name'].strip('()').split(',')
                        l_dim=[e.lower().strip() for e in str_dim]
                        l_dim.sort()
                        key_name='%s,%s'%(project_name,','.join(l_dim))
                    if not dim2task.has_key(key_name):
                        dim2task[key_name]=[]
                    dim2task[key_name].append(task_name)

    return dim2task




if __name__=='__main__':
    all_tables=get_all_table_task()
    auto_stop_task_list()
    # print get_7days_nosuccess_task()
