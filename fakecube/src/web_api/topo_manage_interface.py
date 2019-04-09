# -*- coding:utf8 -*-
"""
    @author: pengbangzhong
    @file: topo_manage_interface.py
    @time: 2017/1/16 上午10:56 
"""

import sys
from mms.lib.TagManage import TagManage
from mms.lib.utils import insertTask, getRunTaskList
reload(sys)
sys.setdefaultencoding('utf8')
import web
from lib import base, action
import json, traceback

sys.path.append('..')
from mms.lib.topo_manage import TopoManage
from mms.lib.topo_manage import get_no_succ_task, get_all_task


STATUS = {
  1: "阻塞",
  2: "准备中",
  3: "进行中",
  4: "进行中",
  5: "成功",
  6: "失败",
  7: "成功",
  9: "阻塞"
}

class GetTopoData(action.Action):
    def POST(self):
        try:
            query_param = web.input(show_task="", is_single="1", is_parent="1", is_child="1")
            show_task = query_param.get('show_task', '')
            if not show_task:
                show_task = ','.join(get_no_succ_task())

            if not show_task:
                return base.retu(2, '请选择任务')
            is_true = lambda x: int(x) == 1
            is_single = is_true(query_param.get('is_single', 1))
            is_parent = is_true(query_param.get('is_parent', 1))
            is_child = is_true(query_param.get('is_child', 1))
            topo_manage = TopoManage(show_task, is_single, is_parent, is_child)
            topo_data = topo_manage.topo_data()
            return base.retu(0, 'success', topo_data)
        except Exception as e:
            traceback.print_exc()
            return base.retu(1, "内部方法异常：" + e.message)

    def GET(self):
        return self.POST()


class SaveRunList(action.Action):
    def POST(self):
        try:
            query_param = web.input(task="", time="", creater="")
            task = query_param.get('task', '')
            if not task:
                return base.retu(2, '缺失任务')
            time = query_param.get('time', '')
            if not time:
                return base.retu(2, '缺失重跑时间')
            creater = query_param.get('creater', '')
            if not creater:
                return base.retu(2, '缺失创建人')
            topo_manage = TopoManage(task, True, False, True)
            topo_data = topo_manage.topo_data()
            for index, value in enumerate(topo_data['nodes']):
                if value['plat'] != 'data':
                    continue
                if value['status'] not in [5,6,7]:
                    return base.retu(1, "任务" + value['task'] + "状态异常")
                tagMage = TagManage(value['ass_table'], time)
                tagMage.delete_tag()
                if value['name'] == task:
                    continue
                projectInfo = value['name'].split('.')
                project = projectInfo[0]
                run_module = '.'.join(projectInfo[1:])
                template = getRunTaskList(project, run_module, time + ' 00:00', time + ' 00:00', 'all', creater)
                insertTask(template)
            return base.retu(0, 'success')
        except Exception as e:
            traceback.print_exc()
            return base.retu(1, "内部方法异常：" + e.message)

    def GET(self):
        return self.POST()


class GetTopoCondition(action.Action):
    def GET(self):
        return self.POST()

    def POST(self):
        try:
            tasks = get_all_task()
            return base.retu(0, 'success', tasks)
        except Exception as e:
            traceback.print_exc()
            return base.retu(1, "内部方法异常：" + e.message)


class CreateTask(action.Action):
    def POST(self):
        pass

    def GET(self):
        return self.POST()
