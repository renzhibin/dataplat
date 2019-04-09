#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
import json
import re
import urllib
import phpserialize
import sys
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
import app_conf as appObj


def getHqlBySecondMenuId(menuId):
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()
    sql='''
        select table_id from t_visual_menu where id=%s
    '''%(menuId)

    cur.execute(sql)
    columns = cur.description
    result=[]
    for value in cur.fetchall():
        tmp={}
        for (index,column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)

    hqlList=[]
    if result:
        tableId=result[0]["table_id"]
        if tableId and not tableId.startswith('http'):
            tableIds=json.loads(result[0]["table_id"])
            if tableIds:
                for table in tableIds:
                    if table.has_key('id'):
                        id=table['id']
                        tmp=getHqlByTableId(id)
                        hqlList+=tmp

    conn.close()
    return list(set(hqlList))

def getHqlByTableId(id):
    '''
        根据报表Id获取获取报表依赖任务
    '''
    hqlList=[]
    if id:
        mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()
        sql='select id,cn_name,project,`group`,metric,creater,flag,params from t_visual_table where id=%s'%(id)

        cur.execute(sql)
        columns = cur.description
        result=[]
        for value in cur.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)
        hqlList=getTableRelyTask(result)

        conn.close()

    return hqlList

def getMmsAppConfIdByAppName(app_name, run_module):
    mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
    conn = mmsMysql.get_conn()
    cur = mmsMysql.get_cur()
    sql = "select id from mms_app_conf where app_name='%s' and concat(category_name, '.', hql_name)= '%s'" % (app_name, run_module)
    cur.execute(sql)
    columns = cur.description
    result = []
    for value in cur.fetchall():
        tmp = {}
        for (index, column) in enumerate(value):
            tmp[columns[index][0]] = column
        result.append(tmp)
    conn.close()

    return result

def getTableRelyTask(result):
    online_tasks_dict=[]
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
    return_list=udc_task_list+online_tasks_dict
    return_list=list(set(return_list))

    return return_list

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
