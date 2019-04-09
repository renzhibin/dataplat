#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import web,yaml,collections
from lib import *
import mms.lib.app_conf as appObj


dir_path = const.dir_path

class GetProjectDimConf(action.Action):

    def POST(self):
        self.GET()

    def GET(self):
        user_data=web.input(project_name='')
        project_name=user_data.project_name
        if not project_name:
            return base.retu(1,'project name is null')

        yaml_content=self.get_project_yaml(project_name)
        dim_met_list=self.get_dim_met_list(yaml_content)
        if len(dim_met_list)<=0:
            return base.retu(1,"fail",{"dim_met":dim_met_list})
        return base.retu(0,"success",{"dim_met":dim_met_list})


    def get_dim_met_list(self,yaml_content):
        dim_met_list=[]
        yaml_content_len=len(yaml_content)

        try:
            if yaml_content_len>0:
                project_content=yaml_content["project"][0]
                project_name=project_content["name"]
                dim_sets_dict={}
                dim_name_tmp={}

                for category_index in range(len(project_content["categories"])):
                    category = project_content["categories"][category_index]

                    for group_index in range(0,len(category["groups"])):
                        group = category["groups"][group_index]

                        group_dimensions=group["dimensions"]#维度
                        for dim_index in range(0,len(group_dimensions)):
                            dim=group_dimensions[dim_index]
                            dim_name_tmp[str(dim["name"]).strip().lower()]="无"

                            if dim.has_key("cn_name") and str(dim["cn_name"]):
                                dim_name_tmp[str(dim["name"]).strip().lower()]=dim["cn_name"]


                for category_index in range(len(project_content["categories"])):
                    category = project_content["categories"][category_index]
                    category_name=category["name"]

                    for group_index in range(0,len(category["groups"])):
                        group = category["groups"][group_index]
                        group_name=group["name"]

                        group_metrics=group["metrics"]#指标

                        met_name_str_list=[]
                        met_col_str_list=[]
                        for met_index in range(0,len(group_metrics)):
                            met=group_metrics[met_index]
                            met_cn_name="无"
                            if met.has_key("cn_name") and str(met["cn_name"]):
                                met_cn_name=met["cn_name"]
                            met_str=str(project_name)+"."+str(category_name)+"."+str(group_name)+"."+met["name"]+"/"+met_cn_name
                            col_str=str(category_name)+"_"+str(group_name)+"_"+met["name"]+"/"+met_cn_name
                            met_col_str_list.append(col_str)
                            met_name_str_list.append(str(met_str))

                        group_dim_sets=group["dim_sets"]#

                        for dim_set_index in range(0,len(group_dim_sets)):
                            dim_met_temp=collections.OrderedDict([("dim_names",""),("met_sets",[])])
                            dim_set=group_dim_sets[dim_set_index]["name"]
                            dim_set_str=dim_set

                            if ")"==dim_set[-1:]:
                                dim_set_str=str(dim_set[1:-1]).strip()

                            if len(dim_set_str)>0:
                                tmp_en_list=str(dim_set_str).split(",")
                                tmp_cn_list=[]
                                tmp_names=[]

                                for tmp_en_index in range(0,len(tmp_en_list)):
                                    tmp_names.append(str(tmp_en_list[tmp_en_index])+"/"+str(dim_name_tmp[str(tmp_en_list[tmp_en_index]).strip().lower()]))

                                dim_set_dict_key=str(",".join(tmp_names)).lower()
                                if dim_sets_dict.has_key(dim_set_dict_key):
                                    met_str_list=list(set(dim_sets_dict[dim_set_dict_key]['met_str_list']+met_name_str_list))
                                    met_col_list=list(set(dim_sets_dict[dim_set_dict_key]['met_col_list']+met_col_str_list))
                                    dim_sets_dict[dim_set_dict_key]={'met_str_list':met_str_list,'met_col_list':met_col_list}
                                else:
                                    met_str_list=list(set(met_name_str_list))
                                    met_col_list=list(set(met_col_str_list))
                                    dim_sets_dict[dim_set_dict_key]={'met_str_list':met_str_list,'met_col_list':met_col_list}

                            else:
                                if dim_sets_dict.has_key("all/总量"):

                                    met_str_list=list(set(dim_sets_dict["all/总量"]['met_str_list']+met_name_str_list))
                                    met_col_list=list(set(dim_sets_dict["all/总量"]['met_col_list']+met_col_str_list))
                                    dim_sets_dict["all/总量"]={'met_str_list':met_str_list,'met_col_list':met_col_list}

                                else:
                                    met_str_list=list(set(met_name_str_list))
                                    met_col_list=list(set(met_col_str_list))
                                    dim_sets_dict["all/总量"]={'met_str_list':met_str_list,'met_col_list':met_col_list}


                for dim_names,met_sets in dim_sets_dict.items():
                    dim_met_temp=collections.OrderedDict([("dim_names",""),("met_sets",[])])
                    dim_met_temp["dim_names"]=str(dim_names).split(",")
                    tmp_mets=met_sets['met_str_list']
                    dim_met_temp["met_sets"]=tmp_mets
                    tmp_cols=met_sets['met_col_list']
                    dim_met_temp["met_cols"]=tmp_cols
                    dim_met_list.append(dim_met_temp)

        except Exception:
            import traceback
            traceback.print_stack()

        return dim_met_list


    def get_project_yaml(self,project_name=""):

        org_yaml_content=[]
        try:
            if project_name:
                appConf=appObj.AppConf(project_name)
                org_yaml_content = appConf.appConf
                return org_yaml_content

        except Exception:
            import traceback
            traceback.print_stack()

        return org_yaml_content

















