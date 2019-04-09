#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import MySQLdb
import sys
import mms_mysql_conf as mmsMysqlConf
import mms_tunnel as mmsTunnel
from log4me import MyLogger
logger = MyLogger.getLogger()

class MmsMysql(object):

    CURSOR_MODE = 0
    DICTCURSOR_MODE = 1
    SSCURSOR_MODE = 2
    SSDICTCURSOR_MODE = 3

    def __init__(self,mysql_conf=None,tunnel_conf=None):
        if not mysql_conf:
            mysql_conf=mmsMysqlConf.MMS_DB_DATA_SLAVE
        if not tunnel_conf:
            tunnel_conf=mmsMysqlConf.MMS_DB_TUNNEL
        #开启隧道 1 open
        if mmsMysqlConf.TUNNEL_SWITCH==1:
            tunnel_key='%s:%s'%(str(mysql_conf['host']),str(mysql_conf['port']))
            tunnels=mmsTunnel.get_tunnels()
            tunnel_port=-1
            if tunnel_key in tunnels:
                tunnel_port=tunnels[tunnel_key]
            else:
                tunnel_port=mmsTunnel.get_open_port()
                mmsTunnel.construct_tunnel(mmsMysqlConf.MMS_DB_TUNNEL['username'],
                                        tunnel_port,
                                        mysql_conf['host'],
                                        mysql_conf['port'])
            self.host=tunnel_conf['host']
            self.port=tunnel_port
            self.user=tunnel_conf['user']
            self.passwd=tunnel_conf['passwd']
            self.db=mysql_conf['database']

        else:
            self.host=mysql_conf['host']
            self.port=mysql_conf['port']
            self.user=mysql_conf['user']
            self.passwd=mysql_conf['passwd']
            self.db=mysql_conf['database']

        self.conn=MySQLdb.connect(host=self.host,
                                port=int(self.port),
                                user=self.user,
                                passwd=self.passwd,
                                db=self.db,charset='utf8')

    def get_cur(self, mode=CURSOR_MODE):
        if mode == self.CURSOR_MODE :
            curclass = MySQLdb.cursors.Cursor
        elif mode == self.DICTCURSOR_MODE :
            curclass = MySQLdb.cursors.DictCursor
        elif mode == self.SSCURSOR_MODE :
            curclass = MySQLdb.cursors.SSCursor
        elif mode == self.SSDICTCURSOR_MODE :
            curclass = MySQLdb.cursors.SSDictCursor
        else :
            raise Exception("mode value is wrong")
        return self.get_conn().cursor(cursorclass=curclass)

    def get_conn(self):
        if self.conn and self.conn.open:
            return self.conn
        else:
            self.conn=MySQLdb.connect(host=self.host,
                                port=int(self.port),
                                user=self.user,
                                passwd=self.passwd,
                                db=self.db,charset='utf8')
            return self.conn



    def conn_close(self):
        if self.conn and self.conn.open:
            self.conn.close()


    def checkMysqlSynchDelay(self,delay_second=0):
        '''
        延迟返回True 正常Flase
        db_name:查询延迟数据库
        weight:数据库weight值
        delay_second:延迟秒
        '''
        try:

            sql='show slave status'
            data_slave_cur=self.get_cur()
            data_slave_conn=self.get_conn()
            data_slave_cur.execute(sql)
            columns = data_slave_cur.description
            slave_res = data_slave_cur.fetchone()
            data_slave_conn.close()
            if not slave_res:
                return False
            second_behind=0
            for (index,column) in enumerate(slave_res):
                if columns[index][0]=='Seconds_Behind_Master':
                    second_behind=column
                    break
            if int(second_behind)==0:
                return False
            elif int(second_behind)>=int(delay_second):
                logger.warn('check database {} master-slave synch delay {} s'.format(self.db,str(second_behind)))
                return True
        except:
            logger.exception('check database {} master-slave exception'.format(self.db))
            return False

if __name__=="__main__":
    mysql = MmsMysql(mmsMysqlConf.MMS_DB_META)
    cur=mysql.get_cur()
    # conn=MySQLdb.connect(host='127.0.0.1', port=3306, user='root',
    #                                     passwd='', db='metric', charset='utf8')
    # cur=conn.cursor()dolphin
    sql="show tables"
    cur.execute(sql)
    for row in cur:
        print row
