# LightMVC
A light mvc framework implemented by PHP.
一个php实现的轻量级的MVC框架
# XDP

XDP是一个轻量级的MVC框架，目前，支持php和nodejs。其中，php部分采用的是LightMVC。

## 编码规范
[[https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md|PSR-1基本编码规范]]

PSR规范共有PSR-0, PSR-1, PSR-2, PSR-4。只需要看下PSR-1和PSR-4即可。

PSR-4定义了AUTOLOADING规范，是现在NAMESPACE遵循的。
[[https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md|PSR-4 Auto Loader]]


## 部署目录

xdp的部署目录，根目录统一为
```
/home/work/xdp/
```

目录结构为
```
/home/work/xdp

├── phpsrc
│   ├── app
│   ├── conf
│   ├── logs
│   ├── phplib
├── README.md
├── receiver.php
├── static
│   ├── saas
│   ├── m
├── templates
│   ├── plugins
│   ├── README.md
│   ├── templates
│   ├── templates_c
├── tools
│   └── statistics.sh
├── webroot
└── webserver
    ├── bin
    ├── client_body_temp
    ├── conf
    ├── conf.20160602.bak
    ├── fastcgi_temp
    ├── html
    ├── logs
    ├── make_server.sh
    ├── nginx_modules
    ├── nginx_modules.tgz
    ├── proxy_temp
    ├── run
    ├── sbin
    ├── scgi_temp
    ├── templates
    └── uwsgi_temp
```

phpsrc的目录结构为

```
├── app
│   ├── {{app_name}}
├── conf
│   ├── {{app_name}}
├── logs
│   ├── backup
│   ├── {{app_name}}.log
│   ├── {{app_name}}.log.wf
├── phplib // 公共库
│   ├── bdHttp // http client
│   ├── build.sh
│   ├── common // common库
│   ├── conf
│   ├── config
│   ├── db // mysql db | mongo db
│   ├── email
│   ├── extlib
│   ├── framework // 框架层
│   ├── frequency
│   ├── local_deploy.sh
│   ├── memcached
│   ├── phplib_headers.php
│   ├── PHPWord
│   ├── README.md
│   ├── redis // redis封装
│   ├── smarty // smarty plugins
│   ├── sms // 短信服务封装
│   ├── templates
│   ├── testfw
│   ├── utils // 实用工具
│   ├── vendor
│   └── xunsearch // 暂不支持，项目使用时手工引用
```

## 安装

```
git clone git@github.com:liangtaohy/LightMVC.git
```

## 如何生成一个应用

  - 切换到cg目录

```
cd cg
```

  - 修改应用的配置

```
vim config.php
```

  - 运行php run.php生成应用

```
php run.php
```

如果本机没有php，请自行装一个。后续会提供一套webserver环境。

## 测试

```
http://{host}:{port}/{APP_NAME}/api/sample
```

返回：

```
Hi,guy! Welcome to crm
```

## 配置

### mongodb配置

```

cat ${DEPLOY_ROOT}/conf/CimongoConfig.class.php

<?php

/**
 * Created by PhpStorm.
 * User: liangtao
 * Date: 16/3/31
 * Time: AM12:02
 */
class CimongoConfig
{
    // This should be override by Application's conf
    public static $conf = [
        'mongo' => [
            'default'   => [
                'host'          => '127.0.0.1',
                'port'          => '27017',
		'user'		=> '',
		'password'	=> '',
                'dbname'        => 'test_crm',
                'query_safety'  => TRUE,
                'db_flag'       => TRUE,
            ],
        ],
    ];
}

```

# XDP线上部署

* 下载[opbin.tgz]{{:opbin.tgz|}}

## 初始化线上环境

```

cd opbin
source init_work.sh

```

## 安装nginx, php,以及php的mongo扩展，redis扩展

```
cd opbin
python init_server.py
```

## 应用上线

以crm上线为例：

```
cd opbin
python deploy.py crm git@code.csdn.net:Xlegal-group02/crm.git v0.1.001
```

格式为python deploy.py $appname $git_address $tag

## 公共配置上线

公共配置指nginx.conf, php.conf,以及数据库配置等，目前，还没有想到好的方案。大家各自线上去操作吧。。。

# 框架约定

### 目录

```
{APP_NAME} // 应用名
├── actions
│   ├── {APP_NAME}ApiAction.class.php
│   ├── {APP_NAME}InternalApiAction.class.php
│   ├── /api/[external|internal|admin ..]/version
│   └── cli
├── build.sh  // APP打包
├── common
│   └── env_init.php // 环境配置
├── conf // 应用的相关配置文件
│   └── {APP_NAME}Config.class.php // 应用内能用的配置文件
├── controller // 路由规则层
│   └── uri_dispatch_rules.php // 路由器
├── index.php // 应用的入口页
├── library  // 库
├── local_deploy.sh  // 部署到测试机
├── models
│   ├── dao  // 数据访问层
│   └── page // 业务逻辑层
└── output
    └── {APP_NAME}.tar.gz  // 可部署版本
    
```

### controller

#### api uri格式约定

```
/{app_name}/[internal|admin]/api/{version}/[entity...]
```

示例：
```
/saas/internal/api/v2/cf/hours/recharge (internal api)
/saas/api/v1/cf/hours/recharge (external api)
/saas/admin/api/v2/cf/hours/recharge (admin api)
```
----

### actions

#### action类文件名约定

```
[Internal|Admin|External]{App_name}[Entity...]Api.class.php
```
----

#### action层功能约定

* 简单的业务逻辑
* 安全性检查
* 参数检查

----

# 部署到测试机

```
测试机地址为 ssh work@xman.legal
部署方法为：
在本地app内，执行 . local_deploy.sh即可
```
