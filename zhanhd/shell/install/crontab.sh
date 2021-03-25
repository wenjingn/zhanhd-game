#!/bin/bash
if [ -z $1 ]; then
cat <<eof
0 22 * * * /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/pvp-reward.php --appdir=/data/php/games/zhanhd
0 4 * * * /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/flush.php --appdir=/data/php/games/zhanhd
0 0 * * 1 /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/actins-reward.php --appdir=/data/php/games/zhanhd
0 3 * * * /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/counter.php --appdir=/data/php/games/zhanhd
30 11 * * 2 /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/top32.php --appdir=/data/php/games/zhanhd
30 12 * * 6 /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/top32-reward.php --appdir=/data/php/games/zhanhd

0 4 * * * /usr/local/php/bin/php /data/php/games/zhanhd/tests/monitor/restart.php --appdir=/data/php/games/zhanhd --port=9000
30 20 * * * /usr/local/php/bin/php /data/php/games/zhanhd/tests/monitor/worldboss-over.php --appdir=/data/php/games/zhanhd --port=9000

*/5 * * * * /usr/local/php/bin/php /data/php/games/zhanhd/shell/platform/apple/backend-order-query.php --appdir=/data/php/games/zhanhd
eof
else
node=$1
rundir=/node-$node/swoole
monitor=$(($node+9000))
cat <<eof
0 22 * * * /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/pvp-reward.php --appdir=/data/php/games/zhanhd --rundir=$rundir
0 4 * * * /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/flush.php --appdir=/data/php/games/zhanhd --rundir=$rundir
0 0 * * 1 /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/actins-reward.php --appdir=/data/php/games/zhanhd --rundir=$rundir
0 3 * * * /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/counter.php --appdir=/data/php/games/zhanhd --rundir=$rundir
30 11 * * 2 /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/top32.php --appdir=/data/php/games/zhanhd --rundir=$rundir
30 12 * * 6 /usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/top32-reward.php --appdir=/data/php/games/zhanhd --rundir=$rundir

0 4 * * * /usr/local/php/bin/php /data/php/games/zhanhd/tests/monitor/restart.php --appdir=/data/php/games/zhanhd --port=$monitor
30 20 * * * /usr/local/php/bin/php /data/php/games/zhanhd/tests/monitor/worldboss-over.php --appdir=/data/php/games/zhanhd --port=$monitor

*/5 * * * * /usr/local/php/bin/php /data/php/games/zhanhd/shell/platform/apple/backend-order-query.php --appdir=/data/php/games/zhanhd --rundir=$rundir
eof
fi
