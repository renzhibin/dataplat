startsupervisord(){
/home/work/online/python/bin/supervisord -c supervisord.conf
}
start(){
#/home/work/online/python/bin/supervisorctl start webapi
#/home/service/python2.7/bin/uwsgi   -x webapi.xml
#/usr/bin/uwsgi  -x webapi.xml --plugin python --limit-post=67108864
/usr/bin/uwsgi  -x webapi.xml --plugin python --limit-post=67108864
}

stop(){
#/home/work/online/python/bin/supervisorctl stop webapi
#killall -9 uwsgi
#cat ../../../data/webapi.pid |xargs kill -9
/usr/sbin/lsof  -i :7625 | awk '{print $2}'|grep -v PID|xargs kill -9
}

case $1 in
start) start;;  
stop) stop;;
restart) 
    stop
    sleep 1
    start
    ;;
supervisord) startsupervisord;;    

*) echo "not correct input";;
esac 
