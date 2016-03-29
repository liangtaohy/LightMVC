<?php
/**
 * env init
 * 环境初始化
 * @author liangtaohy@163.com
 * @date 16/3/28 time: PM8:12
 */

define('IS_DEBUG', false);
define('APP_NAME' , '{{APP_NAME}}');
define('DEPLOY_ROOT', APP_PATH . '/../../');
define('APP_PATH', dirname(__FILE__) . '/..');
define('APP_CONF_PATH', APP_PATH . '/../conf/' . APP_NAME);

require_once(DEPLOY_ROOT . '/conf/phplib/Public.conf.php');
require_once(APP_PATH . '/controller/uri_dispatch_rules.php');

/** We will use autoloader instead of include path. */
$appIncludePath = APP_PATH .'/action/:'.
    APP_PATH .'/models/:' .
    APP_PATH .'/common/:' .
    APP_PATH . '/models/dao:' .
    APP_PATH . '/models/page' .
    APP_PATH . '/library:' .
    APP_CONF_PATH . '/:';
ini_set('include_path', ini_get('include_path') . ':' . $appIncludePath);

//日志打印相关参数定义
$GLOBALS['LOG'] = array(
    'log_level' => MeLog::LOG_LEVEL_ALL,
    'log_file'  => DEPLOY_ROOT . '/logs/{{APP_NAME}}.log',
);