#!/bin/sh
# zhanhd-install

# dev-env
echo "setup dev-env"
apt-get install -y gcc g++ make cmake
apt-get install -y zip
apt-get install -y bison
apt-get install -y re2c
apt-get install -y libxml2 libxml2-dev
apt-get install -y openssl libssl-dev
apt-get install -y curl libcurl3 libcurl4-openssl-dev php5-curl
ln -s /usr/lib/x86_64-linux-gnu/libssl.so  /usr/lib
apt-get install -y libncurses5-dev
apt-get install -y autoconf
mkdir -p /data/php
mkdir /data/php/log
chown -R ubuntu /data/php

# sysctl
echo "setup sysctl"

cat > /etc/sysctl.d/30-swoole.conf <<eof
net.unix.max_dgram_qlen = 128
net.ipv4.tcp_mem  = 379008 505344 758016
net.ipv4.tcp_wmem = 4096   16384  4194304
net.ipv4.tcp_rmem = 4096   87380  4194304
net.core.wmem_default = 8388608
net.core.rmem_default = 8388608
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
net.ipv4.tcp_tw_reuse = 1
net.ipv4.tcp_tw_recycle = 1
net.ipv4.tcp_syncookies = 1
net.ipv4.tcp_max_syn_backlog = 81920
net.ipv4.tcp_synack_retries = 3
net.ipv4.tcp_syn_retries = 3
net.ipv4.tcp_fin_timeout = 30
net.ipv4.tcp_keepalive_time = 300
net.ipv4.tcp_tw_reuse = 1
net.ipv4.tcp_tw_recycle = 1
net.ipv4.ip_local_port_range = 20000 65000
net.ipv4.tcp_max_tw_buckets = 200000
net.ipv4.route.max_size = 5242880
eof

# limits
echo "setup limits"

cat > /etc/security/limits.d/swoole.conf <<eof
* soft nofile 262140
* hard nofile 262140
root soft nofile 262140
root hard nofile 262140
* soft core unlimited
* hard core unlimited
root soft core unlimited
root hard core unlimited
eof

# libevent
echo "setup libevent"

tar zxvf libevent* > /dev/null
cd libevent*
./configure --prefix=/usr/ > /dev/null && make > /dev/null && make install > /dev/null && cd .. && rm -rf libevent*

# libiconv
echo "setup libiconv"

tar zxvf libiconv* > /dev/null
cd libiconv*
sed -i '698 i#ifdef defined(__GLIBC__) && !defined(__UCLIBC__) && !__GLIBC_PREREQ(2, 16)' srclib/stdio.in.h
sed -i '700 i#endif' srclib/stdio.in.h
./configure --prefix=/usr/ > /dev/null && make > /dev/null && make install > /dev/null && cd .. && rm -rf libiconv*

# libmcrypt
echo "setup libmcrypt"

tar zxvf libmcrypt* > /dev/null
cd libmcrypt*
./configure --prefix=/usr/ > /dev/null && make > /dev/null && make install > /dev/null && ldconfig > /dev/null && cd libltdl && ./configure --prefix=/usr/ --enable-ltdl-install > /dev/null && make > /dev/null && make install > /dev/null && cd ../.. && rm -rf libmcrypt*

# mhash
echo "setup mhash"

tar zxvf mhash* > /dev/null
cd mhash*
./configure --prefix=/usr/ > /dev/null && make > /dev/null && make install > /dev/null && cd .. && rm -rf mhash*

# mcrypt
echo "setup mcrypt"

tar zxvf mcrypt* > /dev/null
cd mcrypt*
ldconfig > /dev/null && ./configure --prefix=/usr/ > /dev/null && make > /dev/null && make install > /dev/null && cd .. && rm -rf mcrypt*

# pcre
echo "setup pcre"

tar zxvf pcre* > /dev/null
cd pcre*
./configure --prefix=/usr/ > /dev/null && make > /dev/null && make install > /dev/null && cd .. && rm -rf pcre*

# apache
echo "setup apache"

# httpd-2.4.*
tar zxvf httpd-2.4.*.tar.gz > /dev/null

# apr-1.5.*
tar zxvf apr-1.5.*.tar.gz > /dev/null

# apr-util-1.5.*
tar zxvf apr-util-1.5.*.tar.gz > /dev/null

# httpd
cd httpd-2.4.*
mkdir -p srclib/apr
mkdir -p srclib/apr-util
cp -R ../apr-1.5.*/* srclib/apr/
cp -R ../apr-util-1.5.*/* srclib/apr-util/
./configure --prefix=/usr/local/apache --enable-dav --enable-modules=all --with-mpm=prefork > /dev/null && make > /dev/null && make install > /dev/null && cd .. && rm -rf httpd* apr*

# php
echo "setup php"

tar zxvf php* > /dev/null
cd php*
./configure --prefix=/usr/local/php \
--with-apxs2=/usr/local/apache/bin/apxs \
--disable-rpath \
--enable-bcmath \
--enable-inline-optimization \
--enable-mbstring \
--enable-pcntl \
--enable-shmop \
--enable-soap \
--enable-sockets \
--enable-sysvsem \
--enable-xml \
--enable-zip \
--with-curl \
--with-freetype-dir \
--with-iconv-dir \
--with-libxml-dir \
--with-mcrypt \
--with-mhash \
--with-mysql=mysqlnd \
--with-mysqli=mysqlnd \
--with-openssl \
--with-pcre-dir \
--with-pdo-mysql=mysqlnd \
--with-xmlrpc \
--with-zlib \
--with-zend-vm=GOTO > /dev/null

grep -n 'EXTRA_LIBS = -' Makefile | cut -d':' -f 1 | awk '{ printf "sed -i \47%d s/$/& -liconv/\47 Makefile\n", $1 }' | sh
make > /dev/null && make install > /dev/null && cd .. && rm -rf php*

# redis
echo "setup redis"

tar zxvf redis* > /dev/null
cd redis*
make > /dev/null && make install > /dev/null && cd .. && rm -rf redis*

# mysql
echo "setup mysql"

groupadd mysql
useradd -r -g mysql mysql

tar zxvf mysql* > /dev/null
cd mysql*
cmake . > /dev/null && cmake . \
-DWITH_ARCHIVE_STORAGE_ENGINE=1 \
-DWITH_BLACKHOLE_STORAGE_ENGINE=1 \
-DWITH_MEMORY_STORAGE_ENGINE=1 \
-DWITH_EMBEDDED_SERVER=1 \
-DWITH_EXTRA_CHARSETS=complex \
-DWITH_INNODB_MEMCACHED=1 \
-DWITH_PARTITION_STORAGE_ENGINE=1 \
-DWITH_READLINE=1 \
-DWITH_ZLIB=bundled \
-DWITH_SSL=bundled \
-DWITH_READLINE=system \
-DDEFAULT_CHARSET=utf8 \
-DDEFAULT_COLLATION=utf8_unicode_ci \
-DENABLED_LOCAL_INFILE=1 > /dev/null && \
make > /dev/null && make install > /dev/null && cd .. && rm -rf mysql*

# php-extensions
ln -s /usr/local/php/lib/php/extensions/no-debug-non-zts-20131226 /usr/local/php/lib/php/extensions/ext

unzip npbd.zip
cd npbd/
/usr/local/php/bin/phpize
./configure --with-php-config=/usr/local/php/bin/php-config --enable-npbd
make && make install
cd .. && rm -rf npbd*

unzip swoole-1.7.13-new.zip
cd swoole-1.7.13-new/
/usr/local/php/bin/phpize
./configure --with-php-config=/usr/local/php/bin/php-config --enable-sockets --enable-openssl --with-swoole --enable-swoole
make && make install
cd .. && rm -rf swoole*

tar zxvf yaf* > /dev/null
cd yaf*
/usr/local/php/bin/phpize
./configure --with-php-config=/usr/local/php/bin/php-config
make && make install
cd .. && rm -rf yaf*

tar zxvf rdsphp* > /dev/null
cd rdsphp*
/usr/local/php/bin/phpize
./configure --with-php-config=/usr/local/php/bin/php-config
make && make install
cd .. && rm -rf rdsphp*

# etc
mv source/php.ini /usr/local/php/lib/
lcip=`ifconfig eth0 | grep 'inet addr' | awk '{print $2;}' | awk -F':' '{print $2; }'`
cat >> /usr/local/php/lib/php.ini <<eof
[zhanhd]
pdo.dsn.zhanhd = "mysql:host=$lcip;port=3000;charset=utf8;username=zhanhd;password=2abePZEyzPsTWWcr"
rds.cnt.zhanhd = "redis:host=$lcip;port=4000;timeout=2;retry=100"
eof

# crontab

# rc.local
