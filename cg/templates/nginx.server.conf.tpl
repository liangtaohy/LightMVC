server {
    listen 80; # listen port
    server_name {{APP_NAME}}.xman.legal; # app test domain

    underscores_in_headers on;

    set $flag 0;
    if ($server_port = 8443) {
        set $flag 1;
    }

    ssl_protocols SSLv3 TLSv1;
    ssl_certificate      /home/work/xdp/webserver/conf/ssl_certificate/xman.legal.crt;
    ssl_certificate_key  /home/work/xdp/webserver/conf/ssl_certificate/xman.legal.key;
#ssl_certificate /home/work/nginx/conf/key/nginx.pem;
#ssl_certificate_key /home/work/nginx/conf/key/server.pem;
    ssl_ciphers DHE-DSS-RC4-SHA:EXP-KRB5-RC4-SHA:KRB5-RC4-SHA:RC4-SHA:DHE-RSA-AES128-SHA:AES128-SHA:DHE-DSS-AES128-SHA:HIGHT:!aNULL;
#ssl_ciphers RC4:AES128-SHA:3DES:!EXP:!aNULL:!kEDH:!ECDH;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:100m;
    ssl_session_timeout 10m;

    include rewrites.conf;

    if ($request_uri ~* "(~|\.sql|\.inc|\.bak|\.old|\.lua|\.tpl)$") {
        return 403;
    }

    location ^~ /{{APP_NAME}}/ {
        root {{LOCAL_DEPLOY_PATH}};
        index index.php;
        rewrite ^/{{APP_NAME}}(/[^\?]*)?((\?.*)?)$ /phpsrc/app/{{APP_NAME}}/index.php$1$2 last;
        break;
    }

    location ~* \.php {
        root {{LOCAL_DEPLOY_PATH}};
        include fastcgi.conf;
        fastcgi_pass 127.0.0.1:9091;
        fastcgi_split_path_info ^(.+\.php)(.*)$;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_keep_conn on;
        fastcgi_param XLEGAL_ENV "development";
        break;
    }
}

