#!/usr/bin/env python2.7
#coding=utf-8

import web,sys,json,yaml

from decimal import Decimal

class fakefloat(float):
    def __init__(self, value):
        self._value = value
    def __repr__(self):
        return str(self._value)

def defaultencode(obj):
    if isinstance(obj, Decimal):
        # Subclass float with custom repr?
        return fakefloat(obj)

    import calendar, datetime,time

    if isinstance(obj, datetime.datetime):
        if obj.utcoffset() is not None:
            obj = obj - obj.utcoffset()
        millis = int(
            calendar.timegm(obj.timetuple())

        )
        return time.strftime("%Y-%m-%d",time.localtime(millis))
    elif isinstance(obj, datetime.date):
        millis = int(
            calendar.timegm(obj.timetuple())

        )
        return time.strftime("%Y-%m-%d",time.localtime(millis))

    raise TypeError(repr(obj) + " is not JSON serializable")



def retu(status=0,msg='',data='',others={}):
    if status==0 or status=='':
        status=0
        msg='success'
    result={'status':status,'msg':msg,'data':data}
    result.update(others)
    return json.dumps(result,default=defaultencode)


def replace(hql,params={'dt':'2015-05-01','hour':'0','minute':'0'}):

    sys.path.append('..')
    import mms.bin.run_task_single as run

    return run.replace(hql,params)



