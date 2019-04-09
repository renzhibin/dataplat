#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import os
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
mysql_conf_path=os.path.dirname(cur_abs_dir)+'/conf/mysql.ini'
def parse_mysql_conf():
    #meiliwork,dbreader 替换为mlswriter,mlsreader  密码需要读配置文件
    # mysql_conf_path='/home/work/conf/real/bj/mysql/bi/bi.mysql.ini'
    # mysql_conf_path='/Users/MLS/Desktop/workspace/mysqlconf/bi.mysql.ini'
    import os
    f_obj=open(mysql_conf_path,'r')
    conf_str=f_obj.read()
    f_obj.close()

    result_arr=[]
    conf_list=conf_str.split('\n')
    for conf in conf_list:
        if len(conf.strip())>0:
            tmp={}
            db_conf_list=str(conf).split(' ')
            for i in db_conf_list:
                i_list=i.split('=')
                tmp[str(i_list[0])]=str(i_list[1])
            result_arr.append(tmp)
    return result_arr


def get_mms_mysql_conf(db='metric',master='1',weight='1'):
    mms_mysql_conf={}
    conf_arr=parse_mysql_conf()
    for i in conf_arr:
        if db==i['db'] and str(master)==str(i['master']) and str(weight)==str(i['weight']):
            mms_mysql_conf['host']=i['host']
            mms_mysql_conf['port']=i['port']
            mms_mysql_conf['user']=i['user']
            mms_mysql_conf['passwd']=i['pass']
            mms_mysql_conf['database']=i['db']
            return mms_mysql_conf
    return mms_mysql_conf
if __name__=='__main__':
    print get_mms_mysql_conf()
    ''.strip()