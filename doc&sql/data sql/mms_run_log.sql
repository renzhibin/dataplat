CREATE TABLE `mms_run_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `app_name` varchar(128) DEFAULT NULL,
  `stat_date` varchar(20) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL COMMENT '执行开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '执行结束时间',
  `status` tinyint(4) DEFAULT '1' COMMENT 'WAITING=1,READY=2,RUNNING=3,HIVEEND=4,SUCCESS=5,FAILED=6,WARNING=7',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '日志生成时间',
  `run_module` varchar(1024) DEFAULT NULL COMMENT '执行指定hql',
  `step` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'all',
  `is_test` tinyint(1) DEFAULT '0' COMMENT '是否测试用',
  `priority` tinyint(4) DEFAULT '0' COMMENT '执行优先级',
  `data_size` int(11) DEFAULT '0' COMMENT '导入数据条数',
  `load_time_spend` decimal(10,5) DEFAULT NULL,
  `creater` varchar(128) DEFAULT NULL,
  `schedule_level` varchar(10) DEFAULT 'day' COMMENT '任务调度级别 day,minute',
  `ready_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '任务就绪时间点',
  `repeat_num` int(11) DEFAULT '1' COMMENT '任务失败重试次数',
  `params` mediumblob COMMENT '任务相关参数',
  `job_type` varchar(100) DEFAULT 'default' COMMENT '任务类型 default，hql',
  `task_queue` varchar(100) DEFAULT '' COMMENT '任务运行hadoop队列',
  `submitter` varchar(128) DEFAULT '' COMMENT '任务提交者',
  `last_checked_time` datetime DEFAULT '0001-00-00 00:00:00' COMMENT '上次检查时间',
  `conf_name` varchar(128) DEFAULT '' COMMENT '运行任务机器配置',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `app_name` (`app_name`,`stat_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1078671 DEFAULT CHARSET=utf8 COMMENT='run.py日志表';
