#!/usr/bin/env python2.7
#coding=utf-8

import time
import happybase
import os,sys
import env as conf
from log4me import MyLogger
from HandleData import HandleData
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql

reload(sys)
sys.setdefaultencoding('utf-8')

logger = MyLogger.getLogger()

class HdfsToHbase(HandleData):

    def init(self):
        self.under_dt=time.strftime('%Y_%m_%d',time.strptime(self.dt,'%Y-%m-%d'))
        if self.cat and self.group.has_key("metrics"):
            self.table_suffix='_'.join([self.project,self.lowercat,self.group["name"].lower(),self.under_dt])
            self.hdfs_path='/user/inf/'+self.project+'/'+self.table_suffix
        #self.connection=happybase.Connection(host='127.0.0.1',port=47625,transport='framed',table_prefix='fakecube')
            self.connection=happybase.Connection(host='10.6.0.85',port=30000,transport='framed',table_prefix='fakecube')
            self.hbase_metric=map(lambda x:'cf:'+x,self.met_metric_list)


    def deleteExistsData(self):

        tables=self.exists_tables.values()
        for table in tables:
            table=self.connection.table(table)
            res=table.scan(row_prefix=self.dt)
            delete_batch=table.batch(batch_size=20000)
            for k, v in res:
                if v.keys() == self.hbase_metric:
                    delete_res=delete_batch.delete(k)
                else:
                    delete_res=delete_batch.delete(k,self.hbase_metric)
            delete_batch.send()
            return True


    def __initSchema(self,string_type=' string'):

        all=[]
        for i in self.group["dimensions"]:
            all.append( i["name"].lower()+string_type)
        if string_type==':chararray':
            all.append('GROUPING__ID:int')
        else:
            all.append('GROUPING__ID'+string_type)

        for i in self.met_metric_list:
           all.append( i+string_type)

        return ','.join(all)

    def getHql(self):
        '''
        drop table tmp.fakecube_test;
        CREATE EXTERNAL TABLE tmp.fakecube_test(first_source string, first_sub_source string,client_device string,client_version string,
        GROUPING__ID string,uv string,pv string)
        ROW FORMAT DELIMITED

        FIELDS TERMINATED BY '\t'
        LINES TERMINATED BY '\n'
        LOCATION '/user/inf/mob_channel_metric/a_b';

        insert overwrite  table   tmp.fakecube_test
        '''
        table_suffix=self.table_suffix

        prefix='SET hive.exec.compress.output=false;'

        table_name='tmp.fakecube_'+table_suffix
        field=self.__initSchema()
        drop_table='drop table %s;'%(table_name)
        create_sql="""
                   CREATE EXTERNAL TABLE %s(%s)
        ROW FORMAT DELIMITED

        FIELDS TERMINATED BY '\\t'
        LINES TERMINATED BY '\\n'
        LOCATION '%s';
        """%(table_name,field,self.hdfs_path)

        set_cond='SET hive.exec.compress.output=false;'
        hql_prefix='insert overwrite  table  %s'%(table_name)
        empty_check=[]
        for i in self.group["dimensions"]:
            str= i["name"].lower()
            tmp='''if(%s='','empty',%s) as %s '''%(str,str,str)
            empty_check.append(tmp)

        empty_check.append('GROUPING__ID')

        for i in self.group["metrics"]:
            str= i["name"].lower()
            tmp='''if(%s='','empty',%s) as %s '''%(str,str,self.metric_prefix+str)
            empty_check.append(tmp)

        hql=''' select %s from (%s) tmp '''%(','.join(empty_check),self.hql)
        return  '\n'.join([drop_table,create_sql,set_cond,hql_prefix,hql])

    def loadData(self):
        field=self.__initSchema(':chararray')
        pig_prefix='''
        A = LOAD '%s' USING PigStorage('\\t') AS (%s);
        SPLIT A INTO
        '''%(self.hdfs_path,field)
        gid2dim=dict()
        for i in self.group["dim_sets"]:
            dims = i["name"].lower().strip("()").split(',')
            gid=self.getGroupingIDWithdimensions(dims)
            gid2dim[gid]=dims
        for gid in gid2dim:
            pig_prefix+='''B%s IF GROUPING__ID==%s,''' %(gid,gid)
        #pig_prefix=pig_prefix.strip(',')+';'
        pig_prefix+='BTEST IF GROUPING__ID==\'BTEST\''+';'

        for gid,dim_arr in gid2dim.iteritems():
            dim_arr.sort()
            dim_str = ",".join(dim_arr)
            table=self.exists_tables[dim_str]
            str_dt='\''+self.dt+'\''
            if gid==0:
                dim_pig=str_dt
            else:
                dim_pig='CONCAT('+',\'|\','.join([str_dt]+dim_arr)+')'

            m_pig=','.join(self.met_metric_list)

            pig_prefix+='''
            C%s = FOREACH B%s GENERATE %s,%s;
                        '''%(gid,gid,dim_pig,m_pig)
        hbase_metric=self.hbase_metric
        for gid,dim_arr in gid2dim.iteritems():
            dim_arr.sort()
            dim_str = ",".join(dim_arr)
            table=self.exists_tables[dim_str]
            pig_prefix+='''
            STORE C%s INTO 'hbase://fakecube_%s'
                USING org.apache.pig.backend.hadoop.hbase.HBaseStorage(
               \' %s\'
                );
            '''%(gid,table,','.join(hbase_metric))

        #  TODO: multi-query 没打开
        tmp_file = '%s/%s.pig' %(conf.TMP_SQL_PATH,self.table_suffix)
        with open(tmp_file,'w') as f:
                    f.write(str(pig_prefix))
        condition='source ~/trunk/env.sh && pig %s >>%s 2>&1'%(tmp_file, self.log_file)
        logger.info(pig_prefix)
        logger.info(condition)

        res=os.system(condition)
        if res!=0:
            status=conf.FAILED
        else:
            status=conf.SUCCESS

        return {'num':0,'status':status,'msg':"load data success."}



    def createTable4NewGroup(self,dim_arr,table_count):
        connection = self.connection
        '''
        families={'cf1': dict(max_versions=10),
                'cf2': dict(max_versions=1, block_cache_enabled=False),
                'cf3': dict(),  # use defaults
                }
        '''
        families={'cf':dict()}
        table_name='_'.join([self.project,str(table_count)])

        # do not catch exception
        res=connection.create_table(
                table_name,
                families
                )
        suffix=''
        dim_str = ",".join(dim_arr)
        insert_sql = "insert into "+conf.ENTRY_TABLE+" (metric_conf,group_keys,table_name,storetype,suffix) values(%s,%s,%s,%s,%s);"
        table_field=(self.metric_conf,dim_str,table_name,str(self.storetype),suffix)
        logger.debug('insert entry sql'+insert_sql+str(table_field))
        mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META)
        meta_conn=mmsMysql.get_conn()
        meta_cur=mmsMysql.get_cur()
        meta_cur.execute(insert_sql,table_field)
        meta_conn.commit()
        return table_name







