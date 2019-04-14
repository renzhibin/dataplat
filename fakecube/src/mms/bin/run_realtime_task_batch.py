#!/usr/bin/env python2.7
#coding=utf-8
import argparse
import time
import os
import sys
reload(sys)
os.environ['TZ'] = 'Asia/Shanghai'
time.tzset()
sys.setdefaultencoding('utf8')
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import mms_mysql_conf as mmsMysqlConf
import env as conf
from mms_mysql import MmsMysql
from task_conf import *
from log4me import MyLogger

logger = MyLogger.getLogger()

class RunTask(object):

    def __init__(self):
        self.__get_mysql_connection()
        #调度时间
        self.max_check_count = 20
        self.logger = MyLogger.getLogger()

    def execute(self):
        try:
            check_app_list = self.__get_check_app_list()
            for value in check_app_list:
                cmd = "nohup python %s/run_realtime_task_single.py -l %s > /dev/null 2>&1 &  " % (conf.BIN_PATH, value['id'])
                ret = os.system(cmd)
                msg = "app:%s,module:%s,stat_date:%s for queue id:%s" % (value['app_name'], value['run_module'], value['stat_date'], value['id'])
                if ret:
                    self.logger.error('%s failed!' % msg)
                else:
                    self.logger.info('%s start!' % msg)
        except:
            self.logger.exception("runner exception occurred!")
            self.__close_connection()

    def __get_check_app_list(self):
        check_app_list = []
        now_time_minute = time.strftime('%Y-%m-%d %H:%M:00',time.localtime(time.time()))
        get_check_list_sql = """select id,app_name,run_module,stat_date from mms_realtime_run_log where status=1 and stat_date <= now() and create_time < '%s' order by priority desc,last_checked_time asc limit %s """ % (now_time_minute, self.max_check_count)
        self.cur.execute(get_check_list_sql)
        columns_desc = self.cur.description
        for value in self.cur.fetchall():
            row = {}
            for (index, column) in enumerate(value):
                row[columns_desc[index][0]] = column
            check_app_list.append(row)
        return check_app_list

    def __close_connection(self):
        if self.conn.open:
            self.conn.close()

    def __get_mysql_connection(self):
        #初始mysql链接
        self.mysql_connection = None
        try:
            self.mysql_connection = MmsMysql(mmsMysqlConf.MMS_DB_META)
        except:
            import traceback
            traceback.format_exc()
            time.sleep(60)
            self.mysql_connection = MmsMysql(mmsMysqlConf.MMS_DB_META)

        self.conn = self.mysql_connection.get_conn()
        self.cur = self.mysql_connection.get_cur()


def get_args():
    arg_parser = argparse.ArgumentParser()
    args = arg_parser.parse_args()
    return args


def main():
    task = RunTask()
    task.execute()

if __name__ == '__main__':
    main()
