import web
from lib import base
from getRunLog import getLogMonitor

class RunMonitorInfo():
    def GET(self):
        user_data=web.input(log_id='')
        log_id = user_data.log_id
        if not log_id:
            return base.retu(-1,"param wrong")
        log_list = getLogMonitor(log_id)

        render=web.template.render('templates/')
        return render.run_monitor_info(log_list)

