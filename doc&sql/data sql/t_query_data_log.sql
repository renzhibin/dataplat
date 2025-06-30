CREATE TABLE `t_query_data_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `cdate` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '日志生成时间',
  `token_name` varchar(40) COLLATE utf8mb4_bin NOT NULL COMMENT 'token名',
  `project_name` varchar(100) COLLATE utf8mb4_bin DEFAULT '' COMMENT '项目名',
  `group` varchar(8000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `metric` varchar(8000) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6431595 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='数据查询日志记录表';
