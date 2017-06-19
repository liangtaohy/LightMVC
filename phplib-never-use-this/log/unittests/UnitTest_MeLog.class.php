<?php

/**
 * MeLog单元测试
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 10:33
 */
require_once(dirname(__FILE__) . "/../../phplib_headers.php");

class UnitTest_MeLog implements IBaseTest
{
    /**
     * 测试notice日志
     */
    private static function Test_notice()
    {
        MeLog::getInstance()->notice("logtest", 1, array('key1'=>'value1','key2'=>'value2'));
    }

    /**
     * 测试debug日志
     */
    private static function Test_debug()
    {
        MeLog::getInstance()->debug("logtest", 1, array('key1'=>'value1','key2'=>'value2'));
    }

    /**
     * 测试warning日志
     */
    private static function Test_warning()
    {
        MeLog::getInstance()->warning("logtest", 1, array('key1'=>'value1','key2'=>'value2'));
    }

    /**
     * 测试用例执行入口
     */
    public static function run()
    {
        $log_file = dirname(__FILE__) . '/data/test_log.txt';
        $GLOBALS['LOG'] = array(
            'log_level' => MeLog::LOG_LEVEL_ALL,
            'log_file'  => $log_file,
        );

        MeLog::getInstance()->clean();

        self::Test_notice();
        self::Test_debug();
        self::Test_warning();

        SimpleTest::assert_true(file_exists($log_file));
        $str = file_get_contents($log_file);
        SimpleTest::assert_true(strpos($str, "key1[value1] key2") > 0);
        SimpleTest::assert_true(strpos($str, "DEBUG") > 0);
        $str_wf = file_get_contents($log_file . ".wf");
        SimpleTest::assert_true(!empty($str_wf));
        SimpleTest::assert_true(strpos($str_wf, "logtest") > 0);
        SimpleTest::summary();
    }
}

UnitTest_MeLog::run();