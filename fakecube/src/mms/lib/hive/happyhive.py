#!/usr/bin/env python2.7
#coding=utf-8

import subprocess
import sys

import hivehelper

sys.path.append('../../conf')
import env

BLACKHOLE = open('/dev/null')
HADOOP_BIN = '/hadoop/hadoop/bin/hadoop'


class happyhive(object):
    def __init__(self):
        try:
            self.hp = hivehelper.hivehelper()
        except:
            self.hp=None


    def checkpath(self,path, user_check=False):

        status=-1
        try:
            hql='dfs -ls '+path
            if self.hp:
                status,msg,result=self.openthrift(hql)
                if user_check:
                    for item in result:
                        eles = item[0].split()
                        if len(eles) < 6:
                            continue
                        user = eles[2]
                        if user in env.CHECK_TAG_USER_BLACK_LIST:
                            #跳过tag检查
                            status = env.TAG_STATUS.SKIP
            else:
                status=self.openshell(hql)
        except:
            import traceback
            #print traceback.print_exc()
            status=self.openshell(hql)
        return status
    def openshell(self,hql):
        cmd=HADOOP_BIN+' '+hql

        ret_code=subprocess.call(cmd,shell=True)
        return ret_code
    def openthrift(self,hql):
        status,msg,result=self.hp.hive_execute_all(hql)

        return status,msg,result
    def checkpathlist(self,path):
        path_list=[]
        try:
            hql='dfs -ls '+path
            cmd=HADOOP_BIN+' '+hql
            if self.hp:
                status,msg,result=self.openthrift(hql)
                path_list=[item[0] for item in result]
            else:
               path_list=subprocess.check_output(cmd,shell=True).split('\n')
        except:
            path_list=subprocess.check_output(cmd,shell=True).split('\n')

        return path_list



if __name__ == '__main__':

    path=' /user/data_ready_tag/tmp_wap_pit_shopcart/'
    h=happyhive()

    print 'realresult',h.checkpath(path)

