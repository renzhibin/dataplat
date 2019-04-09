#!/usr/bin/env python2.7
# coding=utf-8

import web, collections,traceback
import yaml
from lib import *
import mms.lib.mms_conf as mms_conf
import mms.lib.app_conf as appObj

dir_path = const.dir_path


class GetAppConf(action.Action):
    def GET(self):
        try:
            user_data = web.input(project='',from_table=None,get_hql=None)
            project_name = user_data.project
            #为前端构造表头特殊处理
            f_table=user_data.from_table
            get_hql=user_data.get_hql

            appConf=appObj.AppConf(project_name)

            if not appConf.appExist:
                return base.retu(1, 'no such app')
            hqls={}
            if get_hql:
                hqls=appConf.get_hql()

            yaml_content = collections.OrderedDict()
            yaml_content['project'] = []
            yaml_content['project'].append(
                collections.OrderedDict([('name', ''), ('cn_name', ''), ('explain', ''), ('categories', [])]))
            yaml_content['project'][0].update(appConf.appConf['project'][0])

            dim_metric_mmsConf=mms_conf.MmsConf()
            dimensions_inf=dim_metric_mmsConf.get_app_dimensions_params(project_name)
            metrics_inf=dim_metric_mmsConf.get_app_all_metrics_params(project_name)

            for categories_index in range(0, len(yaml_content['project'][0]['categories'])):
                org_categories = yaml_content['project'][0]['categories'][categories_index]
                sql_catego=org_categories['name']
                org_categories['name']=org_categories['name'].lower()
                categories = collections.OrderedDict([('name', ''), ('cn_name', ''), ('explain', ''), ('groups', [])])
                categories.update(org_categories)
                yaml_content['project'][0]['categories'][categories_index] = categories
                for i in range(0, len(categories['groups'])):
                    org_groups_content = categories['groups'][i]

                    #小时分钟维度
                    schedule_interval=False
                    if org_groups_content.has_key('schedule_interval') and org_groups_content['schedule_interval'] and f_table:
                        interval=org_groups_content['schedule_interval']
                        interval_split=interval.split('_')
                        if len(interval_split)==1 and int(interval_split[0])==60:
                            schedule_interval=True


                    sql_group=org_groups_content['name']
                    org_groups_content['name']=org_groups_content['name'].lower()
                    groups_content = collections.OrderedDict(
                        [('name', ''), ('cn_name', ''), ('explain', ''), ('metrics', ''), ('dimensions', []),
                         ('dim_sets', []), ('tables', [])])
                    # just for output by order

                    groups_content.update(org_groups_content)
                    for m_index in range(0, len(groups_content['metrics'])):
                        o_d_m = groups_content['metrics'][m_index]
                        #添加指标类型
                        tmp_met_name=o_d_m['name']
                        tmp_met_name='%s_%s_%s'%(org_categories['name'],org_groups_content['name'],tmp_met_name)
                        met_type='decimal'
                        if metrics_inf.has_key(tmp_met_name) and metrics_inf[tmp_met_name]:
                            tmp_met=metrics_inf[tmp_met_name]
                            if tmp_met.has_key('type') and tmp_met['type']:
                                met_type=tmp_met['type']
                        o_d_m['type']=met_type
                        o_d_m['name']=o_d_m['name'].lower()
                        d_m = collections.OrderedDict(
                            [('name', ''), ('cn_name', ''), ('explain', ''), ('pseudo_code', '')])
                        d_m.update(o_d_m)
                        groups_content['metrics'][m_index] = d_m
                    for m_index in range(0, len(groups_content['dimensions'])):
                        o_d_m = groups_content['dimensions'][m_index]
                        #添加维度 类型
                        tmp_dim_name=o_d_m['name']
                        dim_type='varchar'
                        if dimensions_inf.has_key(tmp_dim_name) and dimensions_inf[tmp_dim_name]:
                            tmp_dim=dimensions_inf[tmp_dim_name]
                            if tmp_dim.has_key('type') and tmp_dim['type']:
                                dim_type=tmp_dim['type']

                        o_d_m['type']=dim_type
                        o_d_m['name']=o_d_m['name'].lower()
                        d_m = collections.OrderedDict([('name', ''), ('cn_name', ''), ('explain', '')])
                        d_m.update(o_d_m)
                        groups_content['dimensions'][m_index] = d_m
                    #添加cdate字段
                    if f_table:
                        cdate_d_m=collections.OrderedDict([('name', 'date'), ('cn_name', '时间'), ('explain', '时间')])
                        groups_content['dimensions'].append(cdate_d_m)
                    if schedule_interval:
                        h_d_m = collections.OrderedDict([('name', 'hour'), ('cn_name', '小时'), ('explain', '小时')])
                        m_d_m = collections.OrderedDict([('name', 'minute'), ('cn_name', '分钟'), ('explain', '分钟')])
                        groups_content['dimensions'].append(h_d_m)
                        groups_content['dimensions'].append(m_d_m)
                    for m_index in range(0, len(groups_content['tables'])):
                        o_d_m = groups_content['tables'][m_index]
                        d_m = collections.OrderedDict([('name', ''), ('cn_name', ''), ('par', '')])
                        d_m.update(o_d_m)
                        groups_content['tables'][m_index] = d_m
                    if get_hql:
                        sql_name = sql_catego + '_' + sql_group
                        sql_name=sql_name.lower()
                        groups_content['hql'] = hqls[sql_name]

                    yaml_content['project'][0]['categories'][categories_index]['groups'][i] = groups_content

            if get_hql:
                run_list=appConf.get_run_list()
                yaml_content['run']=run_list
                yaml_content['project'][0]['storetype']=appConf.storetype

            retu = base.retu('', '', yaml_content)

        except:
            traceback.print_exc()
            retu = base.retu(1, 'no such app')

        return retu


