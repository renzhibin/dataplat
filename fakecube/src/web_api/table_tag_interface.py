# -*- coding:utf8 -*-
"""
    @author: pengbangzhong
    @file: table_tag_interface.py
    @time: 16/4/26 上午10:29 
"""
import web,traceback,time,os
from lib import *
import mms.conf.mms_mysql_conf as mmsMysqlConf
from mms.lib.mms_mysql import MmsMysql
from mms.lib.TagManage import TagManage

class SetTableStatus(action.Action):

    def GET(self):
        '''
            target_prefix:
                如果是文件类型则为目录名;数据表则为数据库名
            target:
                如果文件类型这是文件前缀,文件命名规则为文件名_时间
            ready_date:设置就绪时间
            schedule_level:更新级别
                天:day
                小时:hour
            enable:是否生效
                0:未生效
                1:生效

        '''
        try:
            params=web.input(target_prefix=None,target=None,ready_date='',ready_hour='',ready_minute='',schedule_level='day',enable='1')
            target_prefix=params.target_prefix
            target=params.target
            ready_date=params.ready_date
            ready_hour=params.ready_hour
            ready_minute=params.ready_minute
            schedule_level=params.schedule_level
            enable=params.enable
            token_name=params.get('token_name','')
            token_value=params.get('token_value','')
            #检测token


            if not target_prefix or not target or not ready_date:
                return base.retu(1,'参数不完整')

            if schedule_level=='hour' and not ready_hour:
                return base.retu(1,'小时参数为空')

            if schedule_level=='day':
                ready_hour='00'
            if schedule_level!='minute':
                ready_minute='00'

            ready_minute=str(ready_minute).zfill(2)
            ready_hour=str(ready_hour).zfill(2)
            now_time=time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time()))

            params={}
            params['target_prefix']=target_prefix
            params['target']=target
            params['ready_date']=ready_date
            params['ready_hour']=ready_hour
            params['ready_minute']=ready_minute
            params['schedule_level']=schedule_level
            params['enable']=enable
            params['token_name']=token_name
            params['now_time']=now_time

            self.saveOrUpdateTable(params)
            return base.retu(0,'更新状态成功')
        except Exception,e:
            traceback.print_exc()
            return base.retu(1,'更新状态失败.')



    def POST(self):
        return self.GET()

    def saveOrUpdateTable(self,params):
        mmsMysql=MmsMysql(mmsMysqlConf.MMS_DB_META)
        conn=mmsMysql.get_conn()
        cur=mmsMysql.get_cur()
        sql='''
            INSERT INTO  t_table_tag_status(target_prefix,target,ready_date,ready_hour,schedule_level,create_time,update_time,enable,token_name,update_token_name,ready_minute)
            values
            ('{target_prefix}','{target}','{ready_date}','{ready_hour}','{schedule_level}','{create_time}','{update_time}','{enable}','{token_name}','{update_token_name}','{ready_minute}')
            ON DUPLICATE KEY UPDATE schedule_level='{schedule_level}',update_time='{update_time}',enable='{enable}',update_token_name='{update_token_name}'
        '''.format(target_prefix=params['target_prefix'],
                   target=params['target'],
                   ready_date=params['ready_date'],
                   ready_hour=params['ready_hour'],
                   schedule_level=params['schedule_level'],
                   create_time=params['now_time'],
                   update_time=params['now_time'],
                   enable=params['enable'],
                   token_name=params['token_name'],
                   update_token_name=params['token_name'],
                   ready_minute=params['ready_minute'])
        cur.execute(sql)
        conn.commit()
        conn.close()




class GetTableStatus(object):

    def GET(self):
        try:
            params = web.input(target_prefix=None, target=None, ready_date='', ready_hour='0',ready_minute='00',schedule_level=None)
            target_prefix = str(params.target_prefix).strip().lower()
            target = str(params.target).strip().lower()
            ready_date = params.ready_date
            ready_hour = params.ready_hour
            ready_minute=params.ready_minute
            schedule_level = params.schedule_level

            ready_hour=str(ready_hour).zfill(2)

            if not schedule_level:
                return base.retu(1,'schedule_level参数为空')

            if schedule_level=='day':
                ready_hour='00'
            if schedule_level!='minute':
                ready_minute='00'
            ready_minute=str(ready_minute).zfill(2)
            ready_hour = str(ready_hour).zfill(2)
            result=self.getTableStatus(target_prefix,target,ready_date,ready_hour,ready_minute,schedule_level)

            if len(result)>0:
                if len(result)==1:
                    tmp=result[0]
                    enable=tmp['enable']
                    if tmp['schedule_level']==schedule_level:
                        if int(enable)==1:
                            return base.retu(0,'table ready')
                        else:
                            return base.retu(2, 'table not ready')

                else:
                    #如果表为小时导表,查看该表一天数据ready 需要判断是否存在24小时tag
                    l=[]
                    for x in result:
                        enable=x['enable']
                        if int(enable)==1:
                            l.append(x['ready_hour'])
                    l=set(l)
                    if len(l)==24:
                        return base.retu(0,'table ready')


                return base.retu(1,'table not ready')

            else:
                return base.retu(1,'table not ready')

        except Exception,e:
            traceback.print_exc()

            return base.retu(1, '获取数据异常.')

    def POST(self):
        return self.GET()



    def getTableStatus(self,target_prefix,target,ready_date,ready_hour,ready_minute,schedule_level):
        mmsMysql = MmsMysql(mmsMysqlConf.MMS_DB_META)
        conn = mmsMysql.get_conn()
        cur = mmsMysql.get_cur(MmsMysql.DICTCURSOR_MODE)
        sql="select * from t_table_tag_status where target_prefix='%s' and target='%s' and ready_date='%s' "
        sql=sql%(target_prefix,target,ready_date)

        if schedule_level=='hour' or schedule_level=='minute':
            sql+=" and ready_hour='%s' "%(ready_hour)
        if schedule_level=='minute':
            sql+=" and ready_minute='%s' "%(ready_minute)
        cur.execute(sql)
        result=cur.fetchall()
        conn.close()
        return result




class GetTableStatusByTag(object):

    def GET(self):
        try:
            params = web.input(operate='check', day=None, hour=None, minute='00', tag=None, interval=300, check_num=None, is_wait=True, schedule_level='day')
            operate = str(params.operate).strip().lower()
            day = params.day
            if not day:
                day = time.strftime('%Y-%m-%d', time.localtime(time.time() - 86400))
            hour = params.hour
            minute = params.minute
            tag = params.tag
            if not tag:
                return base.retu(1, 'tag参数不允许为空')
            interval = params.interval
            check_num = params.check_num
            is_wait = params.is_wait
            schedule_level = params.schedule_level
            if hour:
                schedule_type='hour'
            if operate in ['create', 'check', 'delete']:
                tagManger = TagManage(tag, day, hour, interval, minute, check_num, schedule_level)
                is_ready, is_next = tagManger.check_tag(is_wait)
                if is_ready:
                    return base.retu(0, 'table ready')
                else:
                    return base.retu(1, 'table not ready')
            else:
                return base.retu(1, 'operate参数为create、check或者delete')

        except Exception,e:
            traceback.print_exc()

            return base.retu(1, '获取数据异常.')

    def POST(self):
        return self.GET()




if __name__ == '__main__':
    l1=['00','01']
    l2=['00','02']
    l=set()
    print set(l1).difference(set(l2))