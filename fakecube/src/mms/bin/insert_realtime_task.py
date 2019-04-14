#!/usr/bin/env python2.7
#coding=utf-8
import argparse
import datetime
import time
import os
import json
import sys
reload(sys)
os.environ['TZ'] = 'Asia/Shanghai'
time.tzset()
sys.setdefaultencoding('utf8')
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
from task_conf import *
from log4me import MyLogger

logger = MyLogger.getLogger()

class RunTask(object):

    def __init__(self):
        self.__get_mysql_connection()
        #调度时间
        self.now_time_stamp = time.time()
        self.now_date_time = datetime.datetime.now()
        self.dispatch_minute = int(time.strftime("%M",time.localtime(self.now_time_stamp)))
        self.dispatch_date_time = time.strftime("%Y-%m-%d %H:%M:00",time.localtime(self.now_time_stamp))
        #当前日期
        self.date = time.strftime("%Y-%m-%d",time.localtime(self.now_time_stamp))

    def execute(self):
        run_app_list = self.__get_run_app_list()
        for value in run_app_list:
            self.__insert_app(value)
        self.__close_connection()

    def __get_run_app_list(self):
        run_app_list = []
        get_run_list_sql = '''SELECT date_s, date_e, app_name, editor, priority, conf
                                    , category_name, hql_name, other_params app_conf
                                FROM mms_realtime_conf
                                    JOIN mms_realtime_app_conf ON mms_realtime_conf.appname = mms_realtime_app_conf.app_name
                                WHERE is_delete = 0
                                    AND ((date_s <= '%s')
                                        OR (date_s IS NULL)
                                        OR (date_s = '0000-00-00 00:00:00'))
                                    AND ((date_e >= '%s')
                                        OR (date_e IS NULL)
                                        OR (date_e = '0000-00-00 00:00:00'));''' % (self.date, self.date)
        self.cur.execute(get_run_list_sql)
        columns_desc = self.cur.description
        for value in self.cur.fetchall():
            row = {}
            for (index, column) in enumerate(value):
                row[columns_desc[index][0]] = column
            conf = json.loads(row['conf'])
            app_conf = json.loads(row['app_conf'])
            if not self.__is_set_run(conf, row['category_name'] + '.' + row['hql_name']):
                continue
            if not self._is_schedule(app_conf):
                continue
            row['stat_date'] = self.__get_schedule_time(app_conf)
            run_app_list.append(row)
        return run_app_list

    def __insert_app(self, app):
        insert_sql = '''insert into mms_realtime_run_log(app_name,stat_date,status,run_module,priority,schedule_level,submitter) 
                        values('%s', '%s', %s, '%s', %s, '%s', '%s') '''
        insert_sql = insert_sql % (
            app['app_name'], app['stat_date'], 1, app['category_name'] + '.' + app['hql_name'], app['priority'], 'realtime', app['editor'])
        self.cur.execute(insert_sql)
        self.conn.commit()

    def __get_schedule_time(self, app_conf):
        delta = datetime.timedelta(minutes=0 - int(app_conf['schedule_interval']) + 1)
        n_minutes = self.now_date_time + delta
        return n_minutes.strftime('%Y-%m-%d %H:%M:00')

    def __is_set_run(self,conf, run_module):
        for value in conf['run']['run_instance']['group']:
            if run_module == value['name']:
                return True
        return False

    def _is_schedule(self, app_conf):
        schedule_interval = app_conf['schedule_interval']
        #提前一分钟插入
        if (int(self.dispatch_minute) + 1) % int(schedule_interval) == 0:
            return True
        return False

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
