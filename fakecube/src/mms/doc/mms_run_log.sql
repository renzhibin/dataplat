create table `mms_run_log` (
	`id` 	      bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `app_name`    varchar(128) DEFAULT NULL,
    `stat_date`   varchar(20) DEFAULT NULL COMMENT '执行日期',
    `start_time`  datetime DEFAULT NULL COMMENT '执行开始时间',
    `end_time`    datetime DEFAULT NULL COMMENT '执行结束时间',
    `status`      tinyint  DEFAULT NULL COMMENT '1:WAITING,2:REDAY,3:RUNNING,4:HIVEEND,5:SUCCESS,6:FAILED,7:WARNING',
    `create_time` datetime DEFAULT now() COMMENT '日志生成时间',
    `run_module`  varchar(1024) DEFAULT NULL COMMENT '执行指定hql',
    `step`        varchar(1024)  DEFAULT 'all'  COMMENT 'all,hive,mysql',
    `is_test`     boolean DEFAULT FALSE COMMENT '是否测试用',
    `priority`    tinyint DEFAULT 0  COMMENT '执行优先级',
    PRIMARY KEY(`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='run.py日志表'

alter table mms_run_log add column data_size int DEFAULT 0 COMMENT '导入数据条数'
alter table mms_run_log add column load_time_spend decimal(10,8) DEFAULT NULL COMMENT '导入耗时时长'


create table `mms_run_monitor` (
`id`  bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
`log_id` bigint(20) unsigned NOT NULL COMMENT 'mms_run_log外键',
`app_name` varchar(128) DEFAULT NULL COMMENT '项目名称',
`mob_name` varchar(128) DEFAULT NULL COMMENT 'CAT.GROUP[NAME]',
`time_spend` decimal(10,8) DEFAULT NULL COMMENT '导入耗时',
`data_size` int DEFAULT 0 COMMENT '导入数据条数',
PRIMARY KEY(`id`),
FOREIGN KEY(log_id) REFERENCES mms_run_log(id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='结果监控表'



