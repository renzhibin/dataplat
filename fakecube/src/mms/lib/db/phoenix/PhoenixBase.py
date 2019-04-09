#!/usr/bin/env python2.7
# coding=utf-8

class PhoenixBase():
    def getPhoenix(self,sql,action=''):
        import  socket,urllib,json

        socket.setdefaulttimeout(3600)
       # sql='select * from TEST'
        params={}
        params['sql']=sql
        params['action']=action

        getRes='http://10.6.3.112:8199/?%s'%(urllib.urlencode(params))
        print getRes
        doc=urllib.urlopen(getRes).read()
        doc=json.loads(doc)
        if doc['status']!=0 :
                raise Exception("phoenix服务异常")
        return doc
