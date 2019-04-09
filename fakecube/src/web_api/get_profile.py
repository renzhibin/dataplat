#!/usr/bin/env python2.7
#coding=utf-8

import web,collections
import json
import yaml
import os,sys,datetime

sys.path.append('..')
from mms.lib.hql_profile import *
from mms.bin.run_task_single import replace
from lib import  *
from mms.lib.mms_conf import MmsConf
from mms.lib.mms_hql_parse import *
import re

dir_path = const.dir_path

class GetProfile(action.Action):
    def get_profile(self,hql,type='dict',src=None,profile_type='hql',hql_type=1,app_name='',category_name='',hql_name='',custom_cdate=0,attach=''):

       # hql="select order_id, user_id as uid, grouping__id, count(*) as pv from dm.dm_order_4analyst where plat='mob' and order_create_dt='2014-11-07'  group by order_id, user_id grouping sets (user_id, order_id, (user_id, order_id), ())"
        try:
            hql=str(hql.strip())
            status,profile=profile_from_hql(base.replace(hql),src,profile_type,hql_type,attach)
        except :
            import traceback
            traceback.print_exc()
            return base.retu('1','parse sql error')
        if status!=0:
            return base.retu('1','parse sql error:'+profile)
        if int(hql_type)!=1:
            if int(hql_type)==2:
                r=re.compile(r'(^\s*(insert|create|INSERT|CREATE|alter|ALTER)\s+([\w.]+))')
                r_res=r.findall(hql)
                if not r_res:
                    return base.retu('1','调度类项目只支持create,insert,alter操作')
                if 'insert'==str(r_res[0][1]).strip().lower():
                    res=dict()
                    tables=get_hql_tables(hql)
                    if len(tables)>0:
                        mmsConf=MmsConf()
                        table_names=mmsConf.get_all_table_name()
                        tmp=[]
                        for i  in set(tables):
                            cn_name=''
                            if table_names.has_key(str(i).strip().lower()):
                                cn_name=table_names[str(i).strip().lower()]
                            tmp.append(collections.OrderedDict([('name',i),('cn_name',cn_name),('par','')]))
                        res['tables']=tmp
                    return base.retu(status,profile,res)
            return base.retu(status,profile)

        #获取维度类型，指标类型


        #ast_str = "(TOK_QUERY (TOK_FROM (TOK_TABREF (TOK_TABNAME dm dm_order_4analyst))) (TOK_INSERT (TOK_DESTINATION (TOK_DIR TOK_TMP_FILE)) (TOK_SELECT (TOK_SELEXPR (TOK_TABLE_OR_COL order_id)) (TOK_SELEXPR (TOK_TABLE_OR_COL user_id) uid) (TOK_SELEXPR (TOK_TABLE_OR_COL grouping__id)) (TOK_SELEXPR (TOK_FUNCTIONSTAR count) pv)) (TOK_WHERE (= (TOK_TABLE_OR_COL plat) 'mob')) (TOK_GROUPING_SETS (TOK_TABLE_OR_COL order_id) (TOK_TABLE_OR_COL user_id) (TOK_GROUPING_SETS_EXPRESSION (TOK_TABLE_OR_COL user_id)) (TOK_GROUPING_SETS_EXPRESSION (TOK_TABLE_OR_COL order_id)) (TOK_GROUPING_SETS_EXPRESSION (TOK_TABLE_OR_COL user_id) (TOK_TABLE_OR_COL order_id)) TOK_GROUPING_SETS_EXPRESSION)))"
        #profile = profile_from_ast(ast_str,src)[0]
        if profile.has_grouping__id is not  True:
            return base.retu('2','no grouping id')

        if profile.proper_pos is  not True:
            return base.retu('3','gronp id must betweent dim  and metric')

        #自定义数据展现时间模式下，只有cdate作为维度的情况下，维度列表返回空，跳过空维度的检测
        only_cdate_dim = False
        if custom_cdate:
            #自定义任务时间下， 要求第一列select dimension为cdate
            if profile.dimensions_in_select[0] != 'cdate':
                return base.retu('5', "自定义数据展现时间模式下要求'cdate'为维度首列, 并且目前时间格式只支持'年-月-日'和'年-月'")

            #grouping sets不允许包含()聚合维度
            if "()" in profile.grouping_sets:
                return base.retu('5', "自定义数据展现时间模式下不允许'()'全聚合维度作为维度组合")

            #检查grouping sets中各维度组合是否包含cdate, 并将其移除
            tmp = []
            for gs in profile.grouping_sets:
                dim_list = gs.strip('()').split(',')
                if 'cdate' in dim_list and 'cdate' == dim_list[0]:
                    dim_list.remove('cdate')
                else:
                    return base.retu('5', "自定义数据展现时间模式下要求每个维度组合必须包含'cdate'")
                dim_str = '('+','.join(dim_list)+')'
                tmp.append(dim_str)
            profile.grouping_sets = list(set(tmp))

            #移除cdate in dimensions, dimensions_in_select and grouping sets
            if 'cdate' in profile.dimensions:
                profile.dimensions.remove('cdate')
            if 'cdate' in profile.dimensions_in_select:
                #只有cdate作为维度
                if len(profile.dimensions_in_select) == 1:
                    only_cdate_dim = True
                profile.dimensions_in_select.remove('cdate')

        select_field_tmp=profile.dimensions_in_select
        tmp_not_field=['id','ID','all','ALL','hour','HOUR','minute','MINUTE']
        for f in tmp_not_field:
            if f in select_field_tmp:
                return_str='维度名中不可包含%s'%(f)
                return base.retu('4',return_str)

        #profile_dict={'metric':profile.metrics,'dim':profile.dimensions,
         #             'gs':profile.grouping_sets,'tables':profile.tables}
        if  type=='html':
            return profile
        
        tmp=[]

        #维度，指标类型
        dim_metric_mmsConf=MmsConf()
        dimensions_inf=dim_metric_mmsConf.get_app_dimensions_params(app_name)
        metrics_inf=dim_metric_mmsConf.get_app_metrics_params(app_name,category_name,hql_name)

        profile_dict=collections.OrderedDict([('name',''),('cn_name',''),('explain','')])
        for i in profile.dimensions_in_select:
            dim_type=''
            tmp_dim_key=str(i).strip().lower()
            if dimensions_inf.has_key(tmp_dim_key):
                tmp_dim=dimensions_inf[tmp_dim_key]
                dim_type='varchar'
                if tmp_dim.has_key('type') and tmp_dim['type']:
                    dim_type=tmp_dim['type']

            tmp.append(collections.OrderedDict([('name',str(i).strip().lower()),('cn_name',''),('explain',''),('type',dim_type)]))
        #维度不可为空
        if len(tmp)==0:
            if custom_cdate and only_cdate_dim:
                #跳过空检测
                pass
            else:
                return base.retu('5','hql不包含维度。')
        profile_dict['dimensions']=tmp
        tmp=[]
        for i in profile.metrics:
            metric_type=''
            tmp_met_key='%s_%s_%s'%(category_name.strip().lower(),hql_name.strip().lower(),str(i).strip().lower())
            if metrics_inf.has_key(tmp_met_key):
                tmp_metric=metrics_inf[tmp_met_key]
                metric_type='decimal'
                if tmp_metric.has_key('type') and tmp_metric['type']:
                    metric_type=tmp_metric['type']

            tmp.append(collections.OrderedDict([('name',str(i).strip().lower()),('cn_name',''),('explain',''),('pseudo_code',''),('type',metric_type)]))
        #指标不可为空
        if len(tmp)==0:
            return base.retu('5','hql不包含指标')

        input = open('white_list_metric')
        white_list = list()
        long_metrics = input.readlines()
        for line in long_metrics:
            white_list.append(line.strip())
        input.close()
        for metric in tmp:
            if metric['name'] in white_list:
                continue;
            if len(metric['name']) > 20:
                return base.retu('5','指标'+metric['name']+'超过了20个字符，请修改')

        profile_dict['metrics']=tmp
        tmp=[]
        for i in profile.grouping_sets:
            '''
            if len(str(i).strip('()').split(','))>15:
                return base.retu('5','维度组合中维度个数最多不超过15个')
            '''
            tmp.append({'name':i})
        profile_dict['dim_sets']=tmp
        tmp=[]
        #自动填写中文名
        mmsConf=MmsConf()
        table_names=mmsConf.get_all_table_name()
        for i  in set(profile.tables):
            cn_name=''
            if table_names.has_key(str(i).strip().lower()):
                cn_name=table_names[str(i).strip().lower()]
            tmp.append(collections.OrderedDict([('name',i),('cn_name',cn_name),('par','')]))
        profile_dict['tables']=tmp

        if type=='default':
            return base.retu('','',profile_dict)

        return  profile_dict


    def POST(self):
        user_data=web.input(hql='',project='',showtype='default',type='hql',hql_type=1,app_name='',category_name='',hql_name='',custom_cdate=0,attach='')
        project=user_data.project
        hql=user_data.hql
        showtype=user_data.showtype
        profile_type=user_data.type
        hql_type=user_data.hql_type
        custom_dt_flag = int(user_data.custom_cdate)
        attach_info = user_data.attach

        app_name=user_data.app_name
        category_name=user_data.category_name
        hql_name=user_data.hql_name
        if hql:
           if  showtype=='default':
                return self.get_profile(hql,showtype,None,profile_type,hql_type=hql_type,app_name=app_name,category_name=category_name,hql_name=hql_name,custom_cdate=custom_dt_flag,attach=attach_info)
           else:
               import time 
               filename='/static/img/'+str(time.time())+'.png'
               src=os.getcwd()+filename
               profile=self.get_profile(hql,'html',src,hql_type=hql_type)
               render = web.template.render('templates/')
               return render.get_profile_post({'name':str(profile),'src':filename})
        yaml_content=yaml.load(project)
        project_name=yaml_content['project'][0]['name']
        categories_index =len(yaml_content['project'][0]['categories'])-1
        categories=yaml_content['project'][0]['categories'][categories_index]

        group_index=len(categories['groups'])-1
        hql=categories['groups'][group_index]['hql']
        profile=self.get_profile(hql,hql_type=hql_type)
        profile['hql']=hql
        yaml_content['project'][0]['categories'][categories_index]['groups'][group_index]=profile
        render = web.template.render('templates/')
        return render.get_project(json.dumps(yaml_content))

    def GET(self):
	if web.input().has_key('test'):
		return 'uptime test'
        render = web.template.render('templates/')
        return render.get_profile_show()






