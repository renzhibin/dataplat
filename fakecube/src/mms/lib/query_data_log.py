#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'
import sys

import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql

def save_query_data_log(token_name='',project_name='',group='',metric=''):
    mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
    conn=mmsMysql.get_conn()
    cur=mmsMysql.get_cur()

    try:
        sql="insert into t_query_data_log (token_name,project_name,`group`,metric) values ('%s','%s','%s','%s')"%(token_name,project_name,group,metric)
        cur.execute(sql)
        conn.commit()
        conn.close()
    except Exception,e:
        import traceback
        traceback.print_exc()






