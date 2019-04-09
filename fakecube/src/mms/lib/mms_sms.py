#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-
__author__ = 'bangzhongpeng'

import requests

'''
http://smsapi.meilishuo.com/smssys/interface/smsapi.php?smsKey=1407399202710&type=both&name=%s&phone=&smscontent=%s&mailsubject=msg_from_%s&mailcontent=%s
'''

class MmsSMS(object):
    def __init__(self,receive_user='bangzhongpeng'):

        self.receive_user=receive_user
        self.type='sms'#both 发送短信，邮件
        self.smscontent='hello word'
        self.mailsubject=''
        self.mailcontent=''
        self.phone=''
        self.smsKey='1407399202710'




    def sendSMS(self, contact_list, message=''):
        url = 'http://10.161.138.17/alarm/sms'
        to_str = ','.join(contact_list)
        header = {"Host": "bi.service.qufenqi.com"}
        data = {}
        data['mobile'] = to_str
        data['content'] = message
        resp = requests.get(url, params=data, headers=header)
        if resp.status_code == 200:
            print 'send message success.'
        else:
            print 'send message failed.msg:{}'.format(resp.text)

    def get_params(self):
        params={}
        params['type']=self.type
        params['name']=self.receive_user
        params['phone']=self.phone
        params['smscontent']=self.smscontent
        params['mailsubject']=self.mailsubject
        params['mailcontent']=self.mailcontent
        params['smsKey']=self.smsKey
        return params


if __name__=='__main__':
    mmsSMS=MmsSMS()
    mmsSMS.sendSMS('bangzhongpeng','mytest')
