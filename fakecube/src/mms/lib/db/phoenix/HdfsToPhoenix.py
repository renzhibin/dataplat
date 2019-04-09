#!/usr/bin/env python2.7
#coding=utf-8

import os
import time
import sys
import env as conf
from log4me import MyLogger
from HandleData import HandleData
from PhoenixBase import PhoenixBase
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql

reload(sys)
sys.setdefaultencoding('utf-8')

logger = MyLogger.getLogger()

class HdfsToPhoenix(HandleData,PhoenixBase):

    def init(self):
        self.under_dt=time.strftime('%Y_%m_%d',time.strptime(self.dt,'%Y-%m-%d'))
        if self.cat and self.group.has_key("metrics"):
            self.table_suffix='_'.join([self.project,self.lowercat,self.group["name"].lower(),self.under_dt])
            self.hdfs_path='/user/inf/'+self.project+'/'+self.table_suffix
            self.hbase_metric=map(lambda x:'cf:'+x,self.met_metric_list)




    def deleteExistsData(self):

        tables=self.exists_tables.values()
        for table_name in tables:
                dim_sets=self.table2dim[table_name].split(',')
                if '' in dim_sets:
                    dim_sets.remove('')

                met_list=[]
                not_metric_met=['cdate']
                not_metric_met +=dim_sets
                not_metric_met += self.met_metric_list
                res=self.getPhoenix(table_name,'schema')


                res_list=res['data']
                for value in res_list:
                    if not value.lower() in not_metric_met:
                        met_list.append(value.lower())
                delete_sql="delete from "+ table_name+" where cdate='"+self.dt+"' "
                for value in met_list:
                    delete_sql+= ' and '+value+ ' is null'

                self.getPhoenix(delete_sql,'update')

                update_sql='UPSERT into '+table_name+' ( '+','.join(not_metric_met)+' )'

                update_select_sql=' select cdate,'+','.join(dim_sets
                                    +['null' for i in range(len(self.met_metric_list))])+' from '+table_name


                update_select_sql+= " where cdate='"+self.dt+"' "
                self.getPhoenix(update_sql+update_select_sql,'update')

        logger.info('delete data succ')

    def __initSchema(self,dim_type=' string',metric_type=None):

        all=[]
        if metric_type is None:
            metric_type=dim_type
        for i in self.group["dimensions"]:
            all.append( i["name"].lower()+dim_type)
        if dim_type==':chararray':
            all.append('GROUPING__ID:int')
        else:
            all.append('GROUPING__ID'+metric_type)

        for i in self.met_metric_list:
           all.append( i+metric_type)

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
            tmp='''if(%s='','empty',cast(%s as string)) as %s '''%(str,str,str)
            empty_check.append(tmp)

        empty_check.append('GROUPING__ID')

        for i in self.group["metrics"]:
            str= i["name"].lower()
            tmp='''if(%s='','empty',%s) as %s '''%(str,str,self.metric_prefix+str)
            empty_check.append(tmp)

        hql=''' select %s from (%s) tmp '''%(','.join(empty_check),self.hql)
        return  '\n'.join([drop_table,create_sql,set_cond,hql_prefix,hql])

    def alterNewCol(self):


        exists_tables=self.exists_tables
        metric_list=self.met_metric_list
        for k,table_name in exists_tables.items():
            res=self.getPhoenix(table_name,'schema')

            print res
            res_list=res['data']
            tmp_list=metric_list[0:]
            for value in res_list:
                if  value in tmp_list:
                    tmp_list.remove(value)

            for k  in tmp_list:

                sql="""
                ALTER TABLE %s ADD %s double
                """%(table_name,k)
                print sql
                try:
                    self.getPhoenix(sql,'update')
                except:
                    print 'alter failed'


    def loadData(self):
        field=self.__initSchema(':chararray',':double')
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
            if '' in dim_arr:
                dim_arr.remove('')
            pig_dim_arr=['SUBSTRING('+i+',0,63)' for i in dim_arr]
            m_pig_arr=['\''+self.dt+'\'']+pig_dim_arr+self.met_metric_list
            if '' in m_pig_arr:
                m_pig_arr.remove('')    

            m_pig=','.join(m_pig_arr)

            pig_prefix+='''
            C%s = FOREACH B%s GENERATE %s;
                        '''%(gid,gid,m_pig)
        hbase_metric=self.hbase_metric
        for gid,dim_arr in gid2dim.iteritems():
            dim_arr.sort()
            dim_str = ",".join(dim_arr)
            table=self.exists_tables[dim_str]
            m_pig_arr=['cdate']+dim_arr+self.met_metric_list
            if '' in m_pig_arr:
                m_pig_arr.remove('')
            pig_prefix+='''
            STORE C%s INTO 'hbase://%s/%s'
               using  org.apache.phoenix.pig.PhoenixHBaseStorage('jxq-hd-001:2181','-batchSize 5000');
            '''%(gid,table,','.join(m_pig_arr))#phoenxi bug maybe best size is 5000

        tmp_file = '%s/%s.pig' %(conf.TMP_SQL_PATH,self.table_suffix)
        jar_prefix='''
        register /hadoop/phoenix/lib/phoenix-pig-3.2.0.jar
        register /hadoop/phoenix/lib/phoenix-core-3.2.0.jar
        register /hadoop/phoenix/lib/zookeeper-3.4.5.jar
        register /hadoop/phoenix/lib/protobuf-java-2.4.0a.jar
        ''';
        with open(tmp_file,'w') as f:
                    f.write(jar_prefix+str(pig_prefix))
        condition='source ~/trunk/env.sh && pig %s >>%s 2>&1'%(tmp_file, self.log_file)
        logger.info(jar_prefix+pig_prefix)
        logger.info(condition)

        res=os.system(condition)
        if res!=0:
            status=conf.FAILED
        else:
            status=conf.SUCCESS

        return {'num':0,'status':status,'msg':"load data success."}



    def createTable4NewGroup(self,dim_arr,table_count):

        table_name='fakecube.'+'_'.join([self.project,str(table_count)])
        dim_str='cdate  VARCHAR(255) not null,'
        pk=' CONSTRAINT pk PRIMARY KEY (cdate,'
        for i in dim_arr:
            if i:
                dim_str+=i+' VARCHAR(255),';
                pk+=i+' ,'

        pk=pk.rstrip(',')+')'

        sql='''CREATE TABLE IF NOT EXISTS %s (%s%s)'''%(table_name,dim_str,pk)

        print sql
        self.getPhoenix(sql,'update')

        # do notcatch exception

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







