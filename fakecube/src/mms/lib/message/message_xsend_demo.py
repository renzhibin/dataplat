'''
Submail message/xsend API demo
SUBMAIL SDK Version 1.0.1 --python
copyright 2011 - 2014 SUBMAIL
'''
from message_xsend import MESSAGEXsend
from app_configs import MESSAGE_CONFIGS

'''
init MESSAGEXsend class
'''
submail = MESSAGEXsend(MESSAGE_CONFIGS)

'''
Optional para
recipient cell phone number
@Multi-para
'''
# submail.add_to('18510588206')
submail.add_to('13718335828')

'''
Optional para
set addressbook sign : Optional
add addressbook contacts to Multi-Recipients
@Multi-para
'''
#submail.add_address_book('subscribe')

'''
Required para
set message project sign
'''
submail.set_project('cnHGY2')

'''
Optional para
submail email text content filter
@Multi-para
'''
submail.add_var('app_name','my_app_name')
submail.add_var('content','my content')
print submail.xsend()
