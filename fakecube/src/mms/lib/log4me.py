#!/usr/bin/env python2.7
#coding=utf-8

import logging
import time
import sys
import env as conf


class MyLogger(object):

    logger = None

    @staticmethod
    def getLogger():

        if MyLogger.logger is not None:
            return MyLogger.logger

        now_data=time.strftime('%Y-%m-%d',time.localtime(time.time()))
        MyLogger.logger = logging.getLogger('mms')
        stream_handler = logging.StreamHandler()
        stream_handler.setLevel(logging.DEBUG)
        stream_handler.setFormatter(conf.LOG_FORMAT)


        debug_log_file = '%s/system_log/mms/run.log' % (conf.LOG_PATH)
        debug_file_handler = logging.FileHandler(debug_log_file)
        debug_file_handler.setFormatter(conf.LOG_FORMAT)
        debug_file_handler.setLevel(logging.DEBUG)

        # err_log_file = '%s/err.log.%s' % (conf.LOG_PATH,now_data)
        # err_file_handler = logging.FileHandler(err_log_file)
        # err_file_handler.setFormatter(conf.LOG_FORMAT)
        # err_file_handler.setLevel(logging.WARN)

        MyLogger.logger.addHandler(stream_handler)
        MyLogger.logger.setLevel(logging.DEBUG)
        MyLogger.logger.addHandler(debug_file_handler)
        # MyLogger.logger.addHandler(err_file_handler)

        return MyLogger.logger


if __name__ == "__main__":
    logger = MyLogger.getLogger()

    for handler in logger.handlers:
        if isinstance(handler,logging.FileHandler):
            filepath='%s/xf_test/run.log' %conf.LOG_PATH
            handler.__init__(filepath)
            handler.setFormatter(conf.LOG_FORMAT)

    while True:
        logger.debug("this is debug info")
        logger.info("this is info msg")
        logger.warn("this is warn msg")
        logger.error("this is error msg")
        time.sleep(60)
