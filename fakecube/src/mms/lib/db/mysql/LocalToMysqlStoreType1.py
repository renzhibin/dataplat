#!/usr/bin/env python2.7
#coding=utf-8
import env as conf
from lock import SingletonLock
from log4me import MyLogger
from mms_conf import MmsConf
from LocalToMysqlBase import LocalToMysql

logger = MyLogger.getLogger()
class LocalToMysqlStoreType1(LocalToMysql):

    def getRangeExistsTables(self, suffix_s, suffix_e):
        mmsMysql = self.meta_mmsMysql_slave
        meta_conn=mmsMysql.get_conn()
        meta_cur=mmsMysql.get_cur(mmsMysql.DICTCURSOR_MODE)

        tables = {}
        metric = self.metric_conf
        sql = """select group_keys,table_name
               from %s where metric_conf = '%s'  and storetype='%s' and schedule_level='%s'
               """ % (conf.ENTRY_TABLE,metric,self.storetype,self.schedule_level)

        meta_cur.execute(sql)
        tables = meta_cur.fetchall()
        meta_conn.close()
        return tables

    def getExistsTablesCustomTime(self, suffix=None,custom_time=None):
        mmsMysql = self.meta_mmsMysql_slave
        meta_conn=mmsMysql.get_conn()
        meta_cur=mmsMysql.get_cur()

        tables = {}
        metric = self.metric_conf
        cat = self.cat
        group_name = self.group["name"]
        sql = """select group_keys,table_name
               from %s where metric_conf = '%s'  and storetype='%s' and schedule_level='%s'
               """ % (conf.ENTRY_TABLE,metric,self.storetype,self.schedule_level)

        meta_cur.execute(sql)

        for row in meta_cur:
            #add suffix in the key
            key = suffix+','+row[0]
            tables[key] = row[1]
        meta_conn.close()
        return tables

    def getExistsTables(self):
        mmsMysql = self.meta_mmsMysql_slave
        meta_conn=mmsMysql.get_conn()
        meta_cur=mmsMysql.get_cur()

        tables = {}
        metric = self.metric_conf
        cat = self.cat
        group_name = self.group["name"]
        sql = """select group_keys,table_name
               from %s where metric_conf = '%s'  and storetype='%s' and schedule_level='%s'
               """ % (conf.ENTRY_TABLE,metric,self.storetype,self.schedule_level)

        meta_cur.execute(sql)

        for row in meta_cur:
            tables[row[0]] = row[1]
        meta_conn.close()
        return tables

    def createTable4NewGroup(self,dims,i, tab_suffix=None,custom_time=None):

        meta_mmsMysql = self.meta_mmsMysql
        meta_conn=meta_mmsMysql.get_conn()
        meta_cur=meta_mmsMysql.get_cur()

        data_mmsMysql = self.data_mmsMysql
        data_conn=data_mmsMysql.get_conn()
        data_cur=data_mmsMysql.get_cur()

        dims_mms_conf=MmsConf()
        dimensions_inf=dims_mms_conf.get_app_dimensions_params(self.metric_conf)
        dimensions2type={}
        dim_type_dict={}
        for k,v in dimensions_inf.items():
            if v.has_key('type') and v['type']:
                dim_type=v['type']
                if self.type_dict.has_key(dim_type) and self.type_dict[dim_type]:
                    dimensions2type[k]=self.type_dict[dim_type]
                    dim_type_dict[k]=dim_type
        #id 表名后缀自增id
        id = i + 1
        dim_str = ",".join(dims)
        storetype=self.storetype

        table = conf.TABLE_PREFIX + "_".join(map(str,[self.metric_conf,id]))


        column = "`cat` varchar(100), `group_name` varchar(100), "
        uk = "`cdate`,`cat`, `group_name`, "
        for i in dims:
            i = i.strip()
            if i != "":
                ns = "`" + i + "`" + " varchar(100) binary, "
                us = "`" + i + "`" + ", "
                column = column + ns
                uk = uk + us
        column = column + "`metric_key` varchar(100),`metric_value` varchar(1024)"
        uk = uk + "`metric_key`"

        if column:
            column +=','

        create_sql = """create table if not exists %s (
                   `id`  bigint(20)  NOT NULL auto_increment COMMENT '主键',
                   cdate varchar(10),
                   %s
                   PRIMARY KEY  (`id`),
                   UNIQUE(%s)
                   )
                   ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='table';""" % (table,column,uk)




        logger.debug('create sql'+create_sql)
        data_cur.execute(create_sql)
        data_conn.commit()

        data_mmsMysql.conn_close()


        insert_sql = "insert into "+conf.ENTRY_TABLE+" (metric_conf,group_keys,table_name,storetype,suffix,schedule_level) values(%s,%s,%s,%s,%s,%s);"
        logger.debug('insert entry sql'+insert_sql+str((self.metric_conf,dim_str,table,str(storetype),tab_suffix)))

        meta_cur.execute(insert_sql,(self.metric_conf,dim_str,table,str(storetype),tab_suffix,self.schedule_level))
        meta_conn.commit()

        meta_mmsMysql.conn_close()


        return table


    def deleteExistsData(self):

        data_mmsMysql = self.data_mmsMysql
        data_conn=data_mmsMysql.get_conn()
        data_cur=data_mmsMysql.get_cur()
        tables = []

        tables=self.table2dim.keys()

        cdate=self.dt
        cat = self.cat
        group_name = self.group["name"]
        storetype=self.storetype

        for table_name in tables:
            sql="""delete from %s where cdate='%s' and cat='%s' and group_name='%s';"""\
                %(table_name,cdate,cat,group_name)
            #logger.info("delete table %s value of %s for %s.%s" %(tablename,cdate,cat,group_name))
            data_cur.execute(sql)
            data_conn.commit()

        data_mmsMysql.conn_close()

        logger.info('delete data succ')

    def insertEachTable(self,data, table):

        data_mmsMysql = self.data_mmsMysql
        data_conn=data_mmsMysql.get_conn()
        data_cur=data_mmsMysql.get_cur()

        storetype=self.storetype

        dim = self.table2dim[table].split(',')
        if dim==['']:
            dim=[]

        met,result=self.insertEachTable_v1(data,table,dim)


        s_str= []
        w_met= []
        for e in met:
            s_str.append('%s')
            e=e.strip('`')
            w_met.append('`'+e+'`')
        template=','.join(s_str)
        met_str = ",".join(w_met)

        sql="replace into "
        sql +=  table + "(%s) values " % (met_str)
        sql = sql +'('+template +')'

        #print sql,result
        lock_file = conf.LOCK_PATH + self.metric_conf
        l = SingletonLock(lock_file)
        l.ex_lock()
        data_cur.execute('SET NAMES utf8mb4')
        data_cur.executemany(sql,result)
        data_conn.commit()
        data_conn.close()

        l.unlock()

    def insertEachTable_v1(self,data_single_table, target_table,dim):
        #met 公共的插入表头
        met = ["`cdate`","cat","group_name"]
        for i in dim:
            met.append(i)


        valcopy =[]
        valcopy.append(self.dt)
        valcopy.append(self.cat)
        valcopy.append(self.group["name"])


        met.append("metric_key")
        met.append("metric_value")



        valuescopy=[]
        temp = []
        for line_dict in data_single_table:
            for key in line_dict["metrics"].keys():
                del valuescopy[:]
                valuescopy = valcopy[:]


                for i in dim:
                    j =  line_dict["dimensions"][i][1]
                    valuescopy.append(j)
                valuescopy.append(key)

                valuescopy.append( line_dict["metrics"][key][1].strip('\n')  )
                temp.append( tuple(valuescopy) )



        return met,temp

    def alterNewCol(self):
        pass