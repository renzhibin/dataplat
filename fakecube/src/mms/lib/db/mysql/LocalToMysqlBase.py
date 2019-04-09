#!/usr/bin/env python2.7
#coding=utf-8
import time
import re
import datetime
import calendar
import traceback
import env as conf
from lock import SingletonLock
from log4me import MyLogger
from HandleData import HandleData
from utils import err_json, mms_md5, getMysqlConfigByAppName,checkMysqlSynchDelay
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql
from mms_conf import MmsConf
from fun_replace import FuncReplace
from abc import abstractmethod

logger = MyLogger.getLogger()
class LocalToMysql(HandleData):

    def init(self):
        self.meta_mmsMysql_slave = MmsMysql(mmsMysqlConf.MMS_DB_META_SLAVE)
        self.meta_mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META)
        metrics_dims_mms_conf=MmsConf()
        res = metrics_dims_mms_conf.select(self.project)
        self.db_name = res[0]['store_db']
        self.db_weight=res[0]['mysql_weight']
        data_primary_config = getMysqlConfigByAppName(self.db_name, 'master')
        self.data_mmsMysql = MmsMysql(data_primary_config)
        logger.info("get mysql store db name = %s, project name=%s" % (self.db_name, self.project))
        #logger.info("db name =%s config parameter= %s" % (self.db_name, str(data_primary_config)))

        metrics_inf=metrics_dims_mms_conf.get_app_metrics_params(self.metric_conf,self.cat,self.group["name"])
        self.metric_dims_inf={}
        type_len_dict={'varchar':100,'varchar200':200,'varchar1024':1024,'decimal':64}
        for k,v in metrics_inf.items():
            metric_type='decimal'
            if v.has_key('type') and v['type']:
                metric_type=v['type']
            if type_len_dict.has_key(metric_type) and type_len_dict[metric_type]:
                self.metric_dims_inf[k]={'type':metric_type,'len':type_len_dict[metric_type]}

        dimensions_inf=metrics_dims_mms_conf.get_app_dimensions_params(self.metric_conf)
        for k,v in dimensions_inf.items():
            dim_type='varchar'
            if v.has_key('type') and v['type']:
                dim_type=v['type']
            if type_len_dict.has_key(dim_type) and type_len_dict[dim_type]:
                self.metric_dims_inf[k]={'type':dim_type,'len':type_len_dict[dim_type]}

        metrics_dims_mms_conf.connRead.close()
        metrics_dims_mms_conf.connWrite.close()

    def close_connection(self):
        if self.meta_mmsMysql_slave.conn.open:
            self.meta_mmsMysql_slave.conn.close()
        if self.meta_mmsMysql.conn.open:
            self.meta_mmsMysql.conn.close()
        if self.data_mmsMysql.conn.open:
            self.data_mmsMysql.conn.close()

    def get_table_columns(self, table):
        data_slave_config = getMysqlConfigByAppName(self.db_name, 'slave', '1')
        tmp_mmsMysql=MmsMysql(data_slave_config)
        cur=tmp_mmsMysql.get_cur()
        conn=tmp_mmsMysql.get_conn()
        columns_list=[]
        sql='''
            show columns from %s
        '''%(table)
        cur.execute(sql)
        for value in cur.fetchall():
            columns_list.append(str(value[0]).lower())
        conn.close()
        return columns_list

    def getCustomTimeSingle(self):
        custom_single = '$DATE(0)'
        if self.group.has_key('custom_single') and self.group['custom_single']:
            custom_single = self.group['custom_single']
        params = {'dt': self.dt}
        udf = ['DATE|MONTH']
        reg_udf = '|'.join(udf)
        repl = custom_single
        r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
        result = r.findall(repl)
        obj = FuncReplace()
        for content in result:
            b = getattr(obj, content[1])
            replace_str = b(params, content[2])
            repl = repl.replace(content[0], replace_str)
        repl_list = repl.split(',')
        result_list = []
        for one_date in repl_list:
            result_list.append("'" + one_date + "'")
        return ','.join(result_list)


    def getCustomTime(self):
        custom_start = '$DATE(0)'
        custom_end = '$DATE(0)'
        if self.group.has_key('custom_start') and self.group['custom_start']:
            custom_start = self.group['custom_start']
        if self.group.has_key('custom_end') and self.group['custom_end']:
            custom_end = self.group['custom_end']
        params= {'dt': self.dt}
        udf = ['DATE|MONTH']
        reg_udf = '|'.join(udf)
        repl = ','.join([custom_start, custom_end])
        r = re.compile(r'(\$(%s)\(([-a-zA-Z0-9,_ ]+)\))' % reg_udf, re.DOTALL)
        result = r.findall(repl)
        obj = FuncReplace()
        for content in result:
            b = getattr(obj, content[1])
            replace_str = b(params, content[2])
            repl = repl.replace(content[0], replace_str)

        repl_list = repl.split(',')
        return repl_list[0], repl_list[1]

    # 0 正常 1 跳过  2 错误
    def checkData(self,line,line_dict, cached_existed_tables):
        #TODO
        dims_metric_index={}
        for key in line_dict['dimensions'].keys():
            p=line_dict['dimensions'][key][0]
            dims_metric_index[p]=key
        dims_metric_index[line_dict['grouping__id'][0]]='grouping__id'

        for key in line_dict["metrics"].keys():
            p = line_dict["metrics"][key][0]
            dims_metric_index[p]=key

        checkflag=True
        arr = line.split("\t")

        if self.custom_cdate == 1:
            #自定义数据展现时间下，首先获取cdate的值，生成对应的维度表
            cdate_val = arr[0]
            cdate_suffix = ''
            # 格式要求 %Y-%m  或者 %Y-%m-%d
            try:
                # 格式 %Y-%m-%d 取月份
                cdate_suffix = time.strftime('%Y%m',time.strptime(cdate_val,"%Y-%m-%d"))
            except: # 格式 %Y-%m 取月份
                try:
                    cdate_suffix = time.strftime('%Y%m',time.strptime(cdate_val,"%Y-%m"))
                except:
                    msg='custom cdate format is not correct %s' % cdate_val
                    return 2, msg, ''

            self.custom_time_suffix.append(cdate_suffix)
            load_cache = False
            #get exists table
            try:
                #try to check cached existed tables have or not
                if cached_existed_tables.has_key(cdate_suffix) and cached_existed_tables[cdate_suffix]:
                    all_exists_tables = cached_existed_tables[cdate_suffix]
                    load_cache = True
                else:
                    all_exists_tables = self.getExistsTablesCustomTime(cdate_suffix,custom_time=cdate_val)
            except:
                msg="project %s get  exists table error" %(self.metric_conf)
                logger.exception(msg)
                return err_json(msg)

            table_count = len(all_exists_tables)


            for i in self.group["dim_sets"]:

                dims_str = i["name"].lower().strip("()")
                tmp_arr = dims_str.split(",")
                dim_arr = []
                for j in tmp_arr:
                    dim_arr.append(j.strip().lower())
                dim_arr.sort()
                dim_str = ",".join(dim_arr)
                tmp_key_dim=dim_str
                dim_str = cdate_suffix+','+dim_str
                table_name = ''
                if not dim_str in all_exists_tables.keys():
                    try:
                        table_name = self.createTable4NewGroup(dim_arr,table_count,cdate_suffix,custom_time=cdate_val)
                        logger.info("%s.%s create newtable %s" %(self.cat,self.group["name"],table_name))
                        table_count += 1

                        self.exists_tables[dim_str] = table_name

                    except:
                        msg="project %s %s.%s create table for new group %s error!" %(self.metric_conf,self.cat,self.group["name"],dim_arr)
                        return err_json(msg,logger=logger)

                else:
                    table_name = all_exists_tables[dim_str]
                    self.exists_tables[dim_str] = table_name

                if not self.dict_file.has_key(table_name):
                    self.dict_file[table_name] = []

                if not load_cache:
                    if cached_existed_tables.has_key(cdate_suffix):
                        cached_existed_tables[cdate_suffix].update({dim_str:table_name})
                    else:
                        cached_existed_tables[cdate_suffix] = {dim_str:table_name}

                if int(self.storetype)==5:
                    tmp_key='-'.join((tmp_key_dim,table_name))
                    if self.updateRangeTimeTable.has_key(tmp_key):
                        update_dim_table=self.updateRangeTimeTable[tmp_key]
                        s_time=update_dim_table['start_time']
                        e_time=update_dim_table['end_time']
                        custom_date=cdate_val.replace('-','')
                        if len(custom_date)==6:
                            custom_date='{}{}'.format(str(custom_date),'01')
                        if custom_date<s_time:
                            s_time=custom_date
                        if custom_date>e_time:
                            e_time=custom_date
                        update_dim_table['start_time']=s_time
                        update_dim_table['end_time']=e_time
                        self.updateRangeTimeTable[tmp_key]=update_dim_table


        data=[]
        errno=0
        msg='TRUE'
        i=0
        for tmp in arr:
             i=i+1
             try:
                 tmp=tmp.decode('utf8')
             except:
                 try:
                     tmp=tmp.decode('gb2312')
                 except:
                    #logger.warn("not utf8 and gb2312  %s"%(arr))
                    return 2,'not ut8 and gb2312',''
             try:
                line_key=dims_metric_index[i-1]
                line_key=line_key.lower()
                if self.metric_dims_inf.has_key(line_key) and self.metric_dims_inf[line_key]:
                    tmp_line_key_inf=self.metric_dims_inf[line_key]
                    if tmp_line_key_inf.has_key('type') and tmp_line_key_inf['type']!='decimal':
                        if tmp_line_key_inf.has_key('len') and tmp_line_key_inf['len']:
                            if len(tmp)>int(tmp_line_key_inf['len']):
                                logger.warn(str(tmp_line_key_inf['len']))
                                logger.warn("data is too long %s"%(arr))
                                errno=3
                                msg='data is too long'
                                tmp=tmp[:int(tmp_line_key_inf['len'])]
             except:
                 import traceback
                 traceback.format_exc()

             try:
                tmp.encode('gbk')
             except:
                pass


             data.append(tmp.encode('utf8'))

        if not  (len(arr) == len(line_dict["dimensions"]) + len(line_dict["metrics"]) + 1):
            tmp_len=len(line_dict["dimensions"]) + len(line_dict["metrics"]) + 1
            msg="Failed to load data to mysql,the length of dim (length:%s) %s  does not match (length:%s) %s."%(line_dict,tmp_len,arr,len(arr))
            logger.warn(msg)
            return  2,msg,''
        return errno,msg,"\t".join(data)

    def initSchema(self):

        line_dict = {}
        line_dict["dimensions"] = {}
        line_dict["metrics"] = {}
        line_dict["grouping__id"] = []

        n = 0
        if self.custom_cdate == 1:
            line_dict["dimensions"]['cdate']= [n]
            n+=1

        for i in self.group["dimensions"]:
            j = i["name"].lower()
            line_dict["dimensions"][j] = []
            line_dict["dimensions"][j].append(n)
            n += 1
        line_dict["grouping__id"].append(n)
        n += 1
        for i in self.group["metrics"]:
            j = i["name"].lower()
            line_dict["metrics"][j] = []
            line_dict["metrics"][j].append(n)
            n += 1
        return line_dict

    def  loadData(self):
        log_file_id=self.log_file_id
        try:
            tmp_project_group='.'.join((self.metric_conf,self.cat,self.group['name']))
            white_project_list=conf.WHITE_PROJECT_LIST.split(',')#特殊项目不处理
            white_task_list=['ares.mogujie_data.shop_sale_month_rank','catalog_gmv_rate.general.catalog_st','mob_poster.general.poster2st_uv_pv']#[200万,500万)
            white_task_list.append('ares.mogujie_data.shop_sale_month_rank')
            white_task_list.append('ares.mogujie_data.goods_gmv_month_rank')
            white_task_list.append('ares.mogujie_data.goods_gmv_week_rank')
            white_task_list.append('ares.mogujie_data.mgj_week_hot_goods')
            white_task_list.append('tag_poster_tid_num.general.poster_tid_num')

            #如果任务结果为空设置为警告
            if int(self.hive_result_nr)==0:
                logger.warn('hive result is empty')
                return {'num':0,'status':conf.WARNING,'msg':'hive result is empty'}

            logger.info('wc -l hive result file num is %s'%(str(self.hive_result_nr)))
            if int(self.hive_result_nr)>=conf.SPECIAL_RESULT_NUM_MIN and int(self.hive_result_nr)<conf.SPECIAL_RESULT_NUM_MAX and tmp_project_group not in white_task_list and self.metric_conf not in white_project_list:
                msg='%s hive result file num is %s more than %s'%(tmp_project_group,str(self.hive_result_nr),str(conf.SPECIAL_RESULT_NUM_MIN))
                logger.error(msg)
                return err_json(msg)
            elif int(self.hive_result_nr)>=conf.SPECIAL_RESULT_NUM_MAX and self.metric_conf not in white_project_list:
                msg='%s hive result file num is %s more than %s'%(tmp_project_group,str(self.hive_result_nr),str(conf.SPECIAL_RESULT_NUM_MAX))
                logger.error(msg)
                return err_json(msg)

            #如果是大数据的任务更新 mms_conf的mysql_weight到副从库
            if self.hive_result_nr > int(conf.BIG_DATA_COLUMN_NUMBER):
                mmsconf_ins = MmsConf()
                mmsconf_res = mmsconf_ins.select(self.project)
                app_id = mmsconf_res[0]['id']
                hql_name = '_'.join((self.cat, self.group['name']))
                update_log = dict(hql=hql_name, result=self.hive_result_nr)
                cur_time = datetime.datetime.strftime(datetime.datetime.now(), '%Y-%m-%d %H:%M')
                update_params = {'mysql_weight':'2', 'update_weight_time': cur_time, 'weight_update_log': str(update_log)}
                mmsconf_ins.update(update_params, app_id)
                mmsconf_ins.connRead.close()
                mmsconf_ins.connWrite.close()

        except:
            msg='wc -l hive result file %s error'%(self.hive_result_file)
            logger.exception(msg)
            return err_json(msg)
        try:
            f = open(self.hive_result_file)
        except:
            msg="project %s %s.%s failed to open hive result file %s"%(self.metric_conf,self.cat,self.group["name"],self.hive_result_file)
            logger.exception(msg)
            return err_json(msg)

        #print exists_tables
        num_file=0
        # a = time.time()

        if self.custom_cdate == 0:
            for table in self.exists_tables.values():
                self.dict_file[table] =[]
        else:
            self.exists_tables={}
            self.table2dim={}



        '''

        example line_dict:
          {'dimensions': {'client_device': [2],
                    'client_version': [3],
                    'first_source': [0],
                    'first_sub_source': [1]},
         'grouping__id': [4],
         'metrics': {'pv': [6], 'uv': [5]}}

        '''
        line_dict = self.initSchema()
        cache_all_existed_tables={} #used for cache all existed tables

        warning_line=0
        warn_status = 0

        for line in f:
            check_status,check_msg,line=self.checkData(line,line_dict,cache_all_existed_tables)
            if check_status == 1:
                return err_json(check_msg)
            if check_status == 2:
                warn_status = 2
                warning_line+=1
                continue
            if check_status == 3:
                warn_status=2

            try:
                arr = line.split("\t")
                num_file+=1
                for key in line_dict["dimensions"].keys():
                    p = line_dict["dimensions"][key][0]
                    line_dict["dimensions"][key].append(arr[p])
                p = line_dict["grouping__id"][0]
                line_dict["grouping__id"].append(arr[p])
                for key in line_dict["metrics"].keys():
                    p = line_dict["metrics"][key][0]
                    line_dict["metrics"][key].append(arr[p])

                # insert into right table
                grouping_id = line_dict["grouping__id"][1]
                tmp_arr = self.getdimensionsWithGroupingID(grouping_id)
                if tmp_arr is None:
                    line_dict = self.initSchema()
                    continue
                dim_str = ",".join(tmp_arr)
                if self.custom_cdate == 1:
                    dim_str = self.custom_time_suffix[num_file-1] + ',' + dim_str


                if dim_str in self.exists_tables.keys():
                    target_table = self.exists_tables[dim_str]
                else:
                    msg="project %s %s.%s Dim set:%s can't find right table "%(self.metric_conf,self.cat,self.group["name"],dim_str)
                    logger.warn(msg)
                    warn_status=2
                    #return err_json(msg)


                self.dict_file[target_table].append(line_dict)

                # load data to mysql every cetern lines.
                if num_file % 2000 == 0:
                    try:
                        #导入数据大于开始检查主从延迟
                        if int(num_file)>=int(conf.LOAD_DATA_CHECK_NUM):
                            logger.info('load data num more than %s check mysql master-slave synch'%(str(num_file)))
                            slave_config = getMysqlConfigByAppName(self.db_name, 'slave',weight=self.db_weight)
                            slave_mysql=MmsMysql(slave_config)
                            slave_mysql.checkMysqlSynchDelay()
                            while(slave_mysql.checkMysqlSynchDelay(delay_second=300)):
                                import time
                                time.sleep(300)
                    except:
                        traceback.print_exc()
                        logger.exception('')

                    #自定义任务运行时间下
                    if self.custom_cdate == 1:
                        #获取table2dims, 去掉时间维度
                        for k,v in self.exists_tables.items():
                            k = k.split(',')
                            k.pop(0)
                            k = ','.join(k)
                            self.table2dim.update({v:k})

                        try:
                            if self.big_result:
                                msg = "大于200万条数据禁止做alter table操作"
                                return err_json(msg,logger=logger)
                            self.alterNewCol()
                        except AssertionError as e:
                            return err_json(e.message,logger=logger)

                    for key,value in self.dict_file.items():

                        if len(value)>0:
                            try:
                                logger.info("start to insert %s data into table %s" % (len(value), key))
                                self.insertEachTable(value,key)
                                logger.info("insert %s data into table %s successfully" % (len(value), key))
                            except Exception,ex:
                                msg="project %s insert data error" %self.metric_conf
                                logger.exception(msg)
                                return err_json(msg)
                            for e in self.exists_tables.values():
                                self.dict_file[e] = []
                    msg="load num %s" %num_file
                    logger.info(msg)
                line_dict = self.initSchema()


            except Exception,ex:
                logger.exception("catToMysql except")
                return err_json("catToMysql Exception:%s" %ex.message)

        #自定义任务运行时间下
        if self.custom_cdate == 1:
            #获取table2dims, 去掉时间维度
            for k,v in self.exists_tables.items():
                k = k.split(',')
                k.pop(0)
                k = ','.join(k)
                self.table2dim.update({v:k})

            try:
                self.alterNewCol()
            except AssertionError as e:
                return err_json(e.message,logger=logger)

        for key,value in self.dict_file.items():
            if len(value)>0:
                try:
                    logger.info("start to insert %s data into table %s" % (len(value), key))
                    self.insertEachTable(value,key)
                    logger.info("insert %s data into table %s successfully" % (len(value), key))
                except Exception,ex:
                    msg="project %s insert data error" %self.metric_conf
                    #logger.exception(str(value[:10]))
                    logger.exception(msg)
                    return err_json(msg)

        if warn_status == 2:
            return {'num':num_file,'status':conf.WARNING,'msg':'%s lines are data too long,omit loading into mysql' %warning_line}

        if int(self.storetype)==5:
            logger.info('update entry table start_time and end_time')
            #TODO 更新表
            for k,v in self.updateRangeTimeTable.items():
                self.updateTableDataRangeTime(v)

        return {'num':num_file,'status':conf.SUCCESS,'msg':"load data success."}


    def deleteExistsData(self):
        '''
            storetype=2表结构 默认实现
        '''
        data_mmsMysql = self.data_mmsMysql
        data_conn=data_mmsMysql.get_conn()
        data_cur=data_mmsMysql.get_cur()
        tables = []

        tables=self.table2dim.keys()

        cdate=self.dt
        cat = self.cat
        group_name = self.group["name"]
        storetype=self.storetype

        #项目user_access_count删除特殊处理
        if self.metric_conf=='user_access_count':
            for table_name in tables:
                sql='truncate table %s'%(table_name)
                data_cur.execute(sql)
                data_conn.commit()
        else:
            #删除某些数据
            for table_name in tables:
                met_list=[]

                not_metric_met=self.table2dim[table_name].split(',')
                not_metric_met.append('id')
                not_metric_met.append('fakecube_unique')
                not_metric_met.append('cdate')
                if 'minute'==self.schedule_level or 'hour'==self.schedule_level:
                    not_metric_met.append('hour')
                    not_metric_met.append('minute')
                not_metric_met += self.met_metric_list

                sql="show columns from %s"%table_name
                data_cur.execute(sql)
                for value in data_cur.fetchall():
                    if not value[0].lower() in not_metric_met:
                        met_list.append(value[0].lower())
                and_hour_sql=''
                if 'minute'==self.schedule_level or 'hour'==self.schedule_level:
                    and_hour_sql=" and hour='"+self.stat_hour+"' and minute='"+self.stat_minute+"' "

                where_cdate =''
                if self.custom_cdate ==1:
                    if self.group.has_key('custom_type') and self.group['custom_type']:
                        custom_type = self.group['custom_type']
                    else:
                        custom_type = 'range'
                    if custom_type == 'range':
                        cdate_custom_start, cdate_custom_end = self.getCustomTime()
                        if cdate_custom_start == cdate_custom_end:
                            where_cdate = "cdate='%s'" % cdate_custom_start
                            logger.info(
                                "delete existed data in %s custom time: cdate='%s'" % (table_name, cdate_custom_start))
                        else:
                            where_cdate = "cdate>='%s' and cdate<='%s'" % (cdate_custom_start, cdate_custom_end)
                            logger.info("delete existed date in %s custom time: cdate>='%s' and cdate<='%s'" % (
                                table_name, cdate_custom_start, cdate_custom_end))
                    else:
                        cdate_custom_single = self.getCustomTimeSingle()
                        where_cdate = "cdate in (%s)" % cdate_custom_single
                        logger.info(
                            "delete existed data in %s custom time: cdate in (%s)" % (table_name, cdate_custom_single))
                else:
                    where_cdate=" cdate='%s' "%(self.dt)
                    if self.group.has_key('schedule_interval') and self.group['schedule_interval']:
                        #如果是月截取出月份
                        r = re.compile(r'^(\d+)(_(\d+)+)?$')
                        res=r.findall(str(self.group['schedule_interval']))
                        if res:
                            res=res[0]
                            func=lambda x:str(x) if len(str(x))==2 else str(0)+str(x)
                            if int(res[0])==30:
                                tmp_date=datetime.datetime.strptime(self.dt,'%Y-%m-%d')
                                tmp,month_days=calendar.monthrange(tmp_date.year,tmp_date.month)
                                cdate_start='%s-%s-%s'%(tmp_date.year,func(tmp_date.month),'01')
                                cdate_end='%s-%s-%s'%(tmp_date.year,func(tmp_date.month),str(month_days))
                                where_cdate=" cdate>='%s' and cdate<='%s' "%(cdate_start,cdate_end)
                            if int(res[0])==7:
                                tmp_date=datetime.datetime.strptime(self.dt,'%Y-%m-%d')
                                cdate_start=tmp_date-datetime.timedelta(days=tmp_date.weekday())
                                cdate_start=cdate_start.strftime('%Y-%m-%d')
                                where_cdate=" cdate>='%s' and cdate<='%s' "%(cdate_start,self.dt)


                #删除控制条数设为3000
                where_sql = " where %s" % (where_cdate)
                where_sql += and_hour_sql
                for value in met_list:
                    where_sql+= ' and `'+value+ '` is null'

                delete_sql="delete from "+ table_name+ where_sql + ' limit %s' % conf.UD_CONTROL_NUMBER
                logger.info("delete sql: %s" % delete_sql)
                d_count = 0
                while True:
                    data_cur.execute(delete_sql)
                    data_conn.commit()
                    affect_nr = data_cur.rowcount
                    logger.info("delete time= %s, affect_nr=%s" % (d_count, affect_nr))
                    if affect_nr ==0:
                        logger.info("finish the delete process")
                        break
                    d_count +=1

                update_sql='update '+table_name+' set '
                for value in self.met_metric_list:
                    update_sql+=value+'=null,'

                update_sql=update_sql.strip(',')
                update_sql+= " where %s "%(where_cdate)
                update_sql+=and_hour_sql
                nr_met_metric_list = len(self.met_metric_list)
                if nr_met_metric_list !=0:
                    update_sql += 'and ('
                    for index, value in enumerate(self.met_metric_list):
                        if index == nr_met_metric_list-1:
                            update_sql+= value + ' is not null'
                        else:
                            update_sql += value+ ' is not null or '
                    update_sql += ' )'

                update_sql+= ' limit %s' % conf.UD_CONTROL_NUMBER

                logger.info('update sql: %s' % update_sql)
                u_count = 0
                while True:
                    data_cur.execute(update_sql)
                    data_conn.commit()
                    affect_nr = data_cur.rowcount
                    logger.info("update time= %s, affect_nr=%s" % (u_count, affect_nr))
                    if affect_nr ==0:
                        logger.info("finish the update process")
                        break
                    u_count +=1


        data_mmsMysql.conn_close()

        logger.info('delete data succ')

    def insertEachTable(self,data, table):
        '''
            storetype=2表结构 默认实现
        '''
        data_mmsMysql = self.data_mmsMysql
        data_conn=data_mmsMysql.get_conn()
        data_cur=data_mmsMysql.get_cur()

        storetype=self.storetype

        dim = self.table2dim[table].split(',')
        if dim==['']:
            dim=[]

        met,result=self.insertEachTable_v2(data,table,dim)



        s_str= []
        w_met= []
        for e in met:
            s_str.append('%s')
            e=e.strip('`')
            w_met.append('`'+e+'`')
        template=','.join(s_str)
        met_str = ",".join(w_met)

        sql="insert into "
        sql +=  table + "(%s) values " % (met_str)
        sql = sql +'('+template +')'

        sql += ' ON DUPLICATE KEY UPDATE '
        for e in self.met_metric_list:
                sql+=e+'=VALUES('+e+'),'
        sql=sql.strip(',')
        #print sql,result
        lock_file = conf.LOCK_PATH + self.metric_conf
        l = SingletonLock(lock_file)
        l.ex_lock()
        data_cur.execute('SET NAMES utf8mb4')
        data_cur.executemany(sql,result)
        data_conn.commit()
        data_conn.close()

        l.unlock()


    def insertEachTable_v2(self,data, table,dim):

        columns_list=self.get_table_columns(table)
        met = ["cdate"]
        non_day_schedule_flag = False

        common_line_data=[self.dt]
        if 'minute'==self.schedule_level or 'hour'==self.schedule_level:
            met.append('hour')
            met.append('minute')
            common_line_data=[self.dt,self.stat_hour,self.stat_minute]
            non_day_schedule_flag = True

        for i in dim:
            met.append(i)
        met=met+self.met_metric_list

        if 'fakecube_unique' in columns_list:
            met.insert(0,'fakecube_unique')

        result_data=[]
        for  line_dict in data:
            line_data=[]
            if self.custom_cdate ==0:
                line_data=common_line_data[:]
            else:
                #cdate作为维度单独处理一下
                cdate_value = line_dict['dimensions']['cdate'][1]
                cdate_hour = 0
                cdate_minute = 0
                if not non_day_schedule_flag:
                    line_data.append(cdate_value)
                else:
                    line_data.append(cdate_value)
                    line_data.append(cdate_hour)
                    line_data.append(cdate_minute)

            try:
                #维度组合md5作为唯一索引
                fakecube_unique_val=[]
                data_include = common_line_data if self.custom_cdate == 0 else line_data
                for i in data_include:
                    fakecube_unique_val.append(str(i))

                for i  in  dim:
                    j =  line_dict["dimensions"][i][1]
                    line_data.append(j)
                    fakecube_unique_val.append(str(j))
                #修复bug特殊兼容逻辑
                tmp_group_name='%s.%s.%s'%(self.project,self.cat,self.group['name'])
                white_list=['Member.coin.userinfo_improve']
                if self.dt>'2015-09-21' and self.dt<'2015-10-09' and tmp_group_name not in white_list:
                    fakecube_unique_val.sort()
                fakecube_unique_val=mms_md5(''.join(fakecube_unique_val))

                if 'fakecube_unique' in columns_list:
                    line_data.insert(0,fakecube_unique_val)

                for i in self.metric_list:
                    j = line_dict['metrics'][i][1].strip('\n')
                    j = 0.0 if j == 'NULL' else j
                    line_data.append(j)

            except:
                import traceback
                #print self.metric_list,line_dict
                raise  Exception
            result_data.append(tuple(line_data))

        return met,result_data

    def alterNewCol(self):
        data_mmsMysql = self.data_mmsMysql#MmsMysql(mmsMysqlConf.MMS_DB_DATA)
        data_conn=data_mmsMysql.get_conn()
        data_cur=data_mmsMysql.get_cur()

        metrics_mms_conf=MmsConf()
        metrics_inf=metrics_mms_conf.get_app_metrics_params(self.metric_conf,self.cat,self.group["name"])
        metrics2type={}
        for k,v in metrics_inf.items():
            if v.has_key('type') and v['type']:
                metric_type=v['type']
                if self.type_dict.has_key(metric_type) and self.type_dict[metric_type]:
                    metrics2type[k]=self.type_dict[metric_type]
        func=lambda x:str(metrics2type[x]) if metrics2type.has_key(x) else self.type_dict['decimal']
        exists_tables=[]
        metric_list=self.met_metric_list
        import copy

        alter_white_list=['run_meilishuo,data_type','pro_tuan,','Member,dim','mob_poster,tag_word','hot_selling,goods_first_catalog,goods_second_catalog,goods_three_catalog,partner_area_id,shop_id_t1,twitter_id_t1']
        alter_white_list.append('poster_base,first_cata,second_cata,tag_word,third_cata')
        if self.custom_cdate == 1:
            for k,v in self.exists_tables.items():
                if isinstance(v, list):
                    #自定义数据时间下首先获得维度组合所对应的table list
                    for t in v:
                        exists_tables.append((k, t))
                else:
                    #自定义数据时间下loaddata会再次调用alterColumn， 此时维度组合会绑定cdate
                    m = k.split(',')
                    m.pop(0)
                    k = ','.join(m)
                    exists_tables.append((k,v))
        else:
            exists_tables = self.exists_tables

        if isinstance(exists_tables, dict):
            exists_tables = exists_tables.items()

        for k,table_name in exists_tables:
            sql="show columns from %s"%table_name
            data_cur.execute(sql)
            tmp_list=copy.deepcopy(metric_list)
            tmp_count_col=[]
            for value in data_cur.fetchall():
                r_value=str(value[0]).lower()
                tmp_count_col.append(r_value)
                if  r_value in tmp_list:
                    tmp_list.remove(r_value)
            tmp_add_list=[]
            if conf.ALTER_SWITCH==True and len(tmp_list)>0:

                if conf.ALTER_SWITCH_COL==True:
                    tmp_project_dim=','.join((self.project,k))
                    if tmp_project_dim not in alter_white_list:
                        tmp_count_col=tmp_count_col+tmp_list
                        tmp_count_col=list(set(tmp_count_col))
                        #最大列宽不可超过100列
                        if len(tmp_count_col)>int(conf.TABLE_MAX_COLS):
                            assert False,'维度组合中维度个数和历史累计指标个数之和不可超过150。'

                if self.custom_cdate == 0:
                    if conf.ALTER_SWITCH_NUM==True and self.big_result == True:
                            assert False,'任务结果数不可大于200万。'

                if conf.ALTER_SWITCH_CHECK_SLAVE==True:
                    #检查主从同步情况

                    logger.info("alter table {} check database {} master-slave synch".format(table_name,self.db_name))
                    slave_config = getMysqlConfigByAppName(self.db_name, 'slave',weight=self.db_weight)
                    slave_mysql=MmsMysql(slave_config)
                    slave_mysql.checkMysqlSynchDelay()
                    while(slave_mysql.checkMysqlSynchDelay(delay_second=0)):
                        import time
                        time.sleep(300)

            for k1 in tmp_list:
                tmp_k='ADD `%s` %s NULL DEFAULT NULL'%(k1,func(k1))
                tmp_add_list.append(tmp_k)

            if tmp_add_list:
                alter_pre='ALTER TABLE `%s` '%(table_name)
                tmp_add_list_str=','.join(tmp_add_list)
                sql='%s%s;'%(alter_pre,tmp_add_list_str)
                logger.info('alter sql:%s'%(sql))
                data_cur.execute(sql)
                data_conn.commit()

        data_mmsMysql.conn_close()

    def checkTableRowsAndSize(self,table_name,limit_rows=20000000,limit_size=1024):
        '''

        检查数据表行数 超过2000万 大小超过 *M
        大于阀值 True 否则 False
        '''
        try:
            data_mmsMysql = self.data_mmsMysql#MmsMysql(mmsMysqlConf.MMS_DB_DATA)
            data_conn=data_mmsMysql.get_conn()
            data_cur=data_mmsMysql.get_cur()

            sql='''
                select * from information_schema.tables where table_schema='{}' and table_name='{}'
            '''.format('metric',str(table_name))

            data_cur.execute(sql)
            columns=data_cur.description

            table={}
            for value in data_cur.fetchall():
                for (index,column) in enumerate(value):
                    table[str(columns[index][0]).lower()] = column
            table_rows=0
            data_length=0
            if table:
                table_rows=int(table['table_rows'])
                data_length=int(table['data_length'])/1024/1024

            if table_rows>limit_rows or data_length>limit_size:
                logger.info('table {} rows {} data_size {}'.format(table_name,table_rows,data_length))
                return True
            return False
        except:
            logger.exception('show table {} rows error'.format(table_name))
            return False



