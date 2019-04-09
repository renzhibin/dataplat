# coding=utf-8
import json
import time
import datetime
import sys
import re
import urllib
import decimal

import web

from lib import *

sys.path.append('..')
from mms.lib.udc_query import getResult as getUdcResult
import mms.lib.app_token_conf as tokenConf
from query_connect import TunnelQuery
from mms.lib.query_data_log import save_query_data_log
import mms.conf.env as conf
from mms.lib.mms_conf import MmsConf
from mms.lib.utils import get_project_group_inf
import calendar

def parse(group):
    kv_group = group.strip().split(",")
    dim_list = []
    for i in kv_group:
        j = i.split("=")
        dim_list.append(j[0].strip())
    return dim_list

def verifyAppToken_v2(appToken,appName,module):
    ipWhiteList=["172.16.2.232","172.16.0.40","127.0.0.1"]
    clientIp = web.ctx.env.get('HTTP_X_REAL_IP',web.ctx.ip)
    if clientIp in appconf.ipWhiteList:
        return True

    if not appToken or not appName:
        return False
    obj_token_conf=tokenConf.AppTokenConf()

    app_list=obj_token_conf.select(app_name=appName,token_val=appToken)
    if len(app_list)>0:
        app=app_list[0];
        p_list=str(app["project_name"]).split(",")
        if "all" in p_list:
            return True
        if module in p_list:
            return True
    return False

def verifyAppToken(appToken,appName,module):
    clientIp = web.ctx.env.get('HTTP_X_REAL_IP',web.ctx.ip)
    if clientIp in appconf.ipWhiteList:
        return True

    if not appToken or not appName:
        return False

    tmp=appconf.appConf.get(appName,None)

    if tmp:
        serToken = tmp.get('token')
        if serToken == appToken:
            projectList = tmp.get('module')
            if projectList == 'all' or module in projectList:
                return True
    return False



class QueryApp(action.Action):
    def POST(self):
        return self.GET()
    def GET(self):
        user_data = web.input(order='2', group='', ordermetric='', edate='', index=0, offset=3000, udc='', filter='',
                              total='', arg=None,addcolumn=None,appToken=None,appName=None,search=None,query_mysql_type='slave',date_type='day',customSort=None,converge=None,table_id='')
        project_name = user_data.project
        appToken = user_data.appToken
        appName = user_data.appName
        '''
        #if not verifyAppToken_v2(appToken,appName,project_name):
            #return base.retu(1,'permission denied')
        '''
        #查询mysql主库(master)／从库(slave)
        query_mysql_type=user_data.query_mysql_type
        date = user_data.date
        group = user_data.group
        group = group if (group != '()' and group != 'all') else ''
        metric = user_data.metric
        edate = user_data.edate  # end date
        arg = user_data.arg
        date_type=user_data.date_type
        table_id=user_data.table_id
        #保存查询日志
        save_query_data_log(appName,project_name,group,metric)

        order = user_data.order  # default random   1 asc 2 desc
        order = order if ((int)(order) in [1, 2])  else 0

        dim_list = parse(group)

        metric_list = metric.replace('.', '_').split(',')

        ordermetric = user_data.ordermetric

        #query mms_conf,get mysql_weight
        mmsconf_ins = MmsConf()
        app_conf = mmsconf_ins.select(project_name)
        mysql_weight = str(app_conf[0]['mysql_weight'])

        #查询小时范围201507270202
        start_hour=None
        end_hour=None

        #指标默认排序特殊处理
        ordermetric_arr_tmp=str(ordermetric).split(',')
        if len(ordermetric_arr_tmp)>1:
            ordermetric = ordermetric_arr_tmp


        if ordermetric == 'date':
            ordermetric = 'cdate'



        index = int(user_data.index) - 1
        index = index if index > 0 else 0

        offset = user_data.offset
        udc = user_data.udc
        udc_list = parse(udc)
        filter_str = user_data.get('filter', '')
        custom_sort=user_data.customSort
        if custom_sort:
            tmp_custom_sort=urllib.unquote(custom_sort)
            try:
                custom_sort = json.loads(tmp_custom_sort)
                # 特殊处理core_index,zone_id 排序
                if(project_name=='core_index' and group=='zone_id'):
                    iter_custom_sort=custom_sort[:]
                    date_index=''
                    zone_id_index=''
                    for i,v in enumerate(iter_custom_sort):
                        if(v['key']=='date'):
                            date_index=i
                        elif(v['key']=='zone_id'):
                            zone_id_index=i
                    if date_index!='' and zone_id_index!='':
                        custom_sort[date_index],custom_sort[zone_id_index]=iter_custom_sort[zone_id_index],iter_custom_sort[date_index]


            except Exception as e:
                return base.retu(1, "bad customSort param")
        converge=user_data.converge
        if converge:
            mysql_weight = '2'
            tmp_converge=urllib.unquote(converge)
            try:
                converge = json.loads(tmp_converge)
            except Exception as e:
                return base.retu(1, "bad tmp_converge param")

        search=user_data.search
        if search:
            search=json.loads(user_data.search)
        total = True if user_data.total  else False

        if filter_str:
            con_str = urllib.unquote(filter_str)
            try:
                filter_str = json.loads(con_str)
            except Exception as e:
                return base.retu(1, "bad filter param")

        orderudc = False
        query_index = index
        query_order = order

        #如果date_type为month代表查询月数据
        if date_type=='month':
            date=date+'-01'
            if not edate:
                edate=date
            else:
                tmp_month_edate=datetime.datetime.strptime(edate,'%Y-%m')
                tmp_no,month_days=calendar.monthrange(tmp_month_edate.year,tmp_month_edate.month)
                edate='%s-%s'%(edate,month_days)
        #如果date_type为hour代表查询小时数据
        tmp_date=None
        tmp_edate=None
        if date_type=='hour':
            tmp_date=date
            date_time=time.localtime(time.mktime(time.strptime(date,"%Y-%m-%d %H:%M")))
            date=time.strftime('%Y-%m-%d',date_time)
            start_hour=time.strftime('%Y%m%d%H',date_time)
            if not edate:
                edate=date
                end_hour=start_hour
            else:
                tmp_edate=edate
                edate_time=time.localtime(time.mktime(time.strptime(edate,"%Y-%m-%d %H:%M")))
                edate=time.strftime('%Y-%m-%d',edate_time)
                end_hour=time.strftime('%Y%m%d%H',edate_time)

        #user_access_count项目特殊逻辑
        white_project_list=conf.WHITE_PROJECT_LIST.split(',')
        if project_name in white_project_list:
            date='2015-07-16'
            edate='2015-07-16'

        if not edate:
            edate=date

        #查询任务是否运行完成
        no_success_dict={}
        run_module_alias={}
        all_time_task_dict={}
        try:
            if date_type=='hour':
                no_success_dict,run_module_alias,all_time_task_dict=get_data_rely_task_status(tmp_date,tmp_edate,date_type,project_name,metric,user_data.addcolumn)
            else:
                no_success_dict,run_module_alias,all_time_task_dict=get_data_rely_task_status(date,edate,date_type,project_name,metric,user_data.addcolumn)
        except:
            import traceback
            traceback.print_exc()
        status,db_query,table_list,storetype = TunnelQuery().get_reconnect(project_name,group,date,edate,query_mysql_type, mysql_weight)
        if status is False:
            showMsg=get_return_show_msg(all_time_task_dict,project_name,date_type=date_type)
            replyMsg=get_return_reply_msg(no_success_dict,project_name)
            return base.retu('','',{},{'showMsg':showMsg,'relyMsg':replyMsg})
            # return base.retu(5, '项目'+project_name+'维度组合:'+group+' 下的数据还未生成')
        #排序规则和storetype有关

        if storetype in [1,2]:
            '''
            if edate and ordermetric == '' and arg is None:
                order = 2
                ordermetric = 'cdate'
            if ordermetric == '':
                ordermetric = metric_list[0]
            '''
            if ordermetric!='' and ordermetric in udc_list:
                orderudc = True
                query_index = ''
                query_order = 0

        if not edate:
            edate=date

        # 不打算兼容$a $b
        if  user_data.addcolumn   is not  None or  int(storetype) not in [1,2]:
            addColumn=None
            if  user_data.addcolumn   is not  None:
                addColumn= urllib.unquote(user_data.addcolumn)
                addColumn=json.loads(addColumn)
            status, ret = db_query.getResult(dt=date, metric_conf=project_name, str_dim=group, metric=metric, order=order, ordermetric=ordermetric,
                                             getkey=True,
                                             edate=edate, index=query_index, offset=offset, filter_str=filter_str,
                                             list_addColumn=addColumn,search=search,table_list=table_list,storetype=storetype,
                                             start_hour=start_hour,end_hour=end_hour,custom_sort=custom_sort,converge=converge,date_type=date_type

            )
        else:
            status, info, ret = getUdcResult(db_query, date, project_name, group, metric, udc, query_order, ordermetric,
                                             True, edate, query_index, offset, filter_str,search=search,table_list=table_list,storetype=storetype,start_hour=start_hour,end_hour=end_hour,custom_sort=custom_sort,converge=converge,date_type=date_type)
        if not status:
            return base.retu(5, ret)
        total_number = 0

        if total and project_name!='user_access_count':
            t_res=()
            if user_data.addcolumn   is not  None:
                addColumn= urllib.unquote(user_data.addcolumn)
                addColumn=json.loads(addColumn)
                status, result = db_query.getResult(dt=date, metric_conf=project_name, str_dim=group, metric=metric, edate=edate, gettotal=True,
                                                filter_str=filter_str,table_list=table_list,storetype=storetype,search=search,list_addColumn=addColumn,start_hour=start_hour,end_hour=end_hour,custom_sort=custom_sort,converge=converge,date_type=date_type)
            else:
                status, result = db_query.getResult(dt=date, metric_conf=project_name, str_dim=group, metric=metric, edate=edate, gettotal=True,
                                                filter_str=filter_str,table_list=table_list,storetype=storetype,search=search,start_hour=start_hour,end_hour=end_hour,custom_sort=custom_sort,converge=converge,date_type=date_type)
            if not status:
                return base.retu(5, result)
            total_number = result[0][0]
        else:
            total_number=10000000

        ret_list = []
        for i in range(0, len(ret)):
            no_exist_list=[]
            for key, value in ret[i].items():
                try:
                    if (type(value) == str and value and type(eval(value)) == float) or type(value) == float \
                            or type(value) == decimal.Decimal:

                        ret[i][key] = '%.2f' % float(value)
                        if ret[i][key].endswith('.00'):
                            ret[i][key] = ret[i][key][:-3]
                    if value is None:
                        ret[i][key] = '不存在'
                        no_exist_list.append(key)
                except:
                    pass
                   #import traceback

                    #traceback.print_exc()

                ret[i][key] = str(ret[i][key])

            if ret[i].has_key('cdate'):
                ret[i]['date'] = ret[i]['cdate']
                #判断不存在数据是否为任务没有运行
                for e in  no_exist_list:
                    if run_module_alias.has_key(e):
                        tmp_alias_list=run_module_alias[e]
                        for a in tmp_alias_list:
                            tmp_ret_cdate=ret[i]['cdate']
                            if ret[i].has_key('hour'):
                                tmp_ret_cdate='%s %s:00'%(ret[i]['cdate'],str(ret[i]['hour']))
                            tmp_e='%s_%s'%(tmp_ret_cdate,a)
                            if tmp_e in no_success_dict.keys():
                                ret[i][e]='未生成'
                    '''
                    tmp_e='%s_%s_%s'%(ret[i]['cdate'],project_name,e)
                    if tmp_e in no_success_dict.keys():
                        no_success_dict.pop(tmp_e)
                        ret[i][e]='未生成'
                    '''
                del ret[i]['cdate']

            #维度聚合手动添加时间
            if converge:
                tmp_start_end_time='%s-%s'%(date,edate)
                ret[i]['date']=tmp_start_end_time
            ret_list.append(ret[i])
        # pprint(ret_list)
        if orderudc == True:
            reversed = False
            if int(order) == 2:
                reversed = True
            #print reversed,user_data.order
            if ordermetric!='':
                ret_list.sort(key=lambda x: (float)(x[ordermetric]), reverse=reversed)

            if index != '' and offset != '':
                ret_list = ret_list[int(index) * int(offset):int(offset)]
        #如果任务没有运行提示错误信息
        showMsg=get_return_show_msg(all_time_task_dict,project_name,date_type=date_type)

        replyMsg=get_return_reply_msg(no_success_dict,project_name)
        columnMap={}
        if table_id:
            columnMap=get_table_columns(table_id)
        retu = base.retu('', '', ret_list, {'total': total_number,'showMsg':showMsg,'relyMsg':replyMsg,"columnMap":columnMap})
        return retu



def get_table_columns(table_id):
    import phpserialize
    table_columns={}
    try:
        mmsconf = MmsConf()
        res=mmsconf.getTableInfoById(table_id)
        if res:
            res=res[0]
            params=phpserialize.loads(res['params'])
            if params.has_key("tablelist"):
                tablelist=params["tablelist"]
                for key, params_table in tablelist.items():
                    grade=params_table.get("grade",{})
                    if grade.has_key("data"):
                        data=grade.get("data",{})
                        for key,col in data.items():
                            name=col["key"]
                            cn_name=col["name"]
                            table_columns[name]=cn_name

    except:
        import traceback
        traceback.print_exc()
    return table_columns

#查询数据任务依赖任务允许是否完成,任务没有完成返回
def get_data_rely_task_status(date,edate,date_type,project_name,metric,addcolumn):
    no_success_tasks={}
    run_module_metric_dict={}
    run_module_alias={}
    project_list=[project_name]
    metric_list=metric.split(',')
    all_tasks=[]
    for m in metric_list:
        if m:
            tmp_run_module=m[0:m.rindex('.')]
            tmp_metric=m[m.rindex('.')+1:]
            tmp_key='%s.%s'%(project_name,tmp_run_module)
            all_tasks.append(tmp_key)
            tmp_alias_metric_key=m.replace('.','_')
            if not run_module_alias.has_key(tmp_alias_metric_key):
                run_module_alias[tmp_alias_metric_key]=[]
            run_module_alias[tmp_alias_metric_key].append('%s_%s'%(project_name,tmp_alias_metric_key))

            if not run_module_metric_dict.has_key(tmp_key):
                run_module_metric_dict[tmp_key]=[]
            run_module_metric_dict[tmp_key].append(tmp_metric)

    if addcolumn is not None:
        addColumn= urllib.unquote(addcolumn)
        addColumn=json.loads(addColumn)
        udf=['max','min','sum','avg','count']
        reg_udf='|'.join(udf)
        r=re.compile(r'((%s)\((.*?)(>>.*)?\))'%reg_udf)
        allr=re.compile(r'((.*?)(\+|\-|\*|/|$)(?!>))')

        for dict_column in addColumn:
            column_name=dict_column['name']
            addColumn=dict_column['expression']
            allresult=allr.findall(addColumn)
            for all in allresult:
                result_name=all[1]
                result=[]
                tmp_result=r.findall(result_name)
                if tmp_result:
                    result=tmp_result[0]
                if result:
                    content=result[2]
                else:
                    content=result_name
                dim_metric=content.split('->')
                if len(dim_metric)==2:
                    try:
                        split_metric=dim_metric[1].split('.')
                        add_project=''
                        add_metric=''
                        if len(split_metric)==4:
                            add_project=split_metric[0]
                            project_list.append(add_project)
                            add_metric='.'.join(split_metric[1:])
                        else:
                            add_project=project_name
                            add_metric=dim_metric[1]

                        add_tmp_key='%s.%s'%(add_project,add_metric[0:add_metric.rindex('.')])

                        all_tasks.append(add_tmp_key)

                        if not run_module_alias.has_key(column_name):
                            run_module_alias[column_name]=[]

                        run_module_alias[column_name].append('%s_%s'%(add_project,add_metric.replace('.','_')))

                        if not run_module_metric_dict.has_key(add_tmp_key):
                            run_module_metric_dict[add_tmp_key]=[]
                        run_module_metric_dict[add_tmp_key].append(add_metric[add_metric.rindex('.')+1:])

                    except:
                        pass
    tmp_task_dict={}
    tmp_time_list=[]
    now_time=int(time.time())
    tmp_interval=86400
    start=0
    end=0
    d_f='%Y-%m-%d'
    if date_type=='hour':
        tmp_interval=3600
        d_f='%Y-%m-%d %H:%M'
        start=time.mktime(time.strptime(date,"%Y-%m-%d %H:%M"))
        end=int(time.mktime(time.strptime(edate,"%Y-%m-%d %H:%M")))+tmp_interval
    else:
        start=time.mktime(time.strptime(date,"%Y-%m-%d"))
        end=int(time.mktime(time.strptime(edate,"%Y-%m-%d")))+tmp_interval

    for t in range(int(start),int(end),tmp_interval):
        tmp_time_list.append(time.strftime(d_f,time.localtime(t)))

    for k,v in run_module_metric_dict.items():
        for tt in tmp_time_list:
            tmp_task_dict['%s.%s'%(tt,k)]={'task_name':'%s'%(k),'metric':v,'time':tt}

    all_time_tasks_list=[]

    for e in list(set(all_tasks)):
        for tt in tmp_time_list:
            all_time_tasks_list.append('%s.%s'%(tt,e))




    # sql='''
    #     select t.task,t.app_name from (
    #     select app_name,concat(stat_date,'.',app_name,'.',run_module) task from mms_run_log where status in (5,7) and stat_date>='%s' and stat_date<='%s'
    #     )t
    # '''%(date,edate)
    #
    # t_where=' where 1!=1 '
    # for tt in set(project_list):
    #     t_where+=" or t.app_name='%s' "%(tt)
    # sql+=t_where

    sql='''
        select concat(stat_date,'.',app_name,'.',run_module) task,app_name,id,status from mms_run_log where  stat_date>='%s' and stat_date<='%s'
    '''%(date,edate)
    replace_project_list=["'"+ele+"'" for ele in set(project_list)]
    project_in_str=','.join(replace_project_list)
    project_in_str=project_in_str.strip()
    t_where=' '
    if project_in_str and len(project_in_str)>0:
        t_where=' and app_name in (%s) '%(project_in_str)
    sql+=t_where

    select_ojb=MmsConf()
    res=select_ojb.get_project_success_task(sql)

    all_time_task_dict={}
    tmp_time_task={}
    for e in all_time_tasks_list:
        if e not in res:
            tmp_task_date=e[0:e.index('.')]
            tmp_task_name=e[e.index('.')+1:]
            if not tmp_time_task.has_key(tmp_task_date):
                tmp_time_task[tmp_task_date]=[]
            tmp_time_task[tmp_task_date].append(tmp_task_name)

    for k,v in tmp_time_task.items():
        if len(set(v))==len(set(all_tasks)):
            for e in v:
                all_time_task_dict['%s.%s'%(k,e)]={'task_name':'%s'%(e),'time':k}


    for q_t,v in tmp_task_dict.items():
        if q_t not in res:

            t_m_l=v['metric']
            for t in t_m_l:
                t_key='%s.%s'%(q_t,t)
                t_key=t_key.replace('.','_')
                no_success_tasks[t_key]=v

    return (no_success_tasks,run_module_alias,all_time_task_dict)

def get_return_show_msg(no_success_dict,project_name=None,date_type='day'):
    msg=''
    white_project_list=conf.WHITE_PROJECT_LIST.split(',')
    if len(no_success_dict)>0 and project_name and project_name not in white_project_list:
        project_group_inf=get_project_group_inf(project_name)
        no_success_time_list=[]
        for no_k,no_v in no_success_dict.items():
            q_time=no_v['time']
            q_task_name=no_v['task_name']

            if project_group_inf.has_key(q_task_name) and project_group_inf[q_task_name]:
                tmp_q_task=project_group_inf[q_task_name]
                #如果是自定义时间任务不返回未生成信息
                if tmp_q_task.has_key('custom_cdate') and tmp_q_task['custom_cdate'] and int(tmp_q_task['custom_cdate'])!=0:
                    continue
                if tmp_q_task.has_key('schedule_interval') and tmp_q_task['schedule_interval']:
                    tmp_interval=tmp_q_task['schedule_interval']
                    r = re.compile(r'^(\d+)(_(\d+)+)?$')
                    res=r.findall(str(tmp_interval))
                    if res:
                        res=res[0]
                        re_offset=re.compile(r'^([-]?\d+)(day|minute|hour)')
                        offset=0
                        if project_group_inf.has_key(q_task_name) and project_group_inf[q_task_name]:
                            if tmp_q_task.has_key('schedule_interval_offset'):
                                offset_res=re_offset.findall(tmp_q_task['schedule_interval_offset'])
                                if len(offset_res)>0:
                                    offset_val=int(offset_res[0][0])
                                    offset_type=str(offset_res[0][1].encode('utf-8')).strip()
                                    if 'day'==offset_type:
                                        offset=int(offset_val*86400)

                        if int(res[0])==30:
                            if project_group_inf.has_key(q_task_name) and project_group_inf[q_task_name]:
                                if tmp_q_task.has_key('schedule_interval_offset'):
                                    offset_res=re_offset.findall(tmp_q_task['schedule_interval_offset'])
                                    if len(offset_res)>0:
                                        offset_val=int(offset_res[0][0])
                                        offset=int(offset_val*86400)
                            q_stat_date_time=int(time.mktime(time.strptime(q_time,'%Y-%m-%d')))-offset
                            q_time_day=int(time.strftime('%d',time.localtime(q_stat_date_time)))
                            if int(q_time_day)!=int(res[2]):
                                continue
                            else:
                                q_time=time.strftime("%Y-%m",time.strptime(q_time,"%Y-%m-%d"))
                        elif int(res[0])==7:
                            q_stat_date_time=int(time.mktime(time.strptime(q_time,'%Y-%m-%d')))-offset
                            weekday=int(time.strftime('%w',time.localtime(q_stat_date_time)))

                            if 0==weekday:
                                weekday=7
                            if int(res[2])!=weekday:
                                continue

            no_success_time_list.append(q_time)
        if no_success_time_list:
            no_success_time_list=sorted(list(set(no_success_time_list)))
            msg_tip=','.join(sorted(no_success_time_list))
            if len(no_success_time_list)>3 and date_type=='day':
                msg_tip='%s~%s有'%(str(no_success_time_list[0]),str(no_success_time_list[len(no_success_time_list)-1]))
                date_start=datetime.datetime.strptime(str(no_success_time_list[0]),'%Y-%m-%d')
                date_end=datetime.datetime.strptime(str(no_success_time_list[len(no_success_time_list)-1]),'%Y-%m-%d')
                diff_start_end=date_end-date_start
                diff_start_end=int(diff_start_end.days)+1
                if len(no_success_time_list)==int(diff_start_end):
                    msg_tip='%s~%s'%(str(no_success_time_list[0]),str(no_success_time_list[len(no_success_time_list)-1]))
            if len(no_success_time_list)>3 and date_type=='hour':
                msg_tip='%s~%s'%(str(no_success_time_list[0]),str(no_success_time_list[len(no_success_time_list)-1]))
            if len(no_success_time_list)>3 and date_type=='month':
                msg_tip='%s~%s'%(str(no_success_time_list[0]),str(no_success_time_list[len(no_success_time_list)-1]))
            msg='%s数据未生成'%(msg_tip)
    return msg

def get_return_reply_msg(no_success_dict,project_name=None):
    no_success_time2task_dict={}
    white_project_list=conf.WHITE_PROJECT_LIST.split(',')
    if len(no_success_dict)>0 and project_name and project_name not in white_project_list:

        project_group_inf=get_project_group_inf(project_name)
        for no_k,no_v in no_success_dict.items():
            q_time=no_v['time']
            task_name=no_v['task_name']

            if project_group_inf.has_key(task_name) and project_group_inf[task_name]:
                tmp_q_task=project_group_inf[task_name]
                if tmp_q_task.has_key('custom_cdate') and tmp_q_task['custom_cdate'] and int(tmp_q_task['custom_cdate'])!=0:
                    continue
                if tmp_q_task.has_key('schedule_interval') and tmp_q_task['schedule_interval']:
                    tmp_interval=tmp_q_task['schedule_interval']
                    r = re.compile(r'^(\d+)(_(\d+)+)?$')
                    res=r.findall(str(tmp_interval))
                    if res:
                        res=res[0]

                        re_offset=re.compile(r'^([-]?\d+)(day|minute|hour)')
                        offset=0
                        if project_group_inf.has_key(task_name) and project_group_inf[task_name]:
                            if tmp_q_task.has_key('schedule_interval_offset'):
                                offset_res=re_offset.findall(tmp_q_task['schedule_interval_offset'])
                                if len(offset_res)>0:
                                    offset_val=int(offset_res[0][0])
                                    offset_type=str(offset_res[0][1].encode('utf-8')).strip()
                                    if 'day'==offset_type:
                                        offset=int(offset_val*86400)

                        if int(res[0])==30:
                            if project_group_inf.has_key(task_name) and project_group_inf[task_name]:
                                if tmp_q_task.has_key('schedule_interval_offset'):
                                    offset_res=re_offset.findall(tmp_q_task['schedule_interval_offset'])
                                    if len(offset_res)>0:
                                        offset_val=int(offset_res[0][0])
                                        offset=int(offset_val*86400)
                            q_stat_date_time=int(time.mktime(time.strptime(q_time,'%Y-%m-%d')))-offset
                            q_time_day=int(time.strftime('%d',time.localtime(q_stat_date_time)))
                            if int(q_time_day)!=int(res[2]):
                                continue
                            else:
                                q_time=time.strftime("%Y-%m",time.strptime(q_time,"%Y-%m-%d"))

                        elif int(res[0])==7:
                            q_stat_date_time=int(time.mktime(time.strptime(q_time,'%Y-%m-%d')))-offset
                            weekday=int(time.strftime('%w',time.localtime(q_stat_date_time)))

                            if 0==weekday:
                                weekday=7
                            if int(res[2])!=weekday:
                                continue

            if not no_success_time2task_dict.has_key(q_time):
                no_success_time2task_dict[q_time]=[]
            no_success_time2task_dict[q_time].append(task_name)


        for k in sorted(no_success_time2task_dict.keys()):
            v=no_success_time2task_dict[k]
            no_success_time2task_dict[k]=list(set(v))
    return no_success_time2task_dict