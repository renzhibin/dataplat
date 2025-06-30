CREATE TABLE `t_visual_behavior_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `cdate` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `user_name` varchar(40) COLLATE utf8mb4_bin NOT NULL COMMENT '用户名',
  `user_action` varchar(100) COLLATE utf8mb4_bin DEFAULT '',
  `param` text COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  KEY `idx_user_action` (`user_action`)
) ENGINE=InnoDB AUTO_INCREMENT=950205 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户行为记录表';
