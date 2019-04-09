# -*- coding:utf8 -*-
"""
    @author: pengbangzhong
    @file: Message4Me.py
    @time: 16/5/31 下午2:10 
"""

from message.message_xsend import MESSAGEXsend
from message.app_configs import MESSAGE_CONFIGS

class Message4Me(MESSAGEXsend):

    def __init__(self):
        MESSAGEXsend.__init__(self,MESSAGE_CONFIGS)
        self.set_project("cnHGY2")



if __name__ == '__main__':
    m4m=Message4Me()
    m4m.add_to("18510588206")
    m4m.add_var('app_name', 'my_app_name')
    m4m.add_var('content', 'my content')
    m4m.xsend()


