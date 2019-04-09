#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-

import os
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import env as conf
from mms_conf import MmsConf
import datetime
import env as Conf


def upgrade_mysql_weight():
    id_list= []
    mms_conf = MmsConf()
    con = mms_conf.connRead
    read_cur = mms_conf.curRead
    sel_sql = 'select id,update_weight_time from mms_conf where mysql_weight=2'
    read_cur.execute(sel_sql)
    result = read_cur.fetchall()
    for item in result:
        id = item[0]
        update_time = item[1]
        now = datetime.datetime.now()
        delta = now-update_time

        if delta.days >= Conf.UPGRADE_MYSQL_REQUEST_DATE_DELTA:
            id_list.append(str(id))

    if len(id_list) != 0:
        ids = ','.join(id_list)
        #升级mysql_weight=1
        upgrade_log = '%s days gap found from last update, change back mysql weight to 1' % (Conf.UPGRADE_MYSQL_REQUEST_DATE_DELTA)
        update_sql = "update mms_conf set mysql_weight=1, update_weight_time=now(), weight_update_log='%s' where id in (%s)" % (upgrade_log, ids)
        write_con = mms_conf.connWrite
        write_cur = mms_conf.curWrite
        write_cur.execute(update_sql)
        write_con.commit()

if __name__ == '__main__':
    upgrade_mysql_weight()
