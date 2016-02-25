<?php
/**
 * Application测试用例
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/23 19:58
 */

require_once(dirname(__FILE__) . '/../../phplib_headers.php');
//require_once(dirname(__FILE__) . '/HiAction.class.php');

define('DEBUG', true);
define('PHPLIB_PATH', dirname(__FILE__) . '/../../');

class UnitTest_Application implements IBaseTest
{
    /**
     * 初始化日志
     */
    private static function initlog()
    {
        $log_file = dirname(__FILE__) . '/data/test_log.txt';
        $GLOBALS['LOG'] = array(
            'log_level' => MeLog::LOG_LEVEL_ALL,
            'log_file'  => $log_file,
        );

        MeLog::getInstance()->clean();
    }

    /**
     * 测试用例执行入口
     */
    public static function run()
    {
        self::initlog();
        Controller::$config = array(
            Controller::TYPE_HASH_MAPPING => array(
                '/test/app'     => array('TestAction'),
            ),
            Controller::TYPE_PREFIX_MAPPING => array(
                '/test/hi'      => array('HiAction'),
            ),
        );
        Application::start();
        SimpleTest::summary();
    }
}

UnitTest_Application::run();