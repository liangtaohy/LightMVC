<?php

/**
 * 频次单测
 * User: liangtaohy@163.com
 * Date: 16/5/18
 * Time: PM3:35
 */
require_once(dirname(__FILE__) . '/../../phplib_headers.php');

define('DEBUG', true);
define('PHPLIB_PATH', dirname(__FILE__) . '/../../');

class UnitTest_FrequencyAuth implements IBaseTest
{
    /**
     * 测试用例执行入口
     */
    public static function run()
    {
        SimpleTest::assert_true(FrequencyAuth::freqAuth('order', '_order_api_v1_add', '123') === true);
        SimpleTest::assert_true(FrequencyAuth::freqAuth('order', '_order_api_v1_add', '123') === true);
        SimpleTest::assert_true(FrequencyAuth::freqAuth('order', '_order_api_v1_add', '123') === true);
        SimpleTest::assert_true(FrequencyAuth::freqAuth('order', '_order_api_v1_add', '123') === false);
        SimpleTest::assert_true(FrequencyAuth::freqAuth('order', '_order_api_v1_add', '123') === false);
        sleep(62);
        SimpleTest::assert_true(FrequencyAuth::freqAuth('order', '_order_api_v1_add', '123') === true);
        SimpleTest::assert_true(FrequencyAuth::freqAuth('order', '_order_api_v1_add', '123') === true);
        SimpleTest::summary();
    }
}