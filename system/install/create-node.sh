#!/bin/sh

if [ -z $1 ]; then
    node=0
    nodedir=/data
    if [ -d $nodedir/mysql ]; then
        echo 'node exists'
        exit
    fi
    mkdir -p $nodedir
else
    node=$1
    nodedir=/node-$node
    if [ -d $nodedir ]; then
        echo 'node exists'
        exit
    fi
fi

lcip=`ifconfig eth0 | grep 'inet addr' | awk '{print $2;}' | awk -F':' '{print $2; }'`
pmrt=$(($node+3000))
pass="lz.#@!.fl.mdb"
prrt=$(($node+4000))

# create node
echo "creating new node ${node}"
# create mysql
echo "mysql@${pass}:${pmrt}"
mkdir -p $nodedir/mysql/binlog
mkdir -p $nodedir/mysql/relaylog
mkdir -p $nodedir/mysql/data

# create my.cnf
cat > $nodedir/mysql/my.cnf <<eof
# mysql configurations for 4GB RAM

[client]
port                            = ${pmrt}
socket                          = /dev/shm/mysql-${node}.sock
default-character-set           = utf8

[mysqld]
basedir                         = /usr/local/mysql
datadir                         = $nodedir/mysql/data
pid-file                        = $nodedir/mysql/mysql.pid
wait_timeout                    = 259200
interactive_timeout             = 259200

bind-address                    = ${lcip}
port                            = ${pmrt}
socket                          = /dev/shm/mysql-${node}.sock
character-set-server            = utf8
max_connections                 = 128
max_connect_errors              = 10

log-bin                         = $nodedir/mysql/binlog/binlog
binlog_format                   = mixed

slow-query-log                  = 1
log-queries-not-using-indexes   = 1
long-query-time                 = 1
slow_query_log_file             = $nodedir/mysql/slow.log
log-error                       = $nodedir/mysql/error.log

default-storage-engine          = MYISAM
table_open_cache                = 512
thread_cache_size               = 8
thread_concurrency              = 8

max_allowed_packet              = 8M
read_buffer_size                = 2M
read_rnd_buffer_size            = 16M
sort_buffer_size                = 8M
join_buffer_size                = 8M

query_cache_size                = 32M
query_cache_limit               = 4M

skip-external-locking

# Replication related settings
server-id                       = 10001${pmrt}
relay-log                       = $nodedir/mysql/relaylog/relaylog
relay-log-index                 = $nodedir/mysql/relaylog/relaylog
relay-log-info-file             = $nodedir/mysql/relaylog/relaylog

# MyISAM Specific options
key_buffer_size                 = 32M
myisam_sort_buffer_size         = 64M
myisam_repair_threads           = 4

# INNODB Specific options
innodb_additional_mem_pool_size = 16M
innodb_buffer_pool_size         = 1G
innodb_write_io_threads         = 8
innodb_read_io_threads          = 8
innodb_thread_concurrency       = 16

[mysqldump]
max_allowed_packet              = 16M
quick

[mysql]
no-auto-rehash

[myisamchk]
key_buffer_size                 = 512M
sort_buffer_size                = 512M
read_buffer                     = 8M
write_buffer                    = 8M

[mysqlhotcopy]
interactive-timeout
eof

# chown
chown -R mysql $nodedir/mysql

# initial mysql data
/usr/local/mysql/scripts/mysql_install_db --basedir=/usr/local/mysql --datadir=$nodedir/mysql/data --user=mysql

# start new mysql server
/usr/local/mysql/bin/mysqld_safe --defaults-file=$nodedir/mysql/my.cnf 2>&1 > /dev/null &
while [ `netstat -tnlp | grep mysqld | grep $pmrt | wc -l` -eq 0 ]; do
    echo 'waiting mysqld'
    sleep 1
done

# setup password
/usr/local/mysql/bin/mysqladmin -S /dev/shm/mysql-${node}.sock -uroot password ${pass}

# create new databases
/usr/local/mysql/bin/mysql -S /dev/shm/mysql-${node}.sock -uroot -p${pass} -e 'create database `zhanhd.global`'
/usr/local/mysql/bin/mysql -S /dev/shm/mysql-${node}.sock -uroot -p${pass} -e 'create database `zhanhd.player`'

# setup new user
/usr/local/mysql/bin/mysql -S /dev/shm/mysql-${node}.sock -uroot -p${pass} -e 'grant all privileges on `zhanhd.player`.* to zhanhd@"%" identified by "2abePZEyzPsTWWcr"'
/usr/local/mysql/bin/mysql -S /dev/shm/mysql-${node}.sock -uroot -p${pass} -e 'grant all privileges on `zhanhd.global`.* to zhanhd@"%" identified by "2abePZEyzPsTWWcr"'
/usr/local/mysql/bin/mysql -S /dev/shm/mysql-${node}.sock -uroot -p${pass} -e 'grant all privileges on `zhanhd.http`.* to zhanhd@"%" identified by "2abePZEyzPsTWWcr"'

# import data
/usr/local/mysql/bin/mysql -S /dev/shm/mysql-${node}.sock -uroot -p${pass} < /data/php/games/zhanhd/data/global.sql
/usr/local/mysql/bin/mysql -S /dev/shm/mysql-${node}.sock -uroot -p${pass} < /data/php/games/zhanhd/data/player.sql

echo "redis:${prrt}"

# create redis directories
mkdir -p $nodedir/redis/etc
mkdir -p $nodedir/redis/log
mkdir -p $nodedir/redis/var
mkdir -p $nodedir/redis/run

# create my.cnf
cat > $nodedir/redis/etc/redis.conf <<eof
daemonize yes
pidfile $nodedir/redis/var/redis.pid
port ${prrt}
tcp-backlog 511
bind ${lcip}
timeout 0
tcp-keepalive 60
loglevel notice
logfile "$nodedir/redis/log/notice.log"
databases 16
save 900 1
save 300 10
save 60 10000
stop-writes-on-bgsave-error yes
rdbcompression yes
rdbchecksum yes
dbfilename dump.rdb
dir $nodedir/redis/var
slave-serve-stale-data yes
slave-read-only yes
repl-disable-tcp-nodelay no
slave-priority 100
maxclients 1024
maxmemory 1gb
appendonly no
appendfilename "appendonly.aof"
appendfsync everysec
no-appendfsync-on-rewrite no
auto-aof-rewrite-percentage 100
auto-aof-rewrite-min-size 64mb
lua-time-limit 5000
slowlog-log-slower-than 10000
slowlog-max-len 128
notify-keyspace-events ""
hash-max-ziplist-entries 512
hash-max-ziplist-value 64
list-max-ziplist-entries 512
list-max-ziplist-value 64
set-max-intset-entries 512
zset-max-ziplist-entries 128
zset-max-ziplist-value 64
hll-sparse-max-bytes 3000
activerehashing yes
client-output-buffer-limit normal 0 0 0
client-output-buffer-limit slave 256mb 64mb 60
client-output-buffer-limit pubsub 32mb 8mb 60
hz 10
aof-rewrite-incremental-fsync yes
eof

# start new redis server
/usr/local/bin/redis-server $nodedir/redis/etc/redis.conf

# create swoole directories
mkdir -p $nodedir/swoole/runtimes
mkdir -p $nodedir/swoole/conf

cat > $nodedir/swoole/conf/zhanhd.config.php <<eof
<?php
return [
    'pdo' => [
        'host' => '$lcip',
        'port' => '$pmrt',
        'user' => 'zhanhd',
        'pass' => '2abePZEyzPsTWWcr',
    ],
    'redis' => [
        'host' => '$lcip',
        'port' => '$prrt',
        'timeout' => 2,
        'retry' => 100,
    ],
];
eof
chown -R ubuntu $nodedir/swoole

# backup
mkdir -p $nodedir/backup/mysql
mkdir -p $nodedir/backup/shell

cat > $nodedir/backup/shell/mysql <<eof
#!/bin/bash
curdate=`date +%Y-%m-%d`

/usr/local/mysql/bin/mysqldump -S /dev/shm/mysql-$node.sock -uzhanhd -p2abePZEyzPsTWWcr --no-create-info --skip-extended-insert zhanhd.player > /data/backup/mysql/player-$curdate.sql
/usr/local/mysql/bin/mysqldump -S /dev/shm/mysql-$node.sock -uzhanhd -p2abePZEyzPsTWWcr --no-create-info --skip-extended-insert zhanhd.global > /data/backup/mysql/global-$curdate.sql
eof
