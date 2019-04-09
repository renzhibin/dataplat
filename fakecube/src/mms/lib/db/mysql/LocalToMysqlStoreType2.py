#!/usr/bin/env python2.7
#coding=utf-8
import env as conf
from log4me import MyLogger
from mms_conf import MmsConf
from LocalToMysqlBase import LocalToMysql

logger = MyLogger.getLogger()
class LocalToMysqlStoreType2(LocalToMysql):


    def getRangeExistsTables(self, suffix_s, suffix_e):
        mmsMysql = self.meta_mmsMysql_slave
        meta_conn=mmsMysql.get_conn()
        meta_cur=mmsMysql.get_cur(mmsMysql.DICTCURSOR_MODE)

        tables = {}
        metric = self.metric_conf
        sql = """select group_keys,table_name
               from %s where metric_conf = '%s'  and storetype='%s' and schedule_level='%s'
               """ % (conf.ENTRY_TABLE,metric,self.storetype,self.schedule_level)

        sql+= ''' and suffix>='%s' and suffix<='%s' '''% (suffix_s, suffix_e)
        logger.info("table start suffix:"+ suffix_s + " end suffix:" + suffix_e)

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
        sql+= ''' and suffix='%s' '''% suffix

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

        sql+= ''' and suffix='%s' '''% self.storetype2suffix

        meta_cur.execute(sql)

        for row in meta_cur:
            tables[row[0]] = row[1]
        meta_conn.close()
        return tables

    def createTable4NewGroup(self,dims,i, tab_suffix=None,custom_time=None):

        if tab_suffix is None:
            tab_suffix = self.storetype2suffix

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

        func=lambda x:str(dimensions2type[x]) if dimensions2type.has_key(x) else str(self.type_dict['varchar'])

        table = conf.TABLE_PREFIX +"v2_" +"_".join(map(str,[self.metric_conf,id,tab_suffix]))

        if 'minute'==self.schedule_level or 'hour'==self.schedule_level:
            table = conf.TABLE_PREFIX +"v2_"+self.schedule_level+"_" +"_".join(map(str,[self.metric_conf,id,tab_suffix]))

        column=''
        #增加唯一索引列fakecube_unique,值为所有维度md5
        uk = "`fakecube_unique`"
        index_key="`cdate`,"
        #索引最大长度3072 bytes 对应到utf8mb4最大是768个字符 当有7个以上维度时没法直接建立索引

        tmp_dims=[]
        for i in dims:
            i=i.strip()
            if i!="":
                if (dim_type_dict.has_key(i) and dim_type_dict[i]!='decimal') or not dim_type_dict.has_key(i):
                    tmp_dims.append(i)

        prefix_index=100
        if len(tmp_dims)>7 and len(tmp_dims)<15:
            prefix_index=(768-10)/len(tmp_dims)

        if len(tmp_dims)>=15:
            prefix_index=(768-10)/15

        for i in range(0,len(tmp_dims)):
            if i<=14:
                index_key+="`" + tmp_dims[i] + "`(" + str(prefix_index)+"),"

        index_key=index_key.strip(',')

        for i in dims:
            i=i.strip()
            if i!="":
                ns = "`" + i + "`" + " %s,"%(func(i))
                us = "`" + i + "`" + ","
                column = column + ns

        uk=uk.strip(',')
        column=column.strip(',')
        if column:
            column +=','

        #分钟级别添加hour minute
        schedule_filed=''
        if 'minute'==self.schedule_level or 'hour'==self.schedule_level:
            schedule_filed='hour varchar(10),minute varchar(10),'
            uk=uk+',`hour`,`minute`'
        create_sql = """create table if not exists %s (
                   `id`  bigint(20)  NOT NULL auto_increment COMMENT '主键',
                   `fakecube_unique` varchar(100) ,
                   cdate varchar(10),
                   %s
                   %s
                   PRIMARY KEY  (`id`),
                   UNIQUE(%s),
                   KEY (%s)
                   )
                   ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='table';""" % (table,column,schedule_filed,uk,index_key)

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

