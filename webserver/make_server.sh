#!/usr/bin/env bash

DIR=`pwd`
mkdir run
scp  work@xman.legal:/home/work/xdp/webroot/tools/nginx_modules.tgz .
tar -zxvf nginx_modules.tgz

/bin/rm -rf node_modules.tgz

mkdir tmp
cd tmp
wget http://nginx.org/download/nginx-1.8.1.tar.gz -O nginx-1.8.1.tar.gz

tar -zxvf nginx-1.8.1.tar.gz

cd nginx-1.8.1

./configure \
--with-http_realip_module \
--with-http_stub_status_module \
--with-http_addition_module \
--add-module=$DIR/nginx_modules/echo-nginx-module-master \
--add-module=$DIR/nginx_modules/headers-more-nginx-module-master \
--add-module=$DIR/nginx_modules/memc-nginx-module-master \
--add-module=$DIR/nginx_modules/nginx-http-concat-master \
--add-module=$DIR/nginx_modules/ngx_devel_kit-master \
--add-module=$DIR/nginx_modules/ngx_http_consistent_hash-master \
--add-module=$DIR/nginx_modules/ngx_http_enhanced_memcached_module-master \
--add-module=$DIR/nginx_modules/ngx_http_upstream_ketama_chash-0.6 \
--add-module=$DIR/nginx_modules/srcache-nginx-module-master \
--with-ld-opt='-stdlib=libstdc++ -L/usr/local/Cellar/openssl/1.0.2a-1/lib' \
--with-pcre=$DIR/nginx_modules/pcre-8.38 \
--prefix=$DIR

if [[ $? -ne 0 ]];then
	echo 'error occured\n'
	exit
fi
make && make install
cd $DIR
/bin/rm -rf tmp
/bin/rm -rf node_modules

cp -rf templates/conf .
cp -rf templates/bin .

./sbin/nginx -t
