#coding=utf-8
'''
 USERNAME = 'bi-warn@'
 PASSWD = 'h5T17F8rNqgF'
 SERVER = 'smtp.exmail.qq.com'

 '''
import smtplib,email,sys,os
from email.Message import Message
from email.mime.multipart import MIMEMultipart
from email.Header import Header




class MmsEmail:
    def __init__(self,cc=''):

        self.smtpserver='smtp.exmail.qq.com'
        self.smtpuser='bi-warn@'
        self.smtppass='h5T17F8rNqgF'
        self.smtpport='465'
        self.emailfrom='bi-warn@'
        self.server=smtplib.SMTP_SSL(self.smtpserver,self.smtpport)
        self.server.ehlo()
        self.server.login(self.smtpuser,self.smtppass)
        self.to=['yangzongqiang', 'yangyulong', 'houyangyang']
        self.cc=cc
        if self.cc :
            self.to.append(self.cc)
        self.subj='fackcube job failed'

    def hostname(self):
        os_name = os.name
        hostname = ''

        if os_name == 'nt':
            hostname = os.getenv('computername')
        elif os_name == 'posix':
            try:
                hostname = os.popen('echo $HOSTNAME').read().strip()
            except:
                hostname = 'Unkwon'
        else:
            hostname = 'Unkwon'

        if hostname.find('.') > 0:
            return "mailman@%s" % hostname
        else:
            return "mailman@%s." % hostname

    def addsuff(self,a):
        emailAddress = str(a)
        atPos = emailAddress.find('@')
        if (atPos != -1):
            name = emailAddress[0:emailAddress.find('@')]
        else:
            name = emailAddress
        return name+'@'

    def sendmessage(self,to='',subj='',content='',attach=None):
       # self.emailfrom=self.hostname()
        msg = Message()
        COMMASPACE = ', '
        if not to:
            to=self.to
        to=map(self.addsuff,to)
        print to
        if not subj:
            subj=self.subj
        if not content:
            content=self.subj
        msg = MIMEMultipart()

        msg['From']    = self.emailfrom
        if self.cc:
            msg['CC'] = self.cc

        msg['To']      =COMMASPACE.join(to)

        msg['Subject'] = Header(subj,'utf-8')
        msg['Date']    = email.Utils.formatdate()
        # msg.set_payload(content)
        if not attach:
            msg.set_payload(content)
        else:
            msg.attach(content)
            msg.attach(attach)
        try:

            failed = self.server.sendmail(self.emailfrom,to,msg.as_string())   # may also raise exc
        except Exception ,ex:
            import traceback
            print traceback.print_exc()
            print Exception,ex
            return 'Error - send failed'
        else:
            return "send success!"



if __name__=="__main__":

    to=['yangyulong', 'houyangyang', 'yangzongqiang']
    cc=''
    subj='fackcube job failed - test'
    print 'Type message text, end with line="."'
    #text = 'content'
    a=MmsEmail()

    a.sendmessage(to=to,subj=subj,content='test')


