#!/usr/bin/env python2.7
# -*- coding: utf-8 -*-

import os,commands
import sys
import socket


def get_open_port():

    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.bind(("",0))
    s.listen(1)
    port = s.getsockname()[1]
    s.close()
    return port


def construct_tunnel(user_name,port,remote_query_ip,remote_query_port):
    tunnel_cmd_template = 'ssh -f %s@osys11.meilishuo.com -L %s:%s:%s -N'
    cmd = tunnel_cmd_template % (user_name, port,remote_query_ip, remote_query_port )
    os.system(cmd)


def get_tunnels():
    cmd="ps axu |grep 'ssh -f' |grep -v grep|awk '{print $15}'"
    r=commands.getoutput(cmd)
    r_list = r.split('\n')
    ip_dict={}

    for item in r_list:
        try:
            index=item.index(":")
            ip_dict[str(str(item)[index+1:].strip())]=item[:index]
        except:
            continue
    return ip_dict



if __name__=="__main__":
    print get_open_port()