#!/bin/bash
if [ -z $1 ]; then
cat <<eof
/usr/local/php/bin/php /data/php/games/system/serve --appdir=/data/php/games/zhanhd \
--host=0.0.0.0 --port=8000 --monitor=9000 --host6=:: --port6=8000 --verbose=0 --worker-num=8 --task-worker-num=8 \
--logfile=/data/php/log/zhanhd.log --daemonize=1
eof
else
node=$1
port=$(($node+8000))
monitor=$(($node+9000))
cat <<eof
/usr/local/php/bin/php /data/php/games/system/serve --appdir=/data/php/games/zhanhd --rundir=/node-$node/swoole \
--host=0.0.0.0 --port=$port --monitor=$monitor --host6=:: --port6=$port --verbose=0 --worker-num=8 --task-worker-num=8 \
--logfile=/data/php/log/zhanhd-$node.log --daemonize=1
eof
fi
