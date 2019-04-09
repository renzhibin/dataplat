#!/usr/bin/env python
# -*- coding: utf-8 -*-
# 
# Hive api returns two array: schema () , rows (two dimensions array) 

import sys
import os
from thrift.transport import TSocket
from thrift.transport import TTransport
from thrift.protocol import TBinaryProtocol

import env as conf
from TCLIService import TCLIService
from TCLIService.ttypes import TOpenSessionReq, TFetchResultsReq,\
  TStatusCode, TGetResultSetMetadataReq, TExecuteStatementReq, TFetchOrientation


class HiveServerClient(object):
    # A simple hive client
    user = 'anyone'
    session_handle = None

    def __init__(self):
        self.connect()
    
    def __del__(self):
        self.close()

    def close(self):
        try:
            self._transport.close()
            #print >> sys.stderr,"Hive session closed"
        except:
            print >> sys.stderr,"Warning : Failed to close Hive session"
            pass

    def connect(self):
        server_name, server_port = self.get_env()
        transport = TSocket.TSocket(server_name, server_port)
        self._transport = TTransport.TBufferedTransport(transport)
        protocol = TBinaryProtocol.TBinaryProtocol(self._transport)
        self._client = TCLIService.Client(protocol)
        self._transport.open()
 
    def get_env(self):
        '''get user env infomation'''
        user = os.getenv('USER')
        url_port = conf.HIVE_ENV.get(user, ())
        if not url_port:
            return (conf.HIVE_DEFAULT_SERVER, conf.HIVE_DEFAULT_SERVER_PORT)
        else:
            return url_port


    def open_session(self, username):
        req = TOpenSessionReq(username=username, configuration={})
        res = self._client.OpenSession(req)
        session_handle = res.sessionHandle
        return session_handle
    

    def __get_value(self,colValue):
        #
        if colValue.boolVal is not None:
            return colValue.boolVal.value
        elif colValue.byteVal is not None:
            return colValue.byteVal.value
        elif colValue.i16Val is not None:
            return colValue.i16Val.value
        elif colValue.i32Val is not None:
            return colValue.i32Val.value
        elif colValue.i64Val is not None:
            return colValue.i64Val.value
        elif colValue.doubleVal is not None:
            return colValue.doubleVal.value
        elif colValue.stringVal is not None:
            return colValue.stringVal.value

 
    def call(self, fn, req, status=TStatusCode.SUCCESS_STATUS):
        if self.session_handle is None:
            self.session_handle = self.open_session(self.user)
 
        if hasattr(req, 'sessionHandle') and req.sessionHandle is None:
            req.sessionHandle = self.session_handle
 
        res = fn(req)
        
        return res
 
    #def execute_statement(self, statement, max_rows=conf.MAX_ROW):
    def execute(self, statement, max_rows=conf.MAX_ROW):
        req = TExecuteStatementReq(statement=statement, confOverlay={})
        res = self.call(self._client.ExecuteStatement, req)
        
        # if hql err,debug
        #print res
 
        return self.fetch_result(res.operationHandle, max_rows=max_rows)
 
 
    def fetch_result(self, operation_handle, orientation=TFetchOrientation.FETCH_NEXT, max_rows=conf.MAX_ROW):
        fetch_req = TFetchResultsReq(operationHandle=operation_handle, orientation=orientation, maxRows=max_rows)
        res = self.call(self._client.FetchResults, fetch_req)

        rows = []
        for row in res.results.rows:
            rowData= []
            for i, col in enumerate(row.colVals):
                rowData.append(self.__get_value(col))
            rows.append(rowData)
            if len(res.results.rows) == 0:
                #break
                pass
        
        if operation_handle.hasResultSet:
            meta_req = TGetResultSetMetadataReq(operationHandle=operation_handle)
            schema = self.call(self._client.GetResultSetMetadata, meta_req)
            
            # todo: positon ?
            columns = []
            for i in schema.schema.columns:
                columns.append(i.columnName)
        else:
            schema = None
            columns = []

        return rows, columns




