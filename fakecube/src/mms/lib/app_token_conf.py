#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'
import sys
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql


class AppTokenConf(object):

    def __init__(self):

        mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
        self.conn=mmsMysql.get_conn()
        self.cur=mmsMysql.get_cur()

    def __del__(self):
        if self.conn.open:
            self.conn.close()


    def save_token(self,user_name=None,app_name=None,token_val=None):
        try:

            sql="insert into %s (user_name,app_name,token_val) values ('%s','%s','%s')"%("t_app_token",user_name,app_name,token_val)
            self.cur.execute(sql)
            self.conn.commit()
            return True,"保存成功！"
        except Exception,e:
            import traceback
            traceback.print_exc()

            return False,"保存token失败！"



    def apply_project(self,token_val=None,project_name=None,app_name=None):

        try:

            result=self.select(app_name,token_val)

            if len(result)<=0:
                return False,"检查token"
            p_list=str(result[0]["project_name"]).split(",")
            if "all" in p_list:
                return False,"超级权限"
            if project_name in p_list:
                project_name=result[0]["project_name"]

                return False,"已经具有该项目权限"
            else:
                project_name=",".join((result[0]["project_name"],str(project_name).strip())) if result[0]["project_name"] else str(project_name).strip()


            sql="update %s set project_name='%s' where app_name='%s' and token_val='%s' "
            sql=sql%("t_app_token",project_name,app_name,token_val)

            self.cur.execute(sql)

            self.conn.commit()
            return  True,"申请成功！"
        except Exception,e:
            import traceback
            traceback.print_exc()
            return False,"申请失败！"



    def select(self,app_name=None,token_val=None,user_name=None):

        sql="select * from %s "%("t_app_token")
        where=" where 1=1 "
        if user_name:
            where+=" and user_name='%s' "%(user_name)
        if app_name:
            where+=" and app_name='%s' "%(app_name)
        if token_val:
            where+=" and token_val='%s' "%(token_val)

        sql=sql+where

        self.cur.execute(sql)

        columns=self.cur.description

        result=[]
        tmp=dict()
        for value in self.cur.fetchall():
            tmp={}
            for (index,column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)

        return  result

