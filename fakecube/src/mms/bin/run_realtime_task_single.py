#!/usr/bin/env python2.7
#coding=utf-8
import argparse
import time
import os
import sys
reload(sys)
sys.setdefaultencoding('utf8')
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import env as conf
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
from task_conf import *
from log4me import MyLogger
from monitor_realtime_task import MonitorTask
import logging
import json
import redis
import re
import datetime

class RunTask(object):

    def __init__(self, queue_id):
        self.queue_id = queue_id
        self.__get_mysql_connection()
        self.conf = self.__get_run_conf()
        self.app_conf = self.__get_app_conf()
        #调度时间
        self.max_run_count = 20
        self.check_readytime_timeout = 600  # 依赖检测运行时长超时（秒）
        #日志路径处理
        self.logger = MyLogger.getLogger()
        stat_time_day = datetime.datetime.strptime(self.conf['stat_date'], '%Y-%m-%d %H:%M:%S')
        stat_time_day = stat_time_day.strftime('%Y-%m-%d')
        conf.LOG_PATH += self.conf['app_name'] + '/' + stat_time_day
        if not os.path.exists(conf.LOG_PATH):
            os.makedirs(conf.LOG_PATH)
        log_file = '%s/run_%s_%s_%s.log' % (
        conf.LOG_PATH, self.conf['stat_date'], self.conf['run_module'], self.queue_id)
        for handler in self.logger.handlers:
            if isinstance(handler, logging.FileHandler):
                handler.__init__(log_file)
                handler.setFormatter(conf.LOG_FORMAT)

    def execute(self):
        monitor_task = MonitorTask()
        try:
            self.__update_job_status(conf.CHECKING)
            self.logger.info('Job exec start.')
            is_run = True
            #if self.__is_last_unfinished():
            #   is_run = False
            if self.__is_reach_maximum_running_count():
                is_run = False
            if not is_run:
                self.__update_job_status(conf.WAITING, False)
                self.logger.info('Job exec end.')
                return
            self.logger.info('Job check rely start.')
            if not self.__check_rely():
                self.__update_job_status(conf.WAITING, True)
                self.logger.info('Job check rely end.')
                if not self.__is_readytime_timeout():
                    pass
                else:
                    self.__update_job_status(conf.FAILED)
                    phone_num = monitor_task.get_user_phone(self.conf['submitter'])
                    alarm_content = '''[dt实时］项目名称：%s 任务名称：%s 执行时间点：%s 依赖检测超时状态已置为失败请注意!'''
                    alarm_content = alarm_content % (
                        self.conf['app_name'], self.conf['run_module'], self.conf['stat_date'])
                    monitor_task.send_sms(phone_num, alarm_content, True)
                    self.logger.info('Job check rely timeout.')
                self.logger.info('Job exec end.')
                return
            self.__update_job_status(conf.READY)
            self.__update_job_status(conf.RUNNING)
            status = self.__job_execute()
            self.__update_job_status(status)
            if status == conf.FAILED:
                phone_num = monitor_task.get_user_phone(self.conf['submitter'])
                alarm_content = '''[dt实时］项目名称：%s 任务名称：%s 执行时间点：%s 运行失败请注意!'''
                alarm_content = alarm_content % (
                self.conf['app_name'], self.conf['run_module'], self.conf['stat_date'])
                monitor_task.send_sms(phone_num, alarm_content, True)
            self.logger.info('Job exec end.')
        except:
            alarm_content = '''[dt实时］项目名称：%s 任务名称：%s 执行时间点：%s 运行异常请注意!'''
            alarm_content = alarm_content % (
                self.conf['app_name'], self.conf['run_module'], self.conf['stat_date'])
            self.logger.exception("runner exception occurred!")
            monitor_task.send_sms('18810502506', alarm_content, True)
        finally:
            self.__close_connection()

    def __is_readytime_timeout(self):
        now = time.time()
        stat_time_array = time.strptime(self.conf['stat_date'], '%Y-%m-%d %H:%M:%S')
        stat_time = time.mktime(stat_time_array)
        return (now - stat_time) >= self.check_readytime_timeout

    def __job_execute(self):
        app_conf = json.loads(self.app_conf['other_params'])
        source_result = self.__get_source_result(app_conf)
        if source_result == conf.FAILED:
            return conf.FAILED

        if self.__has_create_table_in_db(app_conf):
            self.logger.info('Table has been created in target db.')
        else:
            self.logger.info('Need created table in target db.')
            self.__create_table_in_db(app_conf)
            self.logger.info('Creat table success in target db.')

        if len(source_result) == 0:
            self.logger.info('The source execution result is empty.')
            return conf.WARNING
        self.logger.info('Get source data count ' + str(len(source_result)) + '.')
        if not self.__insert_data_2_target_table(app_conf, source_result):
            return conf.FAILED
        self.__create_redis_tag(app_conf)
        self.logger.info('Job exec success.')
        return conf.SUCCESS

    def __has_create_table_in_db(self, app_conf):
        target_db = app_conf['target_db']
        target_table = app_conf['target_table']

        if not self.__get_target_mysql_connection(target_db):
           self.logger.error('Get target mysql connection fail.')
           return False

        try:
            sql = "show tables like '%s'" % (target_table)
            self.logger.info('Check table sql: [ %s; ].' % sql)
            self.target_cur.execute(sql)
            test_data =  self.target_cur.fetchall()
            if test_data:
                return True
            else:
                return False
        except Exception, e:
            self.logger.info('Check target %s table error.' % target_table)
            return False

    def __create_table_in_db(self, app_conf):
        target_db = app_conf['target_db']
        target_table = app_conf['target_table']

        if not self.__get_target_mysql_connection(target_db):
           self.logger.error('Get target mysql connection fail.')
           return False

        metrics = app_conf['metrics']
        dimensions = app_conf['dimensions']
        metric_sql = ''''''
        dimension_sql = ''''''

        for metric in metrics:
            current_name = metric['name']
            current_type = metric['type']
            current_cn_name = metric['cn_name']

            if current_type == 'decimal':
                metric_sql += "        `%s` decimal(64,2) DEFAULT NULL COMMENT '%s',\n" % (current_name, current_cn_name)
            elif current_type == 'varchar':
                metric_sql += "        `%s` varchar(100) DEFAULT NULL COMMENT '%s',\n" % (current_name, current_cn_name)
            elif current_type == 'varchar200':
                metric_sql += "        `%s` varchar(200) DEFAULT NULL COMMENT '%s',\n" % (current_name, current_cn_name)
            elif current_type == 'varchar1024':
                metric_sql += "        `%s` varchar(1024) DEFAULT NULL COMMENT '%s',\n" % (current_name, current_cn_name)
            else:
                metric_sql += "        `%s` varchar(2000) DEFAULT NULL COMMENT '%s',\n" % (current_name, current_cn_name)

        for dimension in dimensions:
            current_name = dimension['name']
            current_type = dimension['type']
            current_cn_name = dimension['cn_name']

            if current_type == 'decimal':
                dimension_sql += "        `%s` decimal(64,2) DEFAULT NULL COMMENT '%s',\n" % (current_name, current_cn_name)
            elif current_type == 'varchar':
                dimension_sql += "        `%s` varchar(100) DEFAULT NULL COMMENT '%s',\n" % (current_name, current_cn_name)
            elif current_type == 'varchar200':
                dimension_sql += "        `%s` varchar(200) DEFAULT NULL COMMENT '%s',\n" % (current_name, current_cn_name)
            elif current_type == 'varchar1024':
                dimension_sql += "        `%s` varchar(1024) DEFAULT NULL COMMENT '%s',\n" % (current_name, current_cn_name)
            else:
                dimension_sql += "        `%s` varchar(2000) DEFAULT NULL COMMENT '%s',\n" % (current_name, current_cn_name)

        create_sql = '''
        CREATE TABLE IF NOT EXISTS `%s` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
        `archive_datetime` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '归档时间',
        \n%s
        \n%s
        PRIMARY KEY (`id`),
        KEY `idx_archive_datetime` (`archive_datetime`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'%s\'''' % (target_table, dimension_sql, metric_sql, target_table)

        self.logger.info('Create table sql: [ %s; ].' % create_sql)

        self.target_cur.execute(create_sql)
        self.target_conn.commit()
        return True

    def __insert_data_2_target_table(self, app_conf, source_result):
        if not self.__get_target_mysql_connection(app_conf['target_db']):
            self.logger.error('Get target mysql connection fail.')
            return False
        columns = self.__get_target_table_stored_column(app_conf['target_table'])
        if not columns:
            return False
        count = 0
        try:
            delete_sql = "delete from %s where archive_datetime='%s'" % (app_conf['target_table'], self.conf['stat_date'])
            self.target_cur.execute(delete_sql)
            self.target_conn.commit()
            self.logger.info('Delete old Data success.')
            for row in source_result:
                values = self.__format_list_4_db_store_2_str(row)
                values = "'%s',%s" % (self.conf['stat_date'], values)
                insert_columns = 'archive_datetime,' + ','.join(columns)
                sql = '''insert into %s(%s) values(%s)''' % (app_conf['target_table'], insert_columns, values)
                self.target_cur.execute(sql)
                self.target_conn.commit()
                count = count + 1
        except Exception,e:
            self.logger.error('Insert table ' + app_conf['target_table'] + ' error :' + str(e) + '.')
            return False
        finally:
            self.__close_target_connection()
        self.logger.info('Insert sucess count: ' + str(count) + '.')
        return True

    def __get_target_table_stored_column(self, source_table):
        columns = []
        try:
            sql = 'SHOW COLUMNS FROM %s' % (source_table)
            self.target_cur.execute(sql)
            for value in self.target_cur.fetchall():
                if value[0] in ['id', 'updated_at', 'created_at', 'archive_datetime']:
                    continue
                columns.append(value[0])
        except Exception,e:
            self.logger.info('Get target table columns fail, error:' + str(e) + '.')
            return False
        return columns

    def __get_source_result(self, app_conf):
        source_db = app_conf['source_db']
        sql = self.__get_execute_sql(app_conf)
        self.logger.info('Get source data sql ' + sql + '.')
        if not self.__get_source_mysql_connection(source_db):
            self.logger.error('Get source mysql connection fail.')
            return conf.FAILED
        result = []
        try:
            self.source_cur.execute(sql)
            for value in self.source_cur.fetchall():
                result.append(value)
        except Exception,e:
            self.logger.error('Get source data fail, error :' + str(e) + '.')
            return conf.FAILED
        finally:
            self.__close_source_connection()
        return result

    def __get_execute_sql(self, app_conf):
        sql = app_conf['hql']
        regexp = r"\$MINUTE\((.*?)\)"
        r = re.findall(regexp, sql)
        if len(r) == 0:
            return sql
        stat_date = self.conf['stat_date']
        stat_date_stamp = datetime.datetime.strptime(stat_date, '%Y-%m-%d %H:%M:%S')
        for value in r:
            delta = datetime.timedelta(minutes=int(value))
            n_time = stat_date_stamp + delta
            calculate_time = n_time.strftime('%Y-%m-%d %H:%M:%S')
            sql = sql.replace('$MINUTE(' + value + ')', calculate_time)
        return sql

    def __check_rely(self):
        app_conf = json.loads(self.app_conf['other_params'])
        source_db = app_conf['source_db']
        for table in app_conf['tables']:
            if int(table['ischecktables']) == 0 or int(table['ischecktables']) == -1:
                continue
            table_name = source_db + '.' + table['name']
            if not self.__check_redis_tag(table_name):
                self.logger.info(table_name + ' is not ready.')
                return False
            self.logger.info(table_name + ' is ready.')
        self.logger.info('All tables are ready.')
        return True

    def __create_redis_tag(self, app_conf):
        table_name = app_conf['target_db'] + '.' + app_conf['target_table']
        tag = self.__get_redis_tag(table_name)
        try:
            r = redis.Redis(
                host=conf.REALTIME_REDIS_TAG_REDIS_HOST, password=conf.REALTIME_REDIS_TAG_REDIS_PASSWORD,
                port=conf.REALTIME_REDIS_TAG_REDIS_PORT, db=conf.REALTIME_REDIS_TAG_REDIS_DB)
            r.set(tag, 1, 864000) #存储10天
            return True
        except Exception, e:
            self.logger.info('Set redis data fail, error :' + str(e) + '.')
            return False

    def __check_redis_tag(self, table_name):
        tag = self.__get_redis_tag(table_name)
        try:
            r = redis.Redis(
                host=conf.REALTIME_REDIS_TAG_REDIS_HOST, password=conf.REALTIME_REDIS_TAG_REDIS_PASSWORD,
                port=conf.REALTIME_REDIS_TAG_REDIS_PORT, db=conf.REALTIME_REDIS_TAG_REDIS_DB)
            tag_value = r.get(tag)
            if tag_value != '1':
                return False
            return True
        except Exception,e:
            self.logger.info('Get redis data fail, error :' + str(e) + '.')
            return False

    def __get_redis_tag(self, table_name):
        tag = 'dt_real_time:table_tag:' + table_name + ':' + self.conf['stat_date']
        return tag

    def __get_app_conf(self):
        run_module = self.conf['run_module'].split('.')
        category_name = run_module[0]
        hql_name = run_module[1]
        sql = '''select * from mms_realtime_app_conf where app_name="%s" and category_name="%s" and hql_name="%s" ''' % \
              (self.conf['app_name'], category_name, hql_name)
        self.cur.execute(sql)
        columns_desc = self.cur.description
        row = {}
        for value in self.cur.fetchall():
            row = {}
            for (index, column) in enumerate(value):
                row[columns_desc[index][0]] = column
            return row
        return row

    def __is_last_unfinished(self):
        last_running_count = self.__get_last_running_count()
        if last_running_count > 0:
            self.logger.info('Job will run later,because last is unfinished.')
            return True
        return False


    def __is_reach_maximum_running_count(self):
        running_count = self.__get_current_running_count()
        if (running_count >= self.max_run_count):
            self.logger.info(
                'Job will run later,because current running job count exceed the maximum value.Current running count : ' + str(
                    running_count) + ',maximum value : ' + str(self.max_run_count))
            return True
        return False

    def __get_current_running_count(self):
        sql = '''select count(0) as count from mms_realtime_run_log where status=%s ''' % (
        conf.RUNNING)
        self.cur.execute(sql)
        count = self.cur.fetchone()
        return count[0]

    def __get_last_running_count(self):
        sql = '''select count(1) as count from mms_realtime_run_log where status=%s and app_name="%s" and run_module="%s" ''' % (conf.RUNNING, self.conf['app_name'], self.conf['run_module'])
        self.cur.execute(sql)
        count = self.cur.fetchone()
        return count[0]


    def __get_run_conf(self):
        result_list = []
        sql = '''select * from mms_realtime_run_log where id=%s ''' % (self.queue_id)
        self.cur.execute(sql)
        columns_desc = self.cur.description
        row = {}
        for value in self.cur.fetchall():
            for (index, column) in enumerate(value):
                row[columns_desc[index][0]] = column
            return row
        return row

    def __get_check_app_list(self):
        check_app_list = []
        get_check_list_sql = '''select * from mms_realtime_run_log where status=1 order by priority desc,last_checked_time asc limit %s''' % (self.max_check_count)
        self.cur.execute(get_check_list_sql)
        columns_desc = self.cur.description
        for value in self.cur.fetchall():
            row = {}
            for (index, column) in enumerate(value):
                row[columns_desc[index][0]] = column
                check_app_list.append(row)
        return check_app_list

    def __update_job_status(self, status, checked=False):
        try:
            sql = "update mms_realtime_run_log set status = %s"
            if status == conf.RUNNING:
                sql += ',start_time = now()'
            elif status in (conf.SUCCESS, conf.FAILED, conf.WARNING):
                sql += ',end_time = now()'
            elif status == conf.READY:
                sql += ' ,ready_time= now()'
            elif status == conf.WAITING:
                if checked:
                    sql += ' ,last_checked_time= now()'
            sql += ' where id = %s'
            sql = sql % (status, self.conf['id'])
            # 防止手动杀死任务，状态再改变
            if status in (conf.READY, conf.WAITING):
                sql += ' and status!=' + str(conf.KILLED)
            self.cur.execute(sql)
            self.conn.commit()
        except:
            self.logger.exception("Update job status fail.")
            sys.exit(-1)

    def __format_list_4_db_store_2_str(self, list):
        result = ''
        for value in list:
            if value in [None]:
                result = result + 'Null,'
                continue
            result = result + "'" + '%s' % (value) + "',"
        result = result.strip(',')
        return result

    def __close_connection(self):
        if self.conn.open:
            self.conn.close()

    def __close_source_connection(self):
        if self.source_conn.open:
            self.source_conn.close()

    def __close_target_connection(self):
        if self.target_conn.open:
            self.target_conn.close()

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

    def __get_source_mysql_connection(self, source):
        # 初始mysql链接
        self.mysql_source_connection = None
        try:
            source_db_config = mmsMysqlConf.MMS_DB_REAL_TIME_SOURCE[source]
            self.mysql_source_connection = MmsMysql(source_db_config)
        except Exception,e:
            import traceback
            traceback.format_exc()
            return False
        self.source_conn = self.mysql_source_connection.get_conn()
        self.source_cur = self.mysql_source_connection.get_cur()
        return True

    def __get_target_mysql_connection(self, target):
        # 初始mysql链接
        self.mysql_target_connection = None
        try:
            target_db_config = mmsMysqlConf.MMS_DB_REAL_TIME_TARGET[target]
            self.mysql_target_connection = MmsMysql(target_db_config)
        except:
            import traceback
            traceback.format_exc()
            return False
        self.target_conn = self.mysql_target_connection.get_conn()
        self.target_cur = self.mysql_target_connection.get_cur()
        return True

def get_args():
    arg_parser = argparse.ArgumentParser()
    arg_parser.add_argument('-l', '--log', help='[help]run queue id', required=True)
    args = arg_parser.parse_args()
    return args

def main():
    cmd_args = get_args()
    queue_id = cmd_args.log
    task = RunTask(queue_id)
    task.execute()

if __name__ == '__main__':
    main()
