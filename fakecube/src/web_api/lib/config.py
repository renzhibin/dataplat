#!/usr/bin/env python
# -*- encoding: utf-8 -*-
#

import logging

file = "../../log/system_log/web_api/webapi.log" # 日志文件路径 #
logformat = "[%(asctime)s] %(filename)s:%(lineno)d(%(funcName)s): [%(levelname)s] %(message)s" # 日志格式 #
datefmt = "%Y-%m-%d %H:%M:%S" # 日志中显示的时间格式 #
loglevel = logging.DEBUG
interval = "H" # 每隔一天生成一个日志文件#
backups	= 8760
