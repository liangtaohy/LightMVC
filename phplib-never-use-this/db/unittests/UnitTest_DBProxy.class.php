<?php

/**
 * DBProxy单元测试
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/3/2 11:39
 */
define('CURRENT_TAG', 'nanjing');
define('QUERY_ENABLE', true);

require_once(dirname(__FILE__) . '/../../phplib_headers.php');

class UnitTest_DBProxy implements IBaseTest
{
    const DB_INNOVATON_BASE = 'innovation_base';

    const TABLE_TEST_SQL = 'test.sql';

    /**
     * 初始化
     */
    private static function init()
    {
        $log_file = dirname(__FILE__) . '/data/test_log.txt';
        $GLOBALS['LOG'] = array(
            'log_level' => MeLog::LOG_LEVEL_ALL,
            'log_file'  => $log_file,
        );

        MeLog::getInstance()->clean();

        DBProxyConfig::$arrDBMap = array(
            'innovation_base'   => array(
                'user'          => 'root',
                'password'      => 'MhxzKhl',
                'charset'       => 'utf8',
                'connect_timeout'   => 0,
                'nanjing'  => array( // 南京机房
                    array(
                        'host'  => '10.94.162.54',
                        'port'  => 8306,
                    ),
                ),
            ),
        );
    }

    /**
     * getInstance测试用例
     */
    public static function Test_getInstance()
    {
        $dbproxy = DBProxy::getInstance(self::DB_INNOVATON_BASE);
        SimpleTest::assert_true($dbproxy instanceof $dbproxy);
        SimpleTest::assert_true($dbproxy->host() == '10.94.162.54');
        SimpleTest::assert_true($dbproxy->port() == 8306);
        SimpleTest::assert_true($dbproxy->dbname() == self::DB_INNOVATON_BASE);

        // create table
        $sql = file_get_contents(self::TABLE_TEST_SQL);
        $res = $dbproxy->query($sql);
        SimpleTest::assert_true($res);
        $curtime = time();
        $values = array(
            'name'  => 'test1',
            'age'   => 10,
            'ctime' => $curtime,
        );
        SimpleTest::assert_true($dbproxy->insert($values, 'test'));
        SimpleTest::assert_true(count($dbproxy->select("select * from test")) > 0);
        $res = $dbproxy->query('drop table test');
        SimpleTest::assert_true($res);
    }

    /**
     * 执行入口
     */
    public static function run()
    {
        self::init();
        self::Test_getInstance();
        SimpleTest::summary();
    }
}

UnitTest_DBProxy::run();