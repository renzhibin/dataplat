#coding=utf-8
'''
 USERNAME = 'mailman@xiaozhu.com'
 PASSWD = '5be0d56f'
 SERVER = 'mail.meilishuo.com'

 '''
import time
from dateutil.relativedelta import relativedelta
import datetime



class FuncReplace:
    def __int__(self):
        pass

    def MONTH(self, params, funpar):
        list_funpar=funpar.split(',')
        if len(list_funpar)==1:
            separate='-'
        else:
            separate=list_funpar[1]
        day_f = ['%Y', '%m', '%d']
        month_f = ['%Y', '%m']
        try:
            date_struct = datetime.datetime.strptime(params['dt'], separate.join(day_f))
            date_format = datetime.date(date_struct.year, date_struct.month, date_struct.day)
            month_res = date_format + relativedelta(months=int(funpar))
        except Exception,ex:
            print ex
        return datetime.datetime.strftime(month_res, separate.join(month_f))

    def DATE(self,params,funpar):
        list_funpar=funpar.split(',')
        if len(list_funpar)==1:
            separate='-'
        else:
            separate=list_funpar[1]
        format_data=separate.join(['%Y','%m','%d'])

        dt_stamp=time.mktime(time.strptime(params['dt'],"%Y-%m-%d"))
        return time.strftime(format_data,time.localtime(dt_stamp+(float)(list_funpar[0])*86400))

    def HOUR(self,params,funpar):
        list_funpar=funpar.split(',')
        if len(list_funpar)==1:
            separate='-'
        else:
            separate=list_funpar[1]
        format_data='%H'
        join_date='%s %s:%s'%(str(params['dt']),str(params['hour']),str(params['minute']))
        dt_stamp=time.mktime(time.strptime(join_date,"%Y-%m-%d %H:%M"))
        return time.strftime(format_data,time.localtime(dt_stamp+(float)(list_funpar[0])*3600))

    def START(self,params,funpar):
        params['dt']=params['start']
        return self.DATE(params,funpar)

    def END(self,params,funpar):
        params['dt']=params['end']
        return self.DATE(params,funpar)

if __name__=="__main__":
    params=dict
    params={'dt':'2014-11-20','hour':'0','minute':'0'}
    import re
    hql='''select * from dt='$HOUR(0)' and dt='$MONTH(0)' '''
    #r=re.compile(r'((\S+)\s*=\s*\$DATE\((-?\d+),(\d+)\))',re.DOTALL)
    udf=['DATE|TEST|HOUR|MONTH']
    reg_udf='|'.join(udf)


    r=re.compile(r'(\$(%s)\(([-a-zA-Z0-9]+)\))'%reg_udf,re.DOTALL)

    result=r.findall(hql)

    for content in result:
        print content
        a=FuncReplace()
        b=getattr(a,content[1])
        replace_str=b(params,content[2])
        print replace_str
        print content[0]
        hql=hql.replace(content[0],replace_str)
        print hql

