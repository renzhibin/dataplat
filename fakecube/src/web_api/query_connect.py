import sys

sys.path.append('..')
from mms.lib.query import Query
from mms.lib.db.mysql.QueryData import  QueryData
from mms.lib.db.phoenix.QueryPhoenix import  QueryPhoenix

from mms.lib.mms_conf import MmsConf


class Singleton(type):
    def __init__(cls, name, bases, dict):  
        super(Singleton, cls).__init__(name, bases, dict)  
        cls._instance = None  
    def __call__(cls, *args, **kw):  
        if cls._instance is None:  
            cls._instance = super(Singleton, cls).__call__(*args, **kw)  
        return cls._instance  
  
class TunnelQuery(object):  
    __metaclass__ = Singleton 
    
    def __init__(self, tunnel_port=-1):
        self.port = tunnel_port
    def get_port(self):
         return self.port


    def get_reconnect(self,project,str_dim,date,edate,query_mysql_type='slave', mysql_weight='1'):
         object_mms_conf = MmsConf()
         res = object_mms_conf.select(project)
         store_type = res[0]['storetype']
         mysql_db = res[0]['store_db']
         object_mms_conf.close_connection()
         obj_query=QueryData()
         res=obj_query.getTableList(project,str_dim,date,edate,store_type)
         if not res:
             return False,'','',''

         if obj_query.storetype == 1 or obj_query.storetype ==2 or obj_query.storetype==5:
            db= Query(self.port,True,query_mysql_type, mysql_weight, mysql_db)
         if obj_query.storetype  == 3:
            db=QueryPhoenix()

         return True,db,res,obj_query.storetype




     


