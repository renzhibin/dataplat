# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 192.168.60.49 (MySQL 5.6.25)
# Database: metric_meta
# Generation Time: 2017-03-07 08:00:46 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table mms_app_conf
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mms_app_conf`;

CREATE TABLE `mms_app_conf` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `app_name` varchar(128) NOT NULL DEFAULT '' COMMENT '项目名',
  `category_name` varchar(128) NOT NULL DEFAULT '' COMMENT 'hql任务分类名',
  `hql_name` varchar(128) NOT NULL DEFAULT '' COMMENT 'hql任务名',
  `dimensions` mediumblob COMMENT '维度',
  `metrics` mediumblob COMMENT '指标',
  `other_params` mediumblob COMMENT 'hql任务其他配置参数',
  `is_run` tinyint(4) DEFAULT '0' COMMENT '0不运行，1运行',
  `is_delete` tinyint(4) DEFAULT '0' COMMENT '1删除，0未删除',
  `is_schedule` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 报表类任务 1 调度类任务',
  `data_table_name` varchar(128) NOT NULL DEFAULT '' COMMENT '写入数据表名',
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_name` (`app_name`,`category_name`,`hql_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目hql任务配置信息';



# Dump of table mms_conf
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mms_conf`;

CREATE TABLE `mms_conf` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `date_s` datetime DEFAULT NULL COMMENT '起始日期',
  `date_e` datetime DEFAULT NULL COMMENT '终止日期',
  `date_n` datetime DEFAULT NULL COMMENT '完成日期',
  `creater` varchar(2014) DEFAULT NULL COMMENT ' 创建人',
  `appname` varchar(128) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `explain` varchar(2048) DEFAULT NULL,
  `cn_name` varchar(128) DEFAULT NULL,
  `storetype` int(11) NOT NULL DEFAULT '2' COMMENT '1老mysql表2新mysql表',
  `editor` varchar(2014) DEFAULT NULL COMMENT '修改人',
  `authtype` int(11) NOT NULL DEFAULT '1' COMMENT '权限类型',
  `authuser` varchar(2048) NOT NULL DEFAULT '' COMMENT '授权人',
  `mysql_weight` int(11) NOT NULL DEFAULT '1' COMMENT '查询从库权重类型',
  `update_weight_time` datetime DEFAULT NULL COMMENT '更新weight字段时间',
  `conf` mediumblob COMMENT '项目所有配置信息',
  `weight_update_log` varchar(128) NOT NULL DEFAULT '' COMMENT 'mysql请求升级降级说明',
  `store_db` varchar(128) NOT NULL DEFAULT 'metric1' COMMENT '多库存储tag',
  PRIMARY KEY (`id`),
  UNIQUE KEY `appname` (`appname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='app任务配置表';



# Dump of table mms_run_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mms_run_log`;

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
  `ready_time` datetime DEFAULT '1990-01-01 00:00:00' COMMENT '任务就绪时间点',
  `repeat_num` int(11) DEFAULT '1' COMMENT '任务失败重试次数',
  `params` mediumblob COMMENT '任务相关参数',
  `job_type` varchar(100) DEFAULT 'default' COMMENT '任务类型 default，hql',
  `task_queue` varchar(100) DEFAULT '' COMMENT '任务运行hadoop队列',
  `submitter` varchar(128) DEFAULT '' COMMENT '任务提交者',
  `last_checked_time` datetime DEFAULT '1990-01-01 00:00:00' COMMENT '上次检查时间',
  `conf_name` varchar(128) DEFAULT '' COMMENT '运行任务机器配置',
  `second_check` varchar(20) DEFAULT NULL COMMENT '二级校验时间',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `app_name` (`app_name`,`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='run.py日志表';



# Dump of table mms_run_monitor
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mms_run_monitor`;

CREATE TABLE `mms_run_monitor` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `log_id` bigint(20) unsigned NOT NULL COMMENT 'mms_run_log外键',
  `app_name` varchar(128) DEFAULT NULL COMMENT '项目名称',
  `mob_name` varchar(128) DEFAULT NULL COMMENT 'CAT.GROUP[NAME]',
  `time_spend` decimal(10,8) DEFAULT NULL COMMENT '导入耗时',
  `data_size` int(11) DEFAULT '0' COMMENT '导入数据条数',
  PRIMARY KEY (`id`),
  KEY `log_id` (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='结果监控表';



# Dump of table mms_table_name
# ------------------------------------------------------------

DROP TABLE IF EXISTS `mms_table_name`;

CREATE TABLE `mms_table_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主建',
  `en_name` varchar(255) NOT NULL DEFAULT '' COMMENT '英文名',
  `cn_name` varchar(255) DEFAULT '' COMMENT '中文名',
  PRIMARY KEY (`id`),
  UNIQUE KEY `en_name` (`en_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='表名中英文对照表';



# Dump of table t_app_token
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_app_token`;

CREATE TABLE `t_app_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `app_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `project_name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `token_val` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Dump of table t_eel_admin_relation_report
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_eel_admin_relation_report`;

CREATE TABLE `t_eel_admin_relation_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `level_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='功能角色关系表';



# Dump of table t_eel_admin_relation_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_eel_admin_relation_user`;

CREATE TABLE `t_eel_admin_relation_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `group_defaulth_have` tinyint(4) DEFAULT NULL COMMENT '1普通用户 2助手 3角色管理员',
  PRIMARY KEY (`id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户角色关系表';



# Dump of table t_eel_admin_role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_eel_admin_role`;

CREATE TABLE `t_eel_admin_role` (
  `role_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(200) DEFAULT NULL COMMENT '角色名称',
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='角色表';



# Dump of table t_favorites_table
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_favorites_table`;

CREATE TABLE `t_favorites_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `form_name` varchar(100) DEFAULT NULL,
  `form_explain` varchar(100) DEFAULT NULL,
  `project` varchar(100) DEFAULT NULL,
  `group` varchar(100) DEFAULT NULL,
  `metric` varchar(2045) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `owner` varchar(100) DEFAULT NULL,
  `permit` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_name` (`form_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='favorites_table';



# Dump of table t_query_data_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_query_data_log`;

CREATE TABLE `t_query_data_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `cdate` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '日志生成时间',
  `token_name` varchar(40) COLLATE utf8mb4_bin NOT NULL COMMENT 'token名',
  `project_name` varchar(100) COLLATE utf8mb4_bin DEFAULT '' COMMENT '项目名',
  `group` varchar(8000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `metric` varchar(8000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='数据查询日志记录表';



# Dump of table t_rely_task
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_rely_task`;

CREATE TABLE `t_rely_task` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `task` varchar(128) DEFAULT NULL COMMENT '任务名',
  `status` tinyint(4) DEFAULT '1' COMMENT 'WAITING=1,READY=2,RUNNING=3,HIVEEND=4,SUCCESS=5,FAILED=6,WARNING=7',
  `creater` varchar(64) DEFAULT NULL COMMENT '负责人',
  `plat` varchar(64) DEFAULT NULL COMMENT '平台',
  `start_time` datetime DEFAULT NULL COMMENT '执行开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '执行结束时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `data_size` int(20) DEFAULT '0' COMMENT '数据量',
  `is_vaild` int(11) DEFAULT '1' COMMENT '1有效，0无效',
  PRIMARY KEY (`id`),
  UNIQUE KEY `task` (`task`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务信息表';



# Dump of table t_rely_topo
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_rely_topo`;

CREATE TABLE `t_rely_topo` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `task` varchar(128) DEFAULT NULL COMMENT '任务名',
  `rely_task` varchar(128) DEFAULT NULL COMMENT '依赖表',
  `ass_table` varchar(128) DEFAULT NULL COMMENT '任务关联表',
  `rely_type` varchar(128) DEFAULT NULL COMMENT '依赖任务类型：表名table，任务名task',
  `token` varchar(128) DEFAULT NULL COMMENT 'token名',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `task` (`task`),
  KEY `rely_task` (`rely_task`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='依赖任务关系';



# Dump of table t_role_behavior_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_role_behavior_log`;

CREATE TABLE `t_role_behavior_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `cdate` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `user_name` varchar(40) COLLATE utf8mb4_bin DEFAULT '' COMMENT '用户名',
  `user_action` varchar(100) COLLATE utf8mb4_bin DEFAULT '',
  `report_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_action` (`user_action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户操作角色记录表';



# Dump of table t_stat_entry_table
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_stat_entry_table`;

CREATE TABLE `t_stat_entry_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `metric_conf` varchar(100) DEFAULT NULL COMMENT '项目',
  `group_keys` varchar(2048) DEFAULT '' COMMENT '纬度组合',
  `table_name` varchar(100) DEFAULT NULL COMMENT '存储表',
  `storetype` int(11) NOT NULL DEFAULT '1' COMMENT '1老mysql表2新mysql表',
  `suffix` varchar(100) DEFAULT NULL,
  `schedule_level` varchar(10) DEFAULT 'day' COMMENT '任务调度级别 day,minute',
  `start_time` varchar(100) DEFAULT '',
  `end_time` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `metric_conf_cat` (`metric_conf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='mms入口表';



# Dump of table t_table_tag_status
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_table_tag_status`;

CREATE TABLE `t_table_tag_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `target_prefix` varchar(128) DEFAULT NULL COMMENT '如果目标数据为文件形式则表示为目录名;数据表则为数据库名',
  `target` varchar(128) DEFAULT NULL COMMENT '文件名前缀或数据表名',
  `ready_date` varchar(20) DEFAULT '' COMMENT '就绪时间天',
  `ready_hour` varchar(20) DEFAULT '' COMMENT '就绪时间小时',
  `schedule_level` varchar(20) DEFAULT '' COMMENT '导表调度级别，day、hour',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '日志生成时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '日志生成时间',
  `enable` tinyint(1) DEFAULT '1' COMMENT '是否生效，1:生效、0:未生效',
  `token_name` varchar(128) DEFAULT NULL COMMENT 'token名',
  `update_token_name` varchar(128) DEFAULT NULL COMMENT '更新token名',
  `ready_minute` varchar(20) DEFAULT '00' COMMENT '就绪时间分钟',
  PRIMARY KEY (`id`),
  UNIQUE KEY `target_unique_key` (`target_prefix`,`target`,`ready_date`,`ready_hour`,`ready_minute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据表tag状态';



# Dump of table t_test_entry_table
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_test_entry_table`;

CREATE TABLE `t_test_entry_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `metric_conf` varchar(100) DEFAULT NULL COMMENT '项目',
  `group_keys` varchar(200) DEFAULT NULL COMMENT '纬度组合',
  `table_name` varchar(100) DEFAULT NULL COMMENT '存储表',
  `storetype` int(11) NOT NULL DEFAULT '1' COMMENT '1老mysql表2新mysql表',
  `suffix` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `metric_conf` (`metric_conf`,`group_keys`,`suffix`),
  KEY `metric_conf_cat` (`metric_conf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='mms入口表';



# Dump of table t_visual_behavior
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_behavior`;

CREATE TABLE `t_visual_behavior` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `cdate` varchar(10) COLLATE utf8mb4_bin NOT NULL COMMENT '日期',
  `user_name` varchar(100) COLLATE utf8mb4_bin NOT NULL COMMENT '用户名',
  `behavior` varchar(4096) COLLATE utf8mb4_bin DEFAULT '' COMMENT '用户行为记录',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cdate` (`cdate`,`user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户行为表';



# Dump of table t_visual_behavior_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_behavior_log`;

CREATE TABLE `t_visual_behavior_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `cdate` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `user_name` varchar(40) COLLATE utf8mb4_bin NOT NULL COMMENT '用户名',
  `user_action` varchar(100) COLLATE utf8mb4_bin DEFAULT '',
  `param` text COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  KEY `idx_user_action` (`user_action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户行为记录表';



# Dump of table t_visual_favorites
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_favorites`;

CREATE TABLE `t_visual_favorites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_name` varchar(100) NOT NULL COMMENT '用户名',
  `table_id` varchar(1024) NOT NULL COMMENT '表id',
  `chinese_name` varchar(100) DEFAULT '' COMMENT '收藏报表者的中文名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户收藏表';



# Dump of table t_visual_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_group`;

CREATE TABLE `t_visual_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(2048) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table t_visual_mail
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_mail`;

CREATE TABLE `t_visual_mail` (
  `mail_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `report_id` varchar(100) DEFAULT NULL COMMENT '报表id',
  `author` varchar(100) DEFAULT NULL COMMENT '创建人',
  `time` varchar(100) NOT NULL DEFAULT '' COMMENT '时间',
  `title` varchar(300) DEFAULT NULL COMMENT '邮件标题',
  `status` int(11) DEFAULT '0' COMMENT '邮件状态1 为发送 0 为未发送',
  `addressee` varchar(2048) DEFAULT NULL COMMENT '收件人',
  `warning_address` varchar(1000) DEFAULT NULL COMMENT '报警收件人',
  `comments` mediumblob COMMENT '邮件注释内容',
  `type` tinyint(4) DEFAULT '1' COMMENT '注释位置1上方，2下方',
  `send_time` datetime DEFAULT '0000-00-00 00:00:00',
  `warning_time` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='t_visual_mail';



# Dump of table t_visual_mapdata
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_mapdata`;

CREATE TABLE `t_visual_mapdata` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `map_name` varchar(128) NOT NULL DEFAULT '' COMMENT '映射分类中文',
  `map_key` varchar(128) NOT NULL DEFAULT '' COMMENT '映射key英文',
  `map_data` mediumblob COMMENT 'hql任务名',
  `creater` varchar(128) DEFAULT '' COMMENT '创建人',
  `updater` varchar(128) DEFAULT '' COMMENT '更新人',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `map_key` (`map_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='映射数据';



# Dump of table t_visual_menu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_menu`;

CREATE TABLE `t_visual_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `first_menu` varchar(1024) NOT NULL COMMENT '一级菜单',
  `second_menu` varchar(1024) NOT NULL COMMENT '二级菜单',
  `table_id` mediumblob NOT NULL COMMENT '报表id列表',
  `user_name` varchar(1024) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '作者',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '类型1表示报表2表示外链',
  `flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1有效2下线',
  `sort` int(11) NOT NULL DEFAULT '2147483647',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜单配置表';



# Dump of table t_visual_project_data
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_project_data`;

CREATE TABLE `t_visual_project_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `project` varchar(1024) NOT NULL COMMENT '项目名',
  `comments` mediumblob COMMENT '注释',
  `timeline` mediumblob COMMENT '时间线',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='项目附加数据';



# Dump of table t_visual_table
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_table`;

CREATE TABLE `t_visual_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `cn_name` varchar(100) DEFAULT NULL COMMENT '报表名称',
  `explain` mediumblob COMMENT '报表说明',
  `project` varchar(100) DEFAULT '' COMMENT '报表所属项目',
  `group` varchar(8000) DEFAULT '' COMMENT '维度',
  `metric` varchar(8000) DEFAULT '' COMMENT '指标',
  `params` mediumblob NOT NULL,
  `creater` varchar(100) DEFAULT NULL COMMENT '报表创建者',
  `auth` varchar(2048) DEFAULT 'all',
  `flag` tinyint(4) DEFAULT '1',
  `type` tinyint(4) DEFAULT '1',
  `modify_user` varchar(100) DEFAULT '' COMMENT '报表修改人',
  `chinese_name` varchar(100) DEFAULT '' COMMENT '报表创建者中文名称',
  `create_date` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cn_name` (`cn_name`),
  KEY `project` (`project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='t_visual_table';



# Dump of table t_visual_timeline
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_timeline`;

CREATE TABLE `t_visual_timeline` (
  `event_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event_name` varchar(1024) NOT NULL COMMENT '事件名称',
  `event_data` mediumblob COMMENT '时间线具体数据',
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='时间线内容管理表';



# Dump of table t_visual_tool
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_tool`;

CREATE TABLE `t_visual_tool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '名称',
  `content` varchar(255) DEFAULT '' COMMENT '内容',
  `parent_id` int(11) DEFAULT '0' COMMENT '父节点id',
  `icon` varchar(100) DEFAULT '0' COMMENT '图标',
  `new_window` int(1) DEFAULT '1' COMMENT '1新窗口0非新窗口',
  `sort` int(11) DEFAULT '999' COMMENT '排序',
  `user_name` varchar(50) DEFAULT 'meilishuo' COMMENT '最后编辑人',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后编辑时间',
  `url` varchar(100) DEFAULT '/visual/index',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table t_visual_unit_map
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_unit_map`;

CREATE TABLE `t_visual_unit_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) NOT NULL COMMENT '名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='单位map表';



# Dump of table t_visual_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_visual_user`;

CREATE TABLE `t_visual_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_name` varchar(100) NOT NULL COMMENT '用户名',
  `group` varchar(1024) NOT NULL DEFAULT '' COMMENT '用户所属组',
  `password` varchar(256) DEFAULT '' COMMENT '密码',
  `realname` varchar(256) DEFAULT '' COMMENT '真实姓名',
  `iphone` varchar(256) DEFAULT '' COMMENT '电话号码',
  `change_pwd` int(11) DEFAULT '0' COMMENT '0:不修改，1:修改',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';



# Dump of table t_white_interface
# ------------------------------------------------------------

DROP TABLE IF EXISTS `t_white_interface`;

CREATE TABLE `t_white_interface` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(128) DEFAULT NULL,
  `url` varchar(256) DEFAULT NULL,
  `refers` varchar(1024) DEFAULT NULL COMMENT 'refer来源',
  `creater` varchar(128) DEFAULT NULL,
  `updater` varchar(128) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='链接白名单';




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
