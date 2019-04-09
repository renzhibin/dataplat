import  time
import  urllib
from mms_email import MmsEmail
email_obj=MmsEmail()
def email_alert(url):
    msg=url+' is down'
    sub='webapi is down'
    email_obj.sendmessage('zhibinren@meilishuo.com',sub,msg)

url_email={}
ignore_num=60
time_interval=60
def check(url):

    try:
        a=urllib.urlopen(url)
        status=a.getcode()
    except:
        status='404'
    print url,status
    url_email.setdefault(url,0)
    if status!=200:
        if url_email[url]>0:
            url_email[url]-=1
        else:
            email_alert(url)
            url_email[url]=ignore_num




urls=['http://www.baidu.com','http://172.16.2.232:8181/list_app','http://172.16.2.232:8181/query_app/?project=msq_metric&date=2014-10-26&group=fisrt_cata,second_cata&metric=default.twitter_access.pv,default.twitter_access.uv']

while(1==1):
    if time.time()%time_interval==0:
        for url in urls:
            check(url)