#!/bin/bash

echo "use branch sync"
exit 0

echo "exporting system ..."
rm -rf /tmp/php/games/system && mkdir -p /tmp/php/games && /usr/bin/svn export /data/php/games/system /tmp/php/games/system > /dev/null
echo "exporting system done"

echo "exporting zhanhd ..."
rm -rf /tmp/php/games/zhanhd && mkdir -p /tmp/php/games && /usr/bin/svn export /data/php/games/zhanhd /tmp/php/games/zhanhd > /dev/null
echo "exporting zhanhd done"

echo "uploading codes ..."
/usr/bin/rsync -avz -e 'sshpass -p ZhanHD2015)(* ssh -l ubuntu' /tmp/php/games 183.131.78.223:/home/ubuntu/php > /dev/null
echo "uploading codes done"

echo "uploading mysql ..."
/usr/bin/rsync -avz -e 'sshpass -p ZhanHD2015)(* ssh -l ubuntu' /data/backup/mysql/config-$(date +"%Y-%m-%d").sql 183.131.78.223:/home/ubuntu/php/games/config-$(date +"%Y-%m-%d").sql > /dev/null
/usr/bin/rsync -avz -e 'sshpass -p ZhanHD2015)(* ssh -l ubuntu' /data/backup/mysql/global-$(date +"%Y-%m-%d").sql 183.131.78.223:/home/ubuntu/php/games/global-$(date +"%Y-%m-%d").sql > /dev/null
/usr/bin/rsync -avz -e 'sshpass -p ZhanHD2015)(* ssh -l ubuntu' /data/backup/mysql/player-$(date +"%Y-%m-%d").sql 183.131.78.223:/home/ubuntu/php/games/player-$(date +"%Y-%m-%d").sql > /dev/null
echo "uploading mysql done"
