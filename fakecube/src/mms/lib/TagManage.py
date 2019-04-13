#!/usr/bin/env python
# -*- coding:utf8 -*-
"""
    @author: pengbangzhong
    @file: TagManage.py
    @doc 数据依赖tag管理服务

"""
import os,sys,argparse,time
reload(sys)
sys.setdefaultencoding('utf8')
import traceback
from kazoo.client import KazooClient

#zookeeper集群地址
ZK_HOST="10.8.9.12:2181"

class TagManage(object):

    '''数据依赖tag管理服务
        创建tag
        删除tag

    Attributes:
        tag:指定生成tag key
        day:指定生成tag时间 天 格式2017-04-12 必填
        hour:指定生成tag时间 小时 格式24小时制 00 选填
        interval:脚本每次检测时间间隔(秒),默认300秒 5分钟 选填
        check_num: 指定校验次数,达到校验次数异常退出 默认无限次校验直到tag生成

    '''
    def __init__(self,tag,day,hour=None,interval='300',minute='00',check_num=None,schedule_type='day'):
        self.zk_client=None
        self.tag=str(tag).lower()
        self.day=day
        self.hour=hour
        self.minute=minute
        self.interval=interval
        self.check_num=check_num
        self.tag_path=''
        self.tag_value=0 #0为无效1有效
        self.tag_root='/di_tag'
        self.tag_exist=False
        self.is_hour=False
        self.schedule_type=schedule_type#day,hour
        self.tag_day_path=None
        self.init()

    def init(self):
        #初始化zk客户端
        self.__init_zk_client()

        if self.interval and self.interval.isdigit():
            self.interval=int(self.interval)
        else:
            self.interval=300

        if self.schedule_type=='hour':
            self.is_hour=True

        if not self.hour:
            self.hour=0


        if self.hour and self.hour<0 and self.hour>24:
            print 'param hour={} is reset to 0.'.format(str(self.hour))
            self.hour=0
        self.hour = str(self.hour).zfill(2)

        self.__init_tag_path()


    def __init_zk_client(self):
        '''
        初始化zookeeper client,失败重试一次
        '''
        try:
            self.zk_client = KazooClient(hosts=ZK_HOST)
            self.zk_client.start()
        except Exception,e:
            traceback.print_exc()
            self.zk_client = KazooClient(hosts=ZK_HOST)
            self.zk_client.start()


    def __init_tag_path(self):
        '''生成tag key
        tag分为3种类型,hive表,hdfs,localfile:
            hive表: db.table_name eg: stage.s_control_acc_credit_data  ->  tagkey:stage_s_control_acc_credit_data
            hdfs: hdfs地址 eg: hdfs://bi-namenode-1:9000/hive/stage/s_control_acc_credit_data -> tagkey:hdfs_bi-namenode-1:9000_hive_stage_s_control_acc_credit_data
            localfile: file://机器ip/home/hadoop/pengbangzhong.ok eg:file://10.252.225.97/home/hadoop/ok ->tagkey:file_10.252.225.97_home_hadoop_ok
        '''
        self.tag=str(self.tag).lower()
        join_arr=[]
        if self.tag.startswith('hdfs://'):
            join_arr.append('hdfs')
            join_arr+=self.tag.split('hdfs://')[1].split('/')

        elif self.tag.startswith('file://'):
            join_arr.append('file')
            join_arr+self.tag.split('file://')[1].split('/')
        else:
            db_table_arr=self.tag.split('.')
            if len(db_table_arr)==1:
                print 'database defaults to "default" '
                join_arr.append('default')
                join_arr.append(db_table_arr[0])
            elif len(db_table_arr)==2:
                join_arr.append(db_table_arr[0])
                join_arr.append(db_table_arr[1])
            else:
                error_msg='''
                    tag分为3种类型,hive表,hdfs,localfile:
                    hive表: db.table_name eg: stage.s_control_acc_credit_data  ->  tagkey:stage_s_control_acc_credit_data
                    hdfs: hdfs地址 eg: hdfs://bi-namenode-1:9000/hive/stage/s_control_acc_credit_data -> tagkey:hdfs_bi-namenode-1:9000_hive_stage_s_control_acc_credit_data
                    localfile: file://机器ip/home/hadoop/pengbangzhong.ok eg:file://10.252.225.97/home/hadoop/ok ->tagkey:file_10.252.225.97_home_hadoop_ok
                '''
                print error_msg
                exit(1)
        date_tag_arr=[self.day]
        if self.is_hour:
            date_tag_arr.append(self.hour)
        date_tag_name='_'.join(date_tag_arr)
        tag_key='_'.join(join_arr)
        self.tag_path=os.path.join(self.tag_root,tag_key,date_tag_name)
        self.tag_day_path=os.path.join(self.tag_root,tag_key,self.day)

    def __check_day_all_hour_tag(self):
        '''
        根据小时tag,判断天tag是否生成
        '''
        tag_hour_paths = [self.tag_day_path + '_' +
                          str(i).zfill(2) for i in range(0, 24)]
        rs_hour_path = ''
        for hour_path in tag_hour_paths:
            is_exist = self.is_tag_exist(hour_path)
            if not is_exist:
                rs_hour_path = hour_path
                print 'hour tag:{} is not ready.'.format(hour_path)
                return False, rs_hour_path
        return True, rs_hour_path


    def is_tag_exist(self,tag_path):
        try:
            if self.zk_client.exists(tag_path):
                return True
        except Exception,e:
            traceback.print_exc()
            return False

    def create_tag(self):
        '''
        创建tag

        '''
        self.tag_value=1

        if not self.is_tag_exist(self.tag_path):
            self.zk_client.ensure_path(self.tag_path)
        node_stat=self.zk_client.set(self.tag_path,str.encode(str(self.tag_value)))

        if node_stat:
            if self.is_hour and self.__check_day_tag_by_hour():
                if not self.is_tag_exist(self.tag_day_path):
                    self.zk_client.ensure_path(self.tag_day_path)
                day_node_ret=self.zk_client.set(self.tag_day_path, str.encode(str(self.tag_value)))
                if day_node_ret:
                    print 'create day tag {} success.'.format(self.tag_day_path)
                else:
                    print 'create day tag {} failed.'.format(self.tag_day_path)
            print 'create tag {} success.'.format(self.tag_path)
            return True
        else:
            print 'create tag {} failed.'.format(self.tag_path)
            return False




    def delete_tag(self):
        '''
        删除tag
        :return:
        '''
        self.tag_value=0
        node_stat=None
        if self.is_tag_exist(self.tag_path):
            node_stat = self.zk_client.set(self.tag_path,str.encode(str(self.tag_value)))
        if node_stat and self.is_hour and not self.__check_day_tag_by_hour():
            if self.is_tag_exist(self.tag_day_path):
                self.zk_client.set(self.tag_day_path, str.encode(str(0)))

        print 'delete tag {} success.'.format(self.tag_path)
        return self.tag_value

    def check_tag_status(self):

        if not self.is_tag_exist(self.tag_path):
            print 'day tag:{} is not ready.'.format(self.tag_path)
            return False,True
        else:
            data,stat=self.zk_client.get(self.tag_path)
            data=bytes.decode(data)
            if int(data)==1:
                return True,False
            else:
                return False,False

    def __check_day_tag_by_hour(self):
        '''
        根据小时tag,判断天tag是否生成
        '''
        tag_hour_paths=[self.tag_day_path+'_'+str(i).zfill(2) for i in range(0,23)]
        for hour_path in tag_hour_paths:
            is_exist=self.is_tag_exist(hour_path)
            if not is_exist:
                print 'hour tag:{} is not ready.'.format(hour_path)
                return False
            else:
                data, stat = self.zk_client.get(self.tag_path)
                data = bytes.decode(data)
                if int(data)==1:
                    return True
                else:
                    return False
        return True


    def check_tag(self,is_wait=True):
        '''

        :return:True,True
        '''

        print 'check day tag.'
        is_ready, is_next=self.check_tag_status()
        if is_ready:
            return is_ready,False
        print 'check hour tag.'
        is_ready, is_next = self.__check_day_all_hour_tag()
        if is_ready:
            return is_ready,False
        index = 0
        while (not is_ready and is_wait):
            msg = "tag {} stat day:{} hour:{}  not ready ".format(self.tag, self.day, self.hour)
            print msg
            index += 1
            if self.check_num and int(self.check_num) != 0 and index >= int(self.check_num):
                msg = 'retry {} times,except exit.'.format(str(self.check_num))
                print msg
                sys.exit(1)
            time.sleep(self.interval)
            is_ready, is_next = self.check_tag_status()

        return is_ready,False

    def stop(self):
        self.zk_client.stop()


    def execute(self,type='check'):
        my_type=['check','create','delete']
        if type not in my_type:
            print '操作类型错误.'
            return

        if type=='check':
            return self.check_tag()
        elif type=='create':
            return self.create_tag()
        elif type=='delete':
            return self.delete_tag()




def getArgs():

    arg_parser = argparse.ArgumentParser()
    arg_parser.add_argument('-t', '--operate', default='check',choices=["create", "check", "delete"], help='设置操作类型', required=True)
    arg_parser.add_argument('-d', '--day', help='指定时间天.eg:2017-03-21', required=True)
    arg_parser.add_argument('-o', '--hour', help='指定时间小时,调度类型为天时小时为00', required=False)
    arg_parser.add_argument('-a', '--tag', help='tag值', required=True)
    arg_parser.add_argument('-i', '--interval', help='重试间隔时间单位秒,默认300', required=False)
    arg_parser.add_argument('-n', '--check_num', help='失败重试次数,默认一直重试', required=False)
    args = arg_parser.parse_args()
    return args


if __name__ == '__main__':
    args = getArgs()
    schedule_type='day'
    if args.hour:
        schedule_type='hour'

    tagManage=TagManage(args.tag,args.day,hour=args.hour,interval=args.interval,check_num=args.check_num,schedule_type=schedule_type)
    tagManage.execute(args.operate)
