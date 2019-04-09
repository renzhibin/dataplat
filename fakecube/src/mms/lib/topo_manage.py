# -*- coding:utf8 -*-
"""
    @author: pengbangzhong
    @file: topo_manage.py
    @time: 2017/1/16 下午2:16 
"""
import sys
reload(sys)
sys.setdefaultencoding('utf8')

import MySQLdb
from mms_mysql_conf import MMS_DB_META

class TopoManage(object):

    def __init__(self,show_task='',is_single=True,is_parent=True,is_child=True):

        self.show_task=show_task.split(',')
        self.is_single=is_single
        self.is_parent=is_parent
        self.is_child=is_child

        self.links_info=[]
        self.table2task={}
        self.node_map={}
        self.node_link={}
        self.linked=[]
        self.parent_linked=[]
        self.child_linked=[]
        self.all_links=[]
        self.init()


    def init(self):
        self.init_linksinfo()
        self.init_table2task()
        self.init_nodelink()

    def init_linksinfo(self):
        conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'], passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')
        sql='''
            select lower(t1.task) task,lower(t1.rely_task) rely_task,lower(t1.ass_table) ass_table,
            lower(t1.rely_type) rely_type,
            lower(t1.token) token,
            t1.update_time,
            t2.status,t2.creater,t2.plat,t2.start_time,t2.end_time,t2.data_size
            from
                t_rely_topo t1
            left join
                t_rely_task t2
            on t1.task=t2.task
        '''
        cur = conn.cursor(MySQLdb.cursors.DictCursor)
        cur.execute(sql)
        self.links_info= cur.fetchall()
        cur.close()
        for link in self.links_info:
            if link['start_time']:
                link['start_time']=link['start_time'].strftime("%Y-%m-%d %H:%M:%S")
            if link['end_time']:
                link['end_time']=link['end_time'].strftime("%Y-%m-%d %H:%M:%S")
            if link['update_time']:
                link['update_time']=link['update_time'].strftime("%Y-%m-%d %H:%M:%S")
            self.node_map[link['task']]=link

    def init_table2task(self):

        for link in self.links_info:
            task = link['task']
            ass_table = link['ass_table']
            token = link['token']
            if ass_table:
                ass_split = ass_table.split('.')
                if len(ass_split) < 2:
                    ass_table = 'default.' + ass_table
                if token == 'data':
                    self.table2task[ass_table.lower()] = task


    def init_nodelink(self):
        for link in self.links_info:
            task = link['task']
            rely_task = link['rely_task']

            if rely_task:
                tmp_sp = rely_task.split('.')
                if len(tmp_sp) == 1:
                    rely_task = 'default.' + rely_task
            if self.table2task.has_key(rely_task.lower()):
                rely_task = self.table2task[rely_task.lower()]

            if not self.node_link.has_key(task):
                self.node_link[task] = {"parent": [], "child": []}

            self.node_link[task]['parent'].append(rely_task)
            if rely_task:
                if not self.node_link.has_key(rely_task):
                    self.node_link[rely_task] = {"parent": [], "child": []}
                self.node_link[rely_task]['child'].append(task)

    def topo_node(self):
        for task in self.show_task:

            if self.table2task.has_key(task.lower()):
                task=self.table2task[task.lower()]

            if self.is_single:
                self.__node_single_link(task)
            else:
                self.__node_link(task)

    def __node_single_link(self,task):

        if self.is_parent:
            self.__node_parent_link(task)
        if self.is_child:
            self.__node_child_link(task)
        self.linked=list(set(self.parent_linked+self.child_linked))

    def __node_parent_link(self,task):
        if self.node_link.has_key(task) and task not in self.parent_linked:
            parent = self.node_link[task]['parent']
            self.parent_linked.append(task)
            for p in parent:
                self.all_links.append("{}@{}".format(p, task))
                self.__node_parent_link(p)

    def __node_child_link(self,task):
        if self.node_link.has_key(task) and task not in self.child_linked:
            child = self.node_link[task]['child']
            self.child_linked.append(task)
            for c in child:
                self.all_links.append("{}@{}".format(task, c))
                self.__node_child_link(c)



    def __node_link(self,task):
        if self.node_link.has_key(task) and task not in self.linked:
            parent = self.node_link[task]['parent']
            child = self.node_link[task]['child']
            self.linked.append(task)
            if self.is_parent:
                for p in parent:
                    self.all_links.append("{}@{}".format(p, task))
                    self.__node_link(p)
            if self.is_child:
                for c in child:
                    self.all_links.append("{}@{}".format(task, c))
                    self.__node_link(c)


    def topo_data(self):
        self.topo_node()
        mynodes = []
        mylinks = []

        for n in list(set(self.linked)):
            tmp_node={}
            tmp_node['name']=n
            tmp_node["status"]=''
            tmp_node["creater"]=''
            tmp_node["plat"]=''
            tmp_node["start_time"]=''
            tmp_node["end_time"]=''
            tmp_node["data_size"]=''
            if self.node_map.has_key(n):
                tmp_node.update(self.node_map[n])
            mynodes.append(tmp_node)
        for l in list(set(self.all_links)):
            ls = l.split("@")
            if len(ls) == 2 and ls[0] and ls[1]:
                tmp_link={}
                tmp_link["source"]=ls[0]
                tmp_link["target"]=ls[1]
                tmp_link["value"]=1
                if self.node_map.has_key(ls[1]):
                    if self.node_map[ls[1]]["data_size"]:
                        tmp_link["value"]=self.node_map[ls[1]]["data_size"]
                if tmp_link["source"]!=tmp_link["target"]:
                    mylinks.append(tmp_link)
        ret={"nodes":mynodes,"links":mylinks}
        return ret


    def get_nodes(self):

        return list(set(self.node_link.keys()))





#获取未成功任务
def get_no_succ_task():
    conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'], passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')


    sql = '''
            select lower(task) task from t_rely_task where is_vaild=1 and status!=5 and status!=7
        '''
    cur = conn.cursor(MySQLdb.cursors.DictCursor)
    cur.execute(sql)
    res = cur.fetchall()
    cur.close()
    task=[r['task'] for r in res]
    return list(set(task))


def get_all_task():
    conn = MySQLdb.connect(host=MMS_DB_META['host'], port=MMS_DB_META['port'], user=MMS_DB_META['user'], passwd=MMS_DB_META['passwd'], db=MMS_DB_META['database'],
                               charset='utf8')


    sql = '''
            select lower(task) task from t_rely_task
        '''
    cur = conn.cursor(MySQLdb.cursors.DictCursor)
    cur.execute(sql)
    res = cur.fetchall()
    cur.close()
    task=[r['task'] for r in res]
    return list(set(task))
