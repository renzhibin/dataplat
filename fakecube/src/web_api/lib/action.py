__author__ = 'renzhibin'
import  web,urllib
class Action():
     def __init__(self,):
        self.logger = web.ctx.environ['wsgilog.logger']

        ctx=web.ctx
        method=ctx.method

        self.url_log=ctx.home + ctx.path + urllib.unquote(ctx.query) or ctx.home + urllib.unquote(ctx.fullpath)
        strQuery=str(web.input())
        baseLog=[self.url_log,method,strQuery]
        self.logger = web.ctx.environ['wsgilog.logger']
        self.logger.info( str("\t".join(baseLog)))
     def GET(self):
        pass

