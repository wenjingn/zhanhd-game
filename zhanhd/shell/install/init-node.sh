#!/bin/bash
lcip=`ifconfig eth0 | grep 'inet addr' | awk '{print $2;}' | awk -F':' '{print $2; }'`
if [ -z $1 ]; then
/usr/local/mysql/bin/mysql -uzhanhd -p2abePZEyzPsTWWcr -h${lcip} -P3000 < /data/php/games/zhanhd/shell/install/source/init-db.sql &&
/usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/flush.php --appdir=/data/php/games/zhanhd &&
/usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/flush.php --appdir=/data/php/games/zhanhd --next=0 &&
/usr/local/php/bin/php /data/php/games/zhanhd/shell/producer/zombie-producer.php --appdir=/data/php/games/zhanhd --zone=1
else
node=$1
port=$(($node+3000))
rundir=/node-$node/swoole
/usr/local/mysql/bin/mysql -uzhanhd -p2abePZEyzPsTWWcr -h${lcip} -P$port < /data/php/games/zhanhd/shell/install/source/init-db.sql &&
/usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/flush.php --appdir=/data/php/games/zhanhd --rundir=$rundir &&
/usr/local/php/bin/php /data/php/games/zhanhd/shell/crontab/flush.php --appdir=/data/php/games/zhanhd --next=0 --rundir=$rundir &&
/usr/local/php/bin/php /data/php/games/zhanhd/shell/producer/zombie-producer.php --appdir=/data/php/games/zhanhd --rundir=$rundir --zone=$node
fi
