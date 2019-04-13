#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'
import os
'''
    mms配置
'''


#分布式配置文件路径
cur_abs_dir = os.path.dirname(os.path.abspath(__file__))
SCHEDULE_CONF_PATH= os.path.dirname(cur_abs_dir)+'/conf/schedule.conf'
STORE_DB_PATH = os.path.dirname(cur_abs_dir)+'/conf/store.conf'


#run运行结果状态
WAITING=1
READY=2
RUNNING=3
HIVEEND=4  #hive执行完成
SUCCESS=5
FAILED=6
WARNING=7
OVERTIME=8
CHECKING=9 #检查表是否准备
KILLED=11#手动杀死

RUNNER_CHECK_DAY_HOUR = 0#默认0点开始检查天级别任务
TASK_CHECK_HOUR = 6 #默认每天6点检查
CRONTAB_TASK_HOUR=1#每天1点运行
CRONTAB_TASK_MINUTE = 4 # 每天启动运行分钟数
#每天优先级高的任务和正常任务成功数比例 乘以100
NORMAL_TTL=40
SPECIAL_TTL=80
MAX_RUN_JOB_NUM= 100
TIME_WAITING_PER_PROCESS = 3

ALTER_SWITCH=True#是否开启alter限制功能
ALTER_SWITCH_COL=True#是否开启alter列限制功能
ALTER_SWITCH_NUM=True#是否开启alter 文件200万限制功能
ALTER_SWITCH_CHECK_SLAVE=False#是否开启检查主从是否同步
ALTER_FILE_NUM=2000000#超过200万数据禁止alter
TABLE_MAX_COLS=300#表最大列数
WHITE_PROJECT_LIST='user_access_count,higo_detail'

TASK_FAIL_NUM=3
MIN_FREE_MEMORY=2048 #最小内存阀值10G



#hql重试次数
RETRY_TIMES=3
#限制同时运行的run.py的个数
QUEUE_TABLE = 'mms_run_log'  #执行队列表
RUN_lOOP_INTERVEL  = 300      #如果执行队列为空，等待300s后继续轮询队列
MULTI_RUN_LIMIT = 50          #同时运行的hql个数不超过50
LIMIT = 1000 #默认展示日志条数
SPECIAL_RESULT_NUM_MIN=2000000 #白名单任务结果行数最小值，正常任务结果最大值
SPECIAL_RESULT_NUM_MAX=5000000 #白名单任务结果函数最大值

#删除更新控制数目
UD_CONTROL_NUMBER = 3000


#导入数据检查主从延迟阀值
LOAD_DATA_CHECK_NUM=2000


#实时项目配置
REALTIME_REDIS_TAG_REDIS_HOST='r-bp125f902adef174297.redis.rds.aliyuncs.com'
REALTIME_REDIS_TAG_REDIS_PORT=6379
REALTIME_REDIS_TAG_REDIS_PASSWORD='eUcGBPfnawJRYh14'
REALTIME_REDIS_TAG_REDIS_DB=1

#手动提交限制
MANUAL_RUN_TASK_NUM=15#手动提交任务个数
MANUAL_SPECIAL_RUN_TASK_NUM=60#特殊用户提交任务个数
MANUAL_USER_WHITE_LIST = ['yangyulong@xiaozhu.com', 'yangzongqiang@xiaozhu.com']
MANUAL_USER_SPECIAL_LIST = ['zhangyipeng@xiaozhu.com']

#项目配置限制
MMS_CONF_MAX_APP_CONF=30
