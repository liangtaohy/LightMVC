<?php

/**
 * PubClassManager单元测试
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 19:42
 */
require_once(dirname(__FILE__) . '/../../phplib_headers.php');

define('DEBUG', true);
define('PHPLIB_PATH', dirname(__FILE__) . '/../../');

class UnitTest_PubClassManager implements IBaseTest
{
    /**
     * 测试registerClass
     */
    private static function Test_registerClass()
    {
        PubClassManager::getInstance()->registerClass('TestClassName', '/TestClassName.class.php');
        $clzmap = PubClassManager::getInstance()->getRegisterClassMap();
        SimpleTest::assert_true(is_array($clzmap));
        SimpleTest::assert_true(count($clzmap) >= 1);
        SimpleTest::assert_true($clzmap['TestClassName'] === '/TestClassName.class.php');
        SimpleTest::assert_true(isset($clzmap['Tmp']) === false);
    }

    /**
     * 测试批量注册自定义类
     */
    private static function Test_registerClasses()
    {
        $arr = array(
            'TestClassName'     => '/TestClassName.class.php',
        );

        SimpleTest::assert_false(PubClassManager::getInstance()->registerClasses(NULL));
        SimpleTest::assert_false(PubClassManager::getInstance()->registerClasses(array()));
        SimpleTest::assert_false(PubClassManager::getInstance()->registerClasses("hello"));
        SimpleTest::assert_false(PubClassManager::getInstance()->registerClasses(1));

        SimpleTest::assert_true(PubClassManager::getInstance()->registerClasses($arr));

        $clzmap = PubClassManager::getInstance()->getRegisterClassMap();
        SimpleTest::assert_true($clzmap['TestClassName'] === '/TestClassName.class.php');
        SimpleTest::assert_true(isset($clzmap['Tmp']) === false);
    }

    /**
     * 测试用例执行入口
     */
    public static function run()
    {
        self::Test_registerClass();
        self::Test_registerClasses();
        SimpleTest::summary();
    }
}

UnitTest_PubClassManager::run();