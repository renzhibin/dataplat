#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
import env

import os
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
HOME_PATH = os.path.dirname(HOME_PATH)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'lib'))
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'bin'))

#是否开启隧道
TUNNEL_SWITCH=2#1open 2close



#存储meta DB

MMS_DB_META={
    'host':'10.3.0.159',
    'port':4307,
    'user':'metric_meta_w',
    'passwd':'9edb35c8f48901d051aaf3a5820a3398',
    'database':'metric_meta'
}


MMS_DB_META_SLAVE={
    'host':'10.3.0.160',
    'port':4307,
    'user':'metric_meta_r',
    'passwd':'3a394ad560ab74fc51c9a58327be9edb',
    'database':'metric_meta'


}


MMS_DB_LOCAL={
    'host':'127.0.0.1',
    'port':3332,
    'user':'root',
    'passwd':'',
    'database':'metric'
}

#存储数据DB
'''
MMS_DB_DATA={
    'host':'192.168.128.18',
    'port':4306,
    'user':'dolphin',
    'passwd':'dolphin',
    'database':'metric'
}

'''
MMS_DB_DATA={
    'host':'10.3.0.159',
    'port':4307,
    'user':'metric_meta_w',
    'passwd':'9edb35c8f48901d051aaf3a5820a3398',
    'database':'metric'
}

MMS_DB_DANDELION={
    'host':'172.16.12.199',
    'port':3332,
    'user':'mlswriter',
    'passwd':'mLsW#1^iPo16QPsd',
    'database':'dandelion'
}

MMS_DB_DATA_SLAVE={
    'host': '10.3.0.160',
    'port': 4307,
    'user': 'metric_meta_r',
    'passwd': '3a394ad560ab74fc51c9a58327be9edb',
    'database': 'metric'
}

MMS_DB_DATA_SLAVE_SECONDARY={
    'host': '10.3.0.160',
    'port': 4307,
    'user': 'metric_meta_r',
    'passwd': '3a394ad560ab74fc51c9a58327be9edb',
    'database': 'metric'
}

MMS_DB_TUNNEL={

    'host':'127.0.0.1',
    'user':'mlswriter',#mlsreader
    'passwd':'mLsW#1^iPo16QPsd',#RMlSxs&^c6OpIAQ1
    'username':'bangzhongpeng'

}


#meiliwork,dbreader 替换为mlswriter,mlsreader  密码需要读配置文件
# from get_mms_mysql_conf import get_mms_mysql_conf
# MMS_DB_META=get_mms_mysql_conf('metric_meta','1')
# MMS_DB_META_SLAVE=get_mms_mysql_conf('metric_meta','0')
# MMS_DB_DATA_SLAVE=get_mms_mysql_conf('metric','0')
# MMS_DB_DATA_SLAVE_SECONDARY=get_mms_mysql_conf('metric','0')
# MMS_DB_DANDELION=get_mms_mysql_conf('dandelion','1')
