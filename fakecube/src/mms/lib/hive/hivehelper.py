#!/usr/local/bin/python
# encoding:utf8
'''
Helper functions to play with hive.
'''
import os, sys, time, string, traceback, socket, getpass, random

from TCLIService import TCLIService
from TCLIService.ttypes import TOpenSessionReq, TGetTablesReq, TFetchResultsReq, \
  TStatusCode, TGetResultSetMetadataReq, TGetColumnsReq, TType, \
  TExecuteStatementReq, TGetOperationStatusReq, TFetchOrientation, \
  TCloseSessionReq, TGetSchemasReq, TCancelOperationReq, TCloseOperationReq, \
  TOperationType, TExecuteStatementResp, TStatus, TGetCatalogsReq, TFetchResultsResp, TRowSet

from thrift import Thrift
from thrift.transport import TSocket
from thrift.transport import TTransport
from thrift.protocol import TBinaryProtocol
from threading import Thread

# HIVE_SERVER = '10.6.0.85' #hd00
# HIVE_SERVER_PORT = 20000

'''
    修改逻辑：
        1. 增加一个hiveHA入口
        2. 只有线上机器ONLINE_MACHINES的特定用户‘work'和‘hadoop’可以提交到flower队列的hiveHA入口，其他用户或机器的提交将被提交至另外一个hiveHA入口
        3. 新增的hive HA入口配置的hiveServer是线下的任务队列
'''
'''
    各个分组的客户端机器列表
'''
# flower
ONLINE_MACHINES = ['jxq-bi-ol01.meilishuo.com',
                   'jxq-bi-ol02.meilishuo.com',
                   'jxq-bi-ol03.meilishuo.com']
## test03
Desire = ['jxq-bi-test03.meilishuo.com']
## test06
Tuan = ['jxq-bi-test06.meilishuo.com']
## test04
AdWork = ['btte-brd-scribe01.meilishuo.com',
          'recbox04.meilishuo.com']
## test01
Focus = ['syq-focus-01.meilishuo.com']
## test04
Risk = ['jxq-bi-risk01.meilishuo.com',
        'jxq-bi-risk02.meilishuo.com']

## test02
UG = ['jxq-bi-ug03.meilishuo.com',
      'jxq-bi-ug04.meilishuo.com',
      'jxq-bi-test02.meilishuo.com']

## test05
ML = ['jxq-bi-test04.meilishuo.com',
      'jxq-di-ml-01.meilishuo.com',
      'jxq-di-ml-02.meilishuo.com',
      'jxq-di-ml-03.meilishuo.com',
      'jxq-di-ml-04.meilishuo.com',
      'jxq-di-ml-05.meilishuo.com',
      'jxq-di-ml-06.meilishuo.com',
      'jxq-di-mltest-01.meilishuo.com',
      'jxq-di-mltest-02.meilishuo.com']

## default
ML2 = ['jxq-di-mlbase-01.meilishuo.com','jxq-di-mltest-03.meilishuo.com']
## dev01
HUE = ['jxq-bi-dev01.meilishuo.com']
## test03
TongKuan = ['syq-tongkuan-01.meilishuo.com',
            'syq-tongkuan-02.meilishuo.com']
## test01
DM = ['jxq-bi-test01.meilishuo.com']

## dev02
UserDev = ['jxq-bi-test05.meilishuo.com',
           'jxq-bi-dev01.meilishuo.com',
           'jxq-bi-dev02.meilishuo.com']
## inf
Inf = ['jxq-bi-inf01.meilishuo.com',
       'jxq-bi-inf02.meilishuo.com',
       'jxq-bi-inf03.meilishuo.com']
## test06
DT = ['jxq-bi-test10.meilishuo.com','jxq-bi-db02.meilishuo.com']

#Dev = ['syq-vm-02.meilishuo.com']
## flower
# Flower = ['jxq-bi-ol01.meilishuo.com',
#           'jxq-bi-ol02.meilishuo.com',
#           'jxq-bi-ol03.meilishuo.com']

'''
    目前集群可用队列列表,flower除外
'''

Queues = ['default', 'test01', 'test02', 'test03', 'test04', 'test05', 'test06', 'dev01', 'dev02']

'''
    线上hiveHA入口
'''

ENV_INFO = {
            'work':('10.6.0.85', 20000),
            #'hadoop':('10.6.0.85', 20000),
        }

'''
                线下hiveHA入口
'''
OFFLINE_ENV_INFO = {
                    'any':('10.6.0.85', 20000),
            }

class hivehelper(object):
    user = ''
    session_handle = None
    operation_handle = None
    creq = None


    def __init__(self,server_name='',server_port='', offline=False):

        self.client_host = socket.gethostname()
        self.client_user = getpass.getuser()
        self.current_path = os.getcwd()
        if offline and not server_name == ""  and not server_port == "":
            self.server_name = server_name
            self.server_port = int(server_port)
        else:
            self.server_name, self.server_port = self.get_env()

        print "server_name:server_port = %s:%s" % (self.server_name, self.server_port)

        if not self.connect_thrift_server():
            raise Exception('sys_error', 'sys_error')
            '''
            根据任务提交的机器判断hql提交到的队列
            '''
        if self.client_host not in ONLINE_MACHINES or self.client_host in ONLINE_MACHINES and not self.client_user == 'work':
            queue_name = 'default'
            if self.client_host in Desire or self.client_host in TongKuan:
                queue_name = "test03"
            elif self.client_host in Tuan or self.client_host in DT:
                queue_name = "test06"
            elif self.client_host in AdWork or self.client_host in Risk:
                queue_name = "test04"
            elif self.client_host in DM or self.client_host in Focus:
                queue_name = "test01"
            elif self.client_host in ML:
                queue_name = "test05"
            elif self.client_host in ML2:
                queue_name = "default"
            elif self.client_host in UG:
                queue_name = "test02"
            elif self.client_host in HUE:
                queue_name = "dev01"
            elif self.client_host in UserDev:
                queue_name = "dev02"
            self.execute_statement("set mapred.job.queue.name = %s" % queue_name, kargs={})



    def __del__(self):
        try:
            if self.creq is not None:
                res = self.call(self.client.CloseSession, self.creq)
                if res.status.statusCode == 0:
                    pass
                    #print "[%s][INFO] Close_Session: success" % (get_nowtime())
                else:
                    print "[%s][WARN] Close_Session: failure because: %s" % (get_nowtime(), res.status.errorMessage)

            self.transport.close()
        except Exception, tx:
            traceback.print_exc()
            print '%s' % (tx.message)

    def connect_thrift_server(self):
        for i in range(0, 1):
            try:
                self.transport = TSocket.TSocket(self.server_name, self.server_port)
                self.transport = TTransport.TBufferedTransport(self.transport)
                self.protocol = TBinaryProtocol.TBinaryProtocol(self.transport)

                self.client = TCLIService.Client(self.protocol)
                self.transport.open()
                return True

            except Thrift.TException, tx:
                print "ERROR in connecting to ", self.server_name, ":", self.server_port
                print '%s' % (tx.message)
                print "client sleep for retry %s .." % (i)
                #time.sleep(20)
                if i == 2:
                    return False

    def _hive_execute_any(self, sql, num, kargs={}):
        #print "[%s][INFO][SEND_QUERY: %s" % (get_nowtime(), sql)

        sql = "set mapreduce.job.submithost=%s;set mapreduce.job.submitpath=%s;%s" % \
        (self.client_host, self.current_path, sql)

        time_begin = time.time()
        #self._hive_sql_add_db()
        tesresp = self.execute_statement(sql, kargs)
        if tesresp.status.statusCode != 0:
            #print "[%s][ERROR] self.client.search: <hql>%s</hql> " % (get_nowtime(), sql)
            #print "[%s][ERROR] %s " % (get_nowtime(), tesresp.status.errorMessage)
            #raise Exception('hive_error', tesresp.status.errorMessage)
            return  tesresp.status.statusCode,tesresp.status.errorMessage,[]
        if num == 1:
            res = self.fetch_result(tesresp.operationHandle, maxRows=num)
            results = self.get_result(res)
            time_end = time.time()
            time_cost = time_end - time_begin
            if len(results) > 0:
                results = [results[0].replace('\001', '\t').split('\t')]
                print "[%s][INFO][%.3f]self.client.fetchOne ok: <hql>%s</hql>" % (get_nowtime(), time_cost, sql)
            else:
                print "[%s][WARN][%.3f]self.client.fetchOne is null: <hql>%s</hql>" % (get_nowtime(), time_cost, sql)
            return results
        elif num == -1:
            tmp_num = 1000
            results = []
            while True:
                res = self.fetch_result(tesresp.operationHandle, maxRows=tmp_num)
                result = self.get_result(res)
                for r in result:
                    results.append(r.replace('\001', '\t').split('\t'))
                if len(result) != tmp_num:
                    self.close_operation(tesresp.operationHandle)
                    break
            time_end = time.time()
            time_cost = time_end - time_begin
            if len(results) > 0:
                print "[%s][INFO][%.3f]self.client.fetchAll ok: <hql>%s</hql>" % (get_nowtime(), time_cost, sql)
                print "[%s][INFO][%.3f] result lengths: %s" % (get_nowtime(), time_cost, len(results))
            else:
                print "[%s][WARN][%.3f]self.client.fetchAll is null: <hql>%s</hql>" % (get_nowtime(), time_cost, sql)
            return 0,'success',results
        else:
            return []

    def hive_execute_all(self, sql, kargs={}):
        return self._hive_execute_any(sql, -1, kargs)

    def hive_execute(self, sql, kargs={}):
        return self._hive_execute_any(sql, 0, kargs)

    def hive_execute_one(self, sql, kargs={}):
        return self._hive_execute_any(sql, 1, kargs)

    def hive_execute_many(self, sql, num, kargs={}):
        sql = "set mapreduce.job.submithost=%s;set mapreduce.job.submitpath=%s;%s" % \
        (self.client_host, self.current_path, sql)

        self._hive_sql_add_db()
        tesresp = self.execute_statement(sql, kargs)
        if tesresp.status.statusCode == 3:
            print "[%s][ERROR] self.client.search: <hql>%s</hql> " % (get_nowtime(), sql)
            print "[%s][ERROR] %s " % (get_nowtime(), tesresp.status.errorMessage)
        i = 0
        while True:
            if num <= 1:
                self.close_operation(tesresp.operationHandle)
                break
            if tesresp.operationHandle is None:
                self.close_operation(tesresp.operationHandle)
                break
            res = self.fetch_result(tesresp.operationHandle, maxRows=num)
    #    print res zjp
            results = self.get_result(res)
            results = [r.replace('\001', '\t').split('\t') for r in results]
            yield results
            if len(results) != num:
                self.close_operation(tesresp.operationHandle)
            break

    def _hive_sql_add_db(self):
        import ConfigParser
        config = ConfigParser.ConfigParser()
        database = ""
        try:
            config.read('%s/config/hive.conf' % os.getenv('SCRIPT_HOME'))
            database = config.get('global', 'DATABASE')
        except Exception:
            pass
        database = database.strip()
        if database:
            self.execute_statement("use %s" % database)
        else:
            self.execute_statement("use default")

    def get_env(self):
        '''get user env infomation'''
        is_online_machine = True
        user = 'any'
        url_port = ()
        if not self.client_host in ONLINE_MACHINES:
            is_online_machine = False
        if is_online_machine:
            user = os.getenv('USER')
            if user == 'work':
                url_port = ENV_INFO.get(user, ())
            else:
                url_port = OFFLINE_ENV_INFO.get(user, ())
        else:
            url_port = OFFLINE_ENV_INFO.get(user, ())
#         if not url_port:
#             return (HIVE_SERVER, HIVE_SERVER_PORT)
#         else:
        return url_port

#     def upload_file(self, local_fullpath, remote_path):
#         ''' upload file to hadoop server '''
#         os.system("scp %s hadoop@%s:%s" % (local_fullpath, HIVE_SERVER, remote_path))

#     def remove_file(self, remote_fullpath):
#         '''remove a file on hadoop server'''
#         os.system("ssh hadoop@%s rm -f %s" % (HIVE_SERVER, remote_fullpath))

    def open_session(self, username):
        kargs = {}
        hostname = self.client_host
        username = self.client_user
#         if hostname == 'data-dev01.meilishuo.com' and username == 'work':
#             kargs["mapred.job.queue.name"] = "flower"
#         elif hostname == 'dm02.meilishuo.com' and username == 'dev' :
#             kargs["mapred.job.queue.name"] = "flower"
#         else:
#             pass

        req = TOpenSessionReq(username=username, configuration=kargs)
        get_session_num = 0
        get_client_num = 0
        try_num = 1
        while get_session_num <= try_num:
            try:
                res = self.client.OpenSession(req)
                if res.status.statusCode == 0:
                    #print "[%s][INFO] Open_Session: success" % (get_nowtime())
                    session_handle = res.sessionHandle
                    self.creq = TCloseSessionReq(session_handle)
                    return session_handle
                else:
                    print "because failure. sleep for open session retry %s  .." % (get_session_num)
                    time.sleep(10)
                    if get_session_num == try_num and get_client_num == 0:
                        if not self.connect_thrift_server():
                            raise Exception('sys_error', 'sys_error')
                        else:
                            get_client_num += 1
                            get_session_num = 0
                    elif get_session_num < try_num:
                        get_session_num += 1
                    else:
                        print "[%s][ERROR] %s " % (get_nowtime(), res.status.errorMessage)
                        raise Exception('sys_error', 'sys_error')

            except Exception, tx:
                print 'open session error because %s' % (tx.message)
                #time.sleep(10)
                print "because exception. sleep for open session retry %s  .." % (get_session_num)
                if get_session_num == try_num and get_client_num == 0:
                    if not self.connect_thrift_server():
                        raise Exception('sys_error', 'sys_error')
                    else:
                        print "Once again get client success  .."
                        get_client_num += 1
                        get_session_num = 0
                elif get_session_num < try_num:
                       get_session_num += 1
                else:
                    raise Exception('sys_error', 'open seesion failed')

    def close_operation(self, operation_handle):
        cpreq = TCloseOperationReq(operation_handle)
        res = self.call(self.client.CloseOperation, cpreq)
        return res

    def close_session(self, session_handle):
        creq = TCloseSessionReq(session_handle)
        res = self.call(self.client.CloseSession, creq)
        return res

    def get_operation_stat(self, operation_handle):
        tosq = TGetOperationStatusReq(operation_handle)
        res = self.call(self.client.GetOperationStatus, tosq)
        return res

    def execute_statement(self, statement, kargs={}):
        if statement[-1] == ";":
            statement = statement[:-1]

        querys = statement.split(';')

        sql = querys[-1]

        for query in querys:
            if len(query) == 0:
                continue
            elif query == sql:
                break
            else:
                req = TExecuteStatementReq(statement=query, confOverlay={})
                self.call(self.client.ExecuteStatement, req)

        req = TExecuteStatementReq(statement=sql, confOverlay=kargs)

        res = self.call(self.client.ExecuteStatement, req)
        self.operation_handle = res.operationHandle


        return res

    def fetch_result(self, operation_handle, orientation=TFetchOrientation.FETCH_NEXT, maxRows=1000):
        if operation_handle.hasResultSet:
            fetch_req = TFetchResultsReq(operationHandle=operation_handle, orientation=orientation, maxRows=maxRows)
            res = self.call(self.client.FetchResults, fetch_req)
        else:
            res = TFetchResultsResp(results=TRowSet(startRowOffset=0, rows=[], columns=[]))
        return res

    def call(self, fn, req):
        if self.session_handle is None:
            self.session_handle = self.open_session(self.user)
        if hasattr(req, 'sessionHandle') and req.sessionHandle is None and req.sessionHandle is not self.session_handle:
            req.sessionHandle = self.session_handle
        res = fn(req)
        return res

    def get_result(self, res):
        results = []
        if len(res.results.columns) > 0:
            for column in res.results.columns:
                result = HiveServerTColumnValue(column).val
                if len(result) > 0:
                    results.append('\t'.join(str(col).replace('\t', '') for col in result))
        tmp = []
        i = 0
        j = 0
        for result in results:
            columns = result.split("\t")
            for column in columns:
                if j == 0 :
                    tmp.append(column)
                else:
                    col = tmp[i]
                    tmp[i] = col + '\t' + column
                    i += 1
            i = 0
            j = 1
        return tmp

class HiveServerTColumnValue:
    def __init__(self, tcolumn_value):
        self.column_value = tcolumn_value

    @property
    def val(self):
        # TODO get index from schema
        if self.column_value.boolVal is not None :
            return self.column_value.boolVal.values
        elif self.column_value.byteVal is not None :
            return self.column_value.byteVal.values
        elif self.column_value.i16Val is not None :
            return self.column_value.i16Val.values
        elif self.column_value.i32Val is not None :
            return self.column_value.i32Val.values
        elif self.column_value.i64Val is not None :
            return self.column_value.i64Val.values
        elif self.column_value.doubleVal is not None:
            return self.column_value.doubleVal.values
        elif self.column_value.stringVal is not None:
            return self.column_value.stringVal.values

def hive_execute_all(sql):
    '''Execute SQL and return all result in a list'''
    hp = hivehelper()
    return hp.hive_execute_all(sql)

def hive_execute(sql):
    '''Execute SQL without return values'''
    hp = hivehelper()
    return hp.hive_execute(sql)

def hive_execute_one(sql):
    '''Execute SQL and return all result in a list'''
    hp = hivehelper()
    return hp.hive_execute_one(sql)

def hive_execute_many(sql, num):
    '''Execute SQL and return all result in a list'''
    hp = hivehelper()
    return hp.hive_execute_many(sql, num)

# def upload_file(local_fullpath, remote_path):
#     ''' upload file to hadoop server '''
#     os.system("scp %s hadoop@%s:%s" % (local_fullpath, HIVE_SERVER, remote_path))

# def remove_file(remote_fullpath):
#     '''remove a file on hadoop server'''
#     os.system("ssh hadoop@%s rm -f %s" % (HIVE_SERVER, remote_fullpath))

def get_nowtime():
    return time.strftime("%F %T", time.localtime(time.time()))

if __name__ == '__main__':
    hp = hivehelper()

    #hql=''' dfs -ls /user/data_ready_tag/tmp_wap_pit_shopcart'''
    hql=''' show databases'''
    # print hql
    try:
        result = hp.hive_execute_all(hql)

    except:
        print  'in'
        import traceback
        print traceback.print_exc()
    from pprint import pprint
    print '----------result--------'
    pprint(result)
