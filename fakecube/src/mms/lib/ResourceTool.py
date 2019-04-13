#!/usr/bin/env python
# -*- coding:utf8 -*-
"""
    @author: pengbangzhong
    @time: 18/4/4 上午10:21
"""
import sys

reload(sys)
sys.setdefaultencoding('utf8')
import traceback
import requests
import simplejson as json
'''
    资源管理
'''

class ResourceTool(object):

    def __init__(self):
        pass


    def check_used_vcore_threshold(self,threshold=95):
        '''
        目前
        Args:
            threshold: 使用vcore占比

        Returns:
            超过设定阈值占比返回True,反之False



        '''
        try:

            resp=requests.get('http://10.8.10.47:8088/ws/v1/cluster/metrics',timeout=1)
            re_status=resp.status_code

            if re_status and re_status==200:
                resp_json=json.loads(resp.text)
                totalVirtualCores=int(resp_json['clusterMetrics']['totalVirtualCores'])
                allocatedVirtualCores =int(resp_json['clusterMetrics']['allocatedVirtualCores'])
                now_rate=(float(allocatedVirtualCores)/float(totalVirtualCores))*100
                print 'hadoop vcore totalVirtualCores:{},allocatedVirtualCores:{} rate:{}'.format(totalVirtualCores,allocatedVirtualCores,now_rate)
                if int(threshold)>int(now_rate):
                    print threshold,now_rate
                    return False

        except Exception, e:
            traceback.print_exc()

        return True









if __name__ == '__main__':
    rt=ResourceTool()
    print rt.check_used_vcore_threshold()



