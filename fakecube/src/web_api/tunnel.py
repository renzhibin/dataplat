#!/usr/bin/env python2.7
#coding = utf-8

import web
import os
import sys
import socket

sys.path.append('..')

from query_connect import  TunnelQuery
from lib import const as conf


def get_open_port():
       # import socket
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.bind(("",0))
        s.listen(1)
        port = s.getsockname()[1]
        s.close()
        return port

def port_used(test_port):

    cmd = "netstat -nltp | awk '{print $4 }' > ./conf/used_port.txt"
    #cmd = 'ssh -f chengyunwang@osys11.meilishuo.com -L 9999:172.16.10.37:3306 -N'
    os.system(cmd)

    file = open('./conf/used_port.txt','r')
    lines=file.readlines()
    try:
        lines = lines[2:]
        port_list = [e.strip('\n').split(':')[-1] for e in lines]
    except:
        return -1

    port_list = [int(e) for e in port_list ]

    for e in port_list:
       if e == test_port:
            return True
    
    return False
    

def  construct_tunnel():    
      # thie function has 2 step: write file and construct tunnel.
      # when writeing files ,the format is project_name ,local_port, remote_ip, remote_port.
      #
      tunnel_file = conf.tunnel_file  # 'tunnel_file.txt'
      port_explain = conf.port_explain
      port = -1
      tunnel_cmd_template = 'ssh -f %s@osys11.meilishuo.com -L %s:%s:%s -N'
      if not os.path.exists( tunnel_file ):
          port = get_open_port()
          f = open(tunnel_file, 'w')
          f.write( port_explain + '\t' + str(port) + '\t' + conf.remote_query_ip + '\t'+ conf.remote_query_port + '\n' )
          f.close()
          # build tunnel
          cmd = tunnel_cmd_template % (conf.user_name, port, conf.remote_query_ip, conf.remote_query_port )
          os.system(cmd)
      else:
          #print 1234 
          f = open( tunnel_file, 'r' )
          lines = f.readlines()         
          lines_port = [e.split('\t') for e in lines]           
          #print lines_port
          f.close()

          flag = False
          for e in lines_port:
              if e[0] == port_explain:
                  flag = True 
                  port = int(e[1])
                  break
          if flag == False:
             port = get_open_port()
             f = open( tunnel_file, 'a')
             f.write( port_explain + '\t' + str(port) + '\t' + conf.remote_query_ip + '\t'+ conf.remote_query_port +'\n') 
             f.close()
             
             cmd = tunnel_cmd_template % (conf.user_name, port, conf.remote_query_ip, conf.remote_query_port )
             os.system(cmd)
          else:
             temp_flag =port_used(port) 
             if temp_flag == False:   
                cmd = tunnel_cmd_template % (conf.user_name, port, conf.remote_query_ip, conf.remote_query_port )
                os.system(cmd)

      #print 'port,',port      
      db_query = TunnelQuery(port)
 
if __name__ == "__main__":
    print   port_used(43303)
