#/usr/bin/env python
#coding=utf-8

#author:xiaofenzhang
#db api
import sys
import mms_mysql_conf as mmsMysqlConf
from mms_mysql import MmsMysql

#TODO 连接池？

class DbUtil(object):
      def __init__(self,host=None,port=None,user=None,password=None,database=None):
          # self.host = host if host else conf.MYSQL_HOST
          # self.port = port if port else conf.MYSQL_PORT
          # self.user = user if user else conf.MYSQL_USER
          # self.passwd=password if password else conf.MYSQL_PASSWD
          # self.database = database if database else conf.MYSQL_DATABASE

          # if conf.TUNNEL_SWITCH == 1:
          #     self.conn = MySQLdb.connect(host=conf.TUNNEL_HOST, port=conf.TUNNEL_PORT, user=conf.TUNNEL_USER,
          #                                      passwd=conf.TUNNEL_PASSWD, db=conf.MYSQL_DATABASE,charset='utf8')
          # else:
          #     self.conn = MySQLdb.connect(host=self.host,port=self.port,user=self.user,
          #                                       passwd=self.passwd,db=self.database,charset='utf8')
          # self.cur = self.conn.cursor()
          mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META)
          self.conn = mmsMysql.get_conn()
          self.cur = mmsMysql.get_cur()


      def __del__(self):
          self.cur.close()
          self.conn.close()

      def queryOne(self,sql,value):
          self.cur.execute(sql,value)
          return self.cur.fetchone()

      def select(self,sql,value=''):
          self.cur.execute(sql,value)
          result=[]
          for r in self.cur:
             result.append(r)
          return result

      def insertOne(self,sql,value):
          """
          @summary:向数据表插入一条纪录
          """
          self.cur.execute(sql,value)
          self.conn.commit()

if __name__=="__main__":
      dbUtil = DbUtil()
      s=dbUtil.queryOne("select * from mms_conf where appname=%s",['xf_test'])
      print s
