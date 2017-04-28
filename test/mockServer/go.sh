#!/usr/bin/env bash
path=`pwd`
echo $path
case "$1" in
    start)
        echo "start mock server env"
        php SyslogServer.php &
        php TcpEchoServer.php &
        php TraceServer.php &
        php HttpEchoServer.php &
    ;;
    stop)
        echo "stop server"
        syslog=$(ps -ef|grep 'php SyslogServer.php'|grep -v "grep"|awk -F " " '{print $2}'|sort)
        kill -9 $syslog
        tcpEchoServer=$(ps -ef|grep 'php TcpEchoServer.php'|grep -v "grep"|awk -F " " '{print $2}'|sort)
        kill -9 $tcpEchoServer
        traceServer=$(ps -ef|grep 'php TraceServer.php'|grep -v "grep"|awk -F " " '{print $2}'|sort)
        kill -9 $traceServer
        httpEchoServer=$(ps -ef|grep 'php HttpEchoServer.php'|grep -v "grep"|awk -F " " '{print $2}'|sort)
        kill -9 $httpEchoServer
    ;;
    *)
    echo "invalid options"
    ;;
esac