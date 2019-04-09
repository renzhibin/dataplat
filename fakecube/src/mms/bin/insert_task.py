#!/usr/bin/env python2.7
#coding=utf-8
import argparse
import time
import os
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
import env as conf
import mms_mysql_conf as mmsMysqlConf
from utils import insertTask, getRunTaskList
from mms_mysql import MmsMysql
from task_conf import *
from log4me import MyLogger
from getHqlByReportId import *

logger = MyLogger.getLogger()

class RunTask(object):

    def __init__(self,date, test, config, module, sdate, edate, step, dependTable, level2Menu, reportid,flag):
        self.date= date
        self.test = test
        self.config = config
        self.module = module
        self.sdate = sdate
        self.edate = edate
        self.step = step
        self.dependTable = dependTable
        self.level2Menu = level2Menu
        self.reportid = reportid
        self.apps_list = []
        self.data_tables=[]
        self.schedule_maps = {}
        self.allhql2tables = {}
        if flag is None:
            self.flag= False
        else:
            self.flag = bool(int(flag))
        self.remove_tag = False
        self.getMysqlConnection()
        self.init()


    def init(self):
        #统一时间调度
        if self.date is None:
            now_time_tmp=time.time()
            self.date=time.strftime("%Y-%m-%d",time.localtime(now_time_tmp))
            self.now=time.mktime(time.strptime(self.date,"%Y-%m-%d"))
            self.stime=int(self.now)
            self.etime=int(self.now)
            self.remove_tag= False

        elif self.date:
            self.now=time.mktime(time.strptime(self.date,"%Y-%m-%d"))
            self.stime=int(self.now)
            self.etime=int(self.now)
            #重跑设置remove_tag=True
            self.remove_tag=True

        if self.sdate and self.edate:
            self.stime=int(time.mktime(time.strptime(self.sdate,"%Y-%m-%d")))
            self.etime=int(time.mktime(time.strptime(self.edate,"%Y-%m-%d")))
            self.remove_tag=True

        if self.config:
            self.apps_list = [{'appname':self.config,'priority':0}]

        else:
            sql='select id,date_s,date_e,date_n,creater,appname,create_time,priority,`explain`,cn_name,storetype,if(editor is not null,editor,creater) editor from mms_conf order by priority desc';
            self.cur.execute(sql)

            columns = self.cur.description

            for value in self.cur.fetchall() :
                tmp={}

                for (index,column) in enumerate(value):
                    tmp[columns[index][0]] = column
                if  tmp['date_s'] is not None and time.mktime(tmp['date_s'].timetuple()) > self.now  :
                    logger.info('%s not start yet' %tmp['appname'])
                    continue
                if  tmp['date_e'] is not None and time.mktime(tmp['date_e'].timetuple()) < self.now :
                    logger.info('%s already end' %tmp['appname'])
                    continue
                logger.info('%s enter in queue' %(tmp['appname']))
                self.apps_list.append(tmp)

        #是否为测试
        self.test = True if self.test else False

        self.step = self.step.strip()

        #初始状态参数 如果只执行mysql 则为2 ready 状态 否则1:waiting状态
        self.status = conf.READY if self.step == 'mysql' else 1

        if self.dependTable is not None:
            print 'please waiting, collecting the schedule task table and hql info now'
            #需要跑下游数据时， 获取一份数据表与任务依赖的关系映射
            schedule_tables = getScheduleTaskTables()
            self.allhql2tables= getAllRunHql2Table(self.apps_list)

            for table in schedule_tables:
                tasks= getTasksDependsOnDataTable(table, self.allhql2tables)
                self.schedule_maps[table] = tasks


    def registerTask(self):
        if self.level2Menu or self.reportid:
            if self.level2Menu:
                hqlList=getHqlBySecondMenuId(self.level2Menu)
            elif self.reportid:
                hqlList=getHqlByTableId(self.reportid)

            app_module_dict = {}
            for hql in hqlList:
                pl = hql.split('.')
                app_name = pl[0]
                run_module='.'.join(pl[1:])
                if app_module_dict.has_key(app_name):
                    app_module_dict[app_name].append(run_module)
                else:
                    app_module_dict[app_name] = [run_module]

            start_datetime=time.strftime('%Y-%m-%d %H:%M',time.localtime(self.stime))
            end_datetime=time.strftime('%Y-%m-%d %H:%M',time.localtime(self.etime))
            for app_name, run_module_list in app_module_dict.iteritems():
                template = getRunTaskList(app_name, run_module_list, start_datetime, end_datetime, 'all', 'bangzhongpeng')
                insertTask(template)
            return True

        else:#module,depend_table
            template=set()
            tasks=set()
            depend_tables = []
            if self.dependTable is not None:
                depend_tables = self.dependTable.split(',')

            for app in self.apps_list:
                for now_timestamp in range(self.etime,self.stime-86400,-86400):
                    now_date=time.strftime("%Y-%m-%d",time.localtime(now_timestamp))
                    app_name=app['appname']
                    if self.module:
                        modules = self.module.split(',')
                        hqlList=getHqlList(app_name,self.data_tables, depend_tables,now_date,modules, self.flag, self.allhql2tables, self.schedule_maps)
                    else:
                        hqlList=getHqlList(app_name,self.data_tables, depend_tables,now_date, None, self.flag, self.allhql2tables, self.schedule_maps)

                    #print module_arr
                    for m in hqlList:
                        task_run_name = m.category+"."+m.hql
                        # template.append((app_name,now_date,status,m.strip(),step,test,app['priority']))
                        template.add((m.app_name,m.stat_time,self.status,task_run_name,self.step,self.test,m.priority,m.schedule_level,None, m.editor,'inf',m.sec_date))
                        tasks.add(m)

            #删除调度任务tag
            for t in tasks:
                print t
                if self.remove_tag:
                    removeTag(t)

            insertTask(template)
            return True


    def closeConnetion(self):
        if self.conn.open:
            self.conn.close()

    def getMysqlConnection(self):

        #初始mysql链接

        self.mmsMysql = None
        try:
            self.mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
        except:
            import traceback
            traceback.format_exc()
            time.sleep(60)
            self.mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)

        self.conn=self.mmsMysql.get_conn()
        self.cur=self.mmsMysql.get_cur()



def getArgs():

    arg_parser = argparse.ArgumentParser()
    arg_parser.add_argument('-d', '--date', help='[optional] Which date\'s data are going to be calculated. And the format must be "YYYY-MM-DD".', required=False)
    arg_parser.add_argument('-t', '--test', action='store_true', help='[Optional] Will use test hql to execute. But the HQL generator may not implement a test HQL.',required=False)
    arg_parser.add_argument('-c', '--config', help='[optional] Which config".', required=False)
    arg_parser.add_argument('-m', '--module',help='[optional] Which module to run',required=False)
    arg_parser.add_argument('-s', '--sdate', help='[optional] Which start date".', required=False)
    arg_parser.add_argument('-e', '--edate', help='[optional] Which end date".', required=False)
    arg_parser.add_argument('-a', '--step', default='all', choices = ["all","hive","mysql"], help='[Optional] all,hive,mysql', required=False)
    arg_parser.add_argument('-b', '--table',help='[Optional] hive table', required=False)
    arg_parser.add_argument('-smid', '--secondMenuId', help='[help]二级菜单ID', required=False)
    arg_parser.add_argument('-id', '--tableId', help='[help]报表id', required=False)
    arg_parser.add_argument('-flag', '--flag', help='[help]0:基于依赖表的报表任务的上游任务, 1:基于依赖表的调度任务重跑依赖此表的报表类任务', required=False)

    args = arg_parser.parse_args()
    return args

def main():
    args = getArgs()
    task = RunTask(args.date, args.test, args.config, args.module, args.sdate, args.edate, args.step, args.table, args.secondMenuId, args.tableId, args.flag)
    task.registerTask()
    task.closeConnetion()

if __name__=='__main__':
    main()
