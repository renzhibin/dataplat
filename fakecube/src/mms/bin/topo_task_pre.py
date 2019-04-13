# -*- coding:utf8 -*-
"""
    @author: pengbangzhong
    @file: topo_task_pre.py
    @time: 2017/1/19 下午2:15 
"""

import sys,os
reload(sys)
sys.setdefaultencoding('utf8')
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
HOME_PATH = os.path.dirname(cur_abs_dir)
os.sys.path.insert(0,'%s/%s' %(HOME_PATH,'conf'))
from mms_mysql_conf import MMS_DB_META
from mms_mysql import MmsMysql
import mms_mysql_conf as mmsMysqlConf
import MySQLdb,time,requests,re,argparse,urllib,phpserialize
import simplejson as json
import app_conf as appObj

class TopoTask():

    def refresh_status(self):
        now = time.strftime('%Y-%m-%d', time.localtime(time.time()))
        start_time='{} 00:00:00'.format(now)
        end_time='{} 23:59:59'.format(now)
        # self.reset_task_status()
        params = self.data_task_status(start_time,end_time)
        #params += self.datax_task_status(start_time,end_time)
        #params += self.log_task_status(start_time,end_time)

        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'], passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        sql = '''
            insert into  t_rely_task(task,status,data_size,creater,start_time,end_time,plat,update_time,is_vaild,schedule_level) values (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
            on duplicate key update
            status=values(status),
            data_size=values(data_size),
            creater=values(creater),
            start_time=values(start_time),
            end_time=values(end_time),
            plat=values(plat),
            update_time=values(update_time),
            is_vaild=values(is_vaild),
            schedule_level=values(schedule_level)
        '''
        cur = conn.cursor()
        cur.executemany(sql, params)
        conn.commit()
        params = self.get_table_task_status()
        sql = '''
            insert into  t_rely_task(task,status,data_size,creater,is_vaild) values (%s,%s,%s,%s,%s)
            on duplicate key update
            status=values(status),
            data_size=values(data_size),
            creater=values(creater),
            is_vaild=values(is_vaild)
        '''
        cur = conn.cursor()
        cur.executemany(sql, params)
        conn.commit()
        cur.close()

    def reset_task_status(self):
        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'],
                               passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        sql='''
            update t_rely_task set is_vaild=0
        '''
        cur = conn.cursor()
        cur.execute(sql)
        conn.commit()
        cur.close()

    def get_table_task_status(self):
        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'],
                               passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        sql = '''select cn_name,creater from t_visual_table where flag=1
                '''
        cur = conn.cursor(MySQLdb.cursors.DictCursor)
        cur.execute(sql)
        res = cur.fetchall()
        ret = []
        for n in res:
            tmp = (n['cn_name'], 5, 0, n['creater'], 1)
            ret.append(tmp)
        return ret

    def data_task_status(self,start_time,end_time):
        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'],
                               passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        now = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        sql = '''
            select concat(app_name,'.',run_module) task,status,data_size,submitter creater,start_time,end_time,schedule_level from mms_run_log t1
            join
            (
                select max(id) id from mms_run_log
                where
                create_time>='{start_time}'
                and create_time<='{end_time}'
                group by concat(app_name,'.',run_module),stat_date
            )t2
            on t1.id=t2.id
        '''.format(start_time=start_time,end_time=end_time)
        cur = conn.cursor(MySQLdb.cursors.DictCursor)
        cur.execute(sql)
        res = cur.fetchall()
        ret = []
        for n in res:
            tmp = (n['task'], n['status'], n['data_size'], n['creater'], n['start_time'], n['end_time'], 'data', now, 1, n['schedule_level'])
            ret.append(tmp)
        return ret

    def datax_task_status(self,start_time,end_time):
        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'],
                               passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        now = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        sql = '''
            select concat(t1.target_db,'.',t1.data_target) task,t1.status,t1.data_size,t1.creater,t1.start_time,t1.end_time from datax_run_log t1
            join
            (
                select max(id) id from datax_run_log
                where
                create_time>='{start_time}'
                and create_time<='{end_time}'
                and job_type like '%2hdfs'
                group by concat(target_db,'.',data_target),stat_date
            )t2
            on t1.id=t2.id

        '''.format(start_time=start_time,end_time=end_time)
        cur = conn.cursor(MySQLdb.cursors.DictCursor)
        cur.execute(sql)
        res = cur.fetchall()
        ret = []
        for n in res:
            tmp = (
            n['task'], n['status'], n['data_size'], n['creater'], n['start_time'], n['end_time'], 'datax', now, 1)
            ret.append(tmp)

        return ret

    def log_task_status(self,start_time,end_time):
        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'],
                               passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        now = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        sql = '''
            select concat(target_db,'.',target_table) task,status,success_size,creater,start_time,end_time from parse_run_log t1
            join
            (
                select max(id) id from parse_run_log
                where
                create_time>='{start_time}'
                and create_time<='{end_time}'
                group by concat(target_db,'.',target_table),stat_date
            )t2
            on t1.id=t2.id
        '''.format(start_time=start_time,end_time=end_time)
        cur = conn.cursor(MySQLdb.cursors.DictCursor)
        cur.execute(sql)
        res = cur.fetchall()
        ret = []
        for n in res:
            tmp = (
            n['task'], n['status'], n['success_size'], n['creater'], n['start_time'], n['end_time'], 'parse', now, 1)
            ret.append(tmp)

        return ret

    #刷新任务关系
    def refresh_task_link(self):
        values=self.list_plat_app()
        #values+=self.list_datax_app()
        #values+=self.list_pase_log()

        self.truncate_task_link()
        self.save(values)
        values=self.get_all_online_table_task()
        self.save(values, True)

    def truncate_task_link(self):
        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'],
                               passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        sql='''
            truncate table t_rely_topo
        '''
        cur=conn.cursor()
        cur.execute(sql)
        conn.commit()
        cur.close()

    def save(self,values, is_table=False):
        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'],
                               passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        sql = "insert into t_rely_topo(task,rely_task,ass_table,rely_type,token,update_time) values (%s,%s,%s,%s,%s,%s)"
        if is_table:
            sql = "insert into t_rely_topo(task,rely_task) values (%s,%s)"
        cur = conn.cursor(MySQLdb.cursors.DictCursor)
        cur.executemany(sql, values)
        conn.commit()
        cur.close()

    def list_plat_app(self):
        rely_dict = {}
        resp = requests.get('http://dt.xiaozhu.com:8001/list_app')
        code = resp.status_code
        values = []
        if int(code) == 200:
            resp_data = json.loads(resp.text).get('data', {})
            apps = [e['project'] for e in resp_data]
            apps_date_e = {}
            for  conf_info in resp_data:
                apps_date_e[conf_info['project']] = conf_info['date_e']
            now = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
            for app in apps:
                if apps_date_e[app] and apps_date_e[app] < time.strftime('%Y-%m-%d 00:00:00',time.localtime(time.time())) :
                    continue
                params = {'project': app, "get_hql": 1}
                resp = requests.get('http://dt.xiaozhu.com:8001/get_app_conf', params)
                resp_data = json.loads(resp.text).get('data', {})
                project = resp_data['project'][0]
                app_name = project['name']
                run_instance = [r['name'] for r in resp_data['run']['run_instance']['group']]
                for category in project['categories']:
                    cate_name = category['name']
                    for group in category['groups']:
                        group_name = group['name']
                        task = '.'.join((app_name, cate_name, group_name))
                        cat_group = '.'.join((cate_name, group_name))
                        if cat_group in run_instance:
                            ass_table = ''
                            if group.has_key('hql_type') and group['hql_type'] and int(group['hql_type']) == 2:
                                hql = group['hql']
                                db, table = self.match_hql_table_tag(hql)
                                if db and table:
                                    ass_table = '.'.join((db, table))
                            rely = [(task, e['name'], ass_table, 'table', 'data', now) for e in group['tables']]
                            values += rely
                            # rely_table=[e['name'] for e in group['tables']]
        return values

    def match_hql_table_tag(self,hql):
        hql = hql.strip().lower()
        match_table = re.compile(r'insert\s+(overwrite)?\s+table\s+([\s\S]*?)\s+(partition\s*\(([\s\S]*?)\))?')

        match_res = match_table.findall(hql)
        if not match_res:
            return None, None
        match_res = match_res[0]
        table = match_res[1]

        if table:
            table = table.split('.')
            db = 'default'
            if len(table) == 2:
                db = table[0]
                table = table[1]
            else:
                table = table[0]

            return db, table

        return None, None

    def list_datax_app(self):
        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'],
                               passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        now = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        cur = conn.cursor(MySQLdb.cursors.DictCursor)
        sql = "select * from datax_app where date_e is null and type like '%2hdfs'"
        count = cur.execute(sql)
        res = cur.fetchall()
        values = []
        for e in res:
            date_e = e['date_e']
            rely_task = []
            rely = []
            task = '.'.join((e['target_db'], e['data_target']))
            if e['check_ready'] != 0 and e['depend']:
                rely_task = e['depend'].split(',')
            if rely_task:
                rely = [(task, e, task, 'table', 'datax', now) for e in rely_task]
            else:
                rely.append((task, '', task, 'table', 'datax', now))
            values += rely
        cur.close()
        return values

    def list_pase_log(self):
        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'],
                               passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        now = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        cur = conn.cursor(MySQLdb.cursors.DictCursor)
        sql = "select * from parse_app "
        count = cur.execute(sql)
        res = cur.fetchall()
        values = []
        for e in res:
            date_e = e['date_e']
            rely_task = []
            rely = []
            task = '.'.join((e['target_db'], e['target_table']))
            if e['check_ready'] != 0 and e['depend']:
                rely_task = e['depend'].split(',')
            if rely_task:
                rely = [(task, e, task, 'table', 'datax', now) for e in rely_task]
            else:
                rely.append((task, '', task, 'table', 'parse', now))
            values += rely
        cur.close()
        return values

    def get_all_online_table_task(self):
        online_tasks_dict = []
        mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        conn = mmsMysql.get_conn()
        cur = mmsMysql.get_cur()
        sql = 'select id,cn_name,project,`group`,metric,creater,flag,params from t_visual_table where flag=1'
        cur.execute(sql)
        columns = cur.description
        result = []
        for value in cur.fetchall():
            tmp = {}
            for (index, column) in enumerate(value):
                tmp[columns[index][0]] = column
            result.append(tmp)
        conn.close()

        udf = ['max', 'min', 'sum', 'avg', 'count']
        reg_udf = '|'.join(udf)
        r = re.compile(r'((%s)\((.*?)(>>.*)?\))' % reg_udf)
        allr = re.compile(r'((.*?)(\+|\-|\*|/|$)(?!>))')
        udc_task_list = []
        for e in result:
            params = e['params']
            project = e['project']
            metric = e['metric']
            table_name = e['cn_name']

            if project and metric:
                metric_list = metric.split(',')
                for m in metric_list:
                    tmp = project + '.' + m[0:m.rindex('.')]
                    online_tasks_dict.append([table_name,tmp])
            try:
                params = phpserialize.loads(params)
                if params.has_key('tablelist') and params['tablelist']:
                    params_table_list = params['tablelist']
                    for key, params_table in params_table_list.items():
                        if params_table.has_key('sql') and params_table['sql']:
                            sql = params_table['sql']
                            udf = ['TABLE']
                            reg_udf = '|'.join(udf)
                            r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
                            result = r.findall(sql)
                            for e in result:
                                pro_dims = str(e[2]).split(',')
                                project = pro_dims[0]
                                dims = pro_dims[1:]
                                dims.sort()
                                dim_key = ''
                                if len(dims) == 1 and 'all' in dims:
                                    dim_key = project
                                else:
                                    dim_key = '%s,%s' % (project, ','.join(dims))
                                dim2tasks = self.get_app_dim2tasks(project)
                                if dim2tasks.has_key(dim_key):
                                    online_tasks_dict += dim2tasks[dim_key]
                        if params_table.has_key('metric') and params_table['metric']:
                            table_metric_list = params_table['metric'].split(',')
                            for t_m in table_metric_list:
                                t_tmp = project + '.' + t_m[0:t_m.rindex('.')]
                                online_tasks_dict.append([table_name, t_tmp])

                        if params_table.has_key('udcconf') and params_table['udcconf']:
                            udcconf = json.loads(urllib.unquote(params_table['udcconf']))
                            if isinstance(udcconf, (list)):
                                for u in udcconf:
                                    if u.has_key('expression') and u['expression']:
                                        udc = u['expression']
                                        allresult = allr.findall(udc)
                                        for all in allresult:
                                            res_name = all[1]
                                            content = ''
                                            tmp_res = r.findall(res_name)
                                            if tmp_res:
                                                result = tmp_res[0]
                                                if result:
                                                    content = result[2]
                                            else:
                                                content = res_name
                                            dim_metric = content.split('->')
                                            if len(dim_metric) == 2:
                                                split_metric = dim_metric[1].split('.')
                                                if len(split_metric) == 4:
                                                    tmp = str(dim_metric[1][0:dim_metric[1].rindex('.')])
                                                    udc_task_list.append([table_name, tmp])
                                                else:
                                                    tmp = project + '.' + str(
                                                        dim_metric[1][0:dim_metric[1].rindex('.')])
                                                    udc_task_list.append([table_name, tmp])

                if params.has_key('table') and params['table']:
                    params_table = params['table']
                    if params_table.has_key('metric') and params_table['metric']:
                        table_metric_list = params_table['metric'].split(',')
                        for t_m in table_metric_list:
                            t_tmp = project + '.' + t_m[0:t_m.rindex('.')]
                            online_tasks_dict.append([table_name, t_tmp])

                    if params_table.has_key('udcconf') and params_table['udcconf']:
                        udcconf = json.loads(urllib.unquote(params_table['udcconf']))
                        if isinstance(udcconf, (list)):
                            for u in udcconf:
                                if u.has_key('expression') and u['expression']:
                                    udc = u['expression']
                                    allresult = allr.findall(udc)
                                    for all in allresult:
                                        res_name = all[1]
                                        content = ''
                                        tmp_res = r.findall(res_name)
                                        if tmp_res:
                                            result = tmp_res[0]
                                            if result:
                                                content = result[2]
                                        else:
                                            content = res_name
                                        dim_metric = content.split('->')
                                        if len(dim_metric) == 2:
                                            split_metric = dim_metric[1].split('.')
                                            if len(split_metric) == 4:
                                                tmp = str(dim_metric[1][0:dim_metric[1].rindex('.')])
                                                udc_task_list.append([table_name, tmp])
                                            else:
                                                tmp = project + '.' + str(dim_metric[1][0:dim_metric[1].rindex('.')])
                                                udc_task_list.append([table_name, tmp])
                if params.has_key('chart') and params['chart']:
                    params_chart_list = params['chart']
                    for p_chart in params_chart_list.values():
                        if p_chart.has_key('metric') and p_chart['metric']:
                            chart_metric_list = p_chart['metric'].split(',')
                            for t_m in chart_metric_list:
                                t_tmp = project + '.' + t_m[0:t_m.rindex('.')]
                                online_tasks_dict.append([table_name, t_tmp])

                        if p_chart.has_key('udcconf') and p_chart['udcconf']:
                            udcconf = json.loads(urllib.unquote(p_chart['udcconf']))
                            if isinstance(udcconf, (list)):
                                for u in udcconf:
                                    if u.has_key('expression') and u['expression']:
                                        udc = u['expression']
                                        allresult = allr.findall(udc)
                                        for all in allresult:
                                            res_name = all[1]
                                            content = ''
                                            tmp_res = r.findall(res_name)
                                            if tmp_res:
                                                result = tmp_res[0]
                                                if result:
                                                    content = result[2]
                                            else:
                                                content = res_name
                                            dim_metric = content.split('->')
                                            if len(dim_metric) == 2:
                                                split_metric = dim_metric[1].split('.')
                                                if len(split_metric) == 4:
                                                    tmp = str(dim_metric[1][0:dim_metric[1].rindex('.')])
                                                    udc_task_list.append([table_name, tmp])
                                                else:
                                                    tmp = project + '.' + str(
                                                        dim_metric[1][0:dim_metric[1].rindex('.')])
                                                    udc_task_list.append([table_name, tmp])



            except:
                import traceback
                # traceback.print_exc()
        return_list = udc_task_list + online_tasks_dict
        #return_list = list(set(return_list))
        return_list = list(set([tuple(t) for t in return_list]))
        return_list = [list(v) for v in return_list]
        return return_list

    def get_app_dim2tasks(self,project_name=None):
        dim2task = {}
        if project_name:
            appConf = appObj.AppConf(project_name)
            temp = appConf.appConf
            result = {}
            for category in temp['project'][0]["categories"]:
                cat_name = category['name']
                for group in category["groups"]:
                    group_name = group['name']
                    for d_dim in group['dim_sets']:
                        task_name = '%s.%s.%s' % (project_name, cat_name, group_name)
                        key_name = ''
                        if d_dim['name'] == '()':
                            key_name = project_name
                        else:
                            str_dim = d_dim['name'].strip('()').split(',')
                            l_dim = [e.lower().strip() for e in str_dim]
                            l_dim.sort()
                            key_name = '%s,%s' % (project_name, ','.join(l_dim))
                        if not dim2task.has_key(key_name):
                            dim2task[key_name] = []
                        dim2task[key_name].append(task_name)

        return dim2task




def cmdArgsDef():
    arg_parser = argparse.ArgumentParser()
    arg_parser.add_argument('-o', '--operation', default='refresh', choices = ["refresh","update"], help='[Optional] all,hive,mysql', required=True)

    args = arg_parser.parse_args()

    return args


if __name__ == '__main__':
    cmd_args = cmdArgsDef()
    operation = cmd_args.operation

    try:

        topo_task = TopoTask()
        now = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        if operation == 'refresh':
            print now
            print 'start refresh task status'
            topo_task.refresh_status()
            print 'end refresh task status'
        elif operation == 'update':
            print now
            print 'start update task rely'
            topo_task.refresh_task_link()
            print 'end update task rely'
        else:
            print 'nonononononono'
    except:
        import traceback

        traceback.print_exc()
