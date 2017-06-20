<?php

/**
 * Eloquent初始化代码
 * User: Liang Tao (liangtaohy@163.com)
 * Date: 17/6/20
 * Time: AM6:27
 */

require_once APP_PATH . '/../../phplib/vendor/autoload.php';

$database = [
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'database'  => 'xlegal_saas',
    'username'  => 'root',
    'password'  => 'Tncr6dAuibF677gi',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => 'xlegal_',
];

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as DB;
$db = new DB;

$db->addConnection($database);

$db->setAsGlobal();

$db->bootEloquent();