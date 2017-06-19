<?php

/**
 * 测试用例：有效性检查工具类
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 16:01
 */
require_once(dirname(__FILE__) . "/../../phplib_headers.php");

class UnitTest_Validator implements IBaseTest
{
    private static function Test_isValidUrl()
    {
        $url1 = "test";
        $url2 = "http:/test.php";
        $url3 = "http://";
        $url4 = "https://";
        $url5 = "http://www.baidu.com";
        $url6 = "http://www.baidu.com/";
        $url7 = "http://www.baidu.com/sdsd/?s=g";

        SimpleTest::assert_value(Validator::isValidUrl($url1), false);
        SimpleTest::assert_value(Validator::isValidUrl($url2), false);
        SimpleTest::assert_value(Validator::isValidUrl($url3), false);
        SimpleTest::assert_value(Validator::isValidUrl($url4), false);
        SimpleTest::assert_value(Validator::isValidUrl($url5), true);
        SimpleTest::assert_value(Validator::isValidUrl($url6), true);
        SimpleTest::assert_value(Validator::isValidUrl($url7), true);
    }

    public static function run()
    {
        self::Test_isValidUrl();
        SimpleTest::summary();
    }
}

UnitTest_Validator::run();