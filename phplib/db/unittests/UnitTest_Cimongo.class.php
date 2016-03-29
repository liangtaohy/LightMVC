<?php
/**
 * Cimongo 单元测试
 *
 * Created by unkown ide ps.
 * User: deliang
 * DateTime: 3/28/16 5:27 PM
 */

require_once(dirname(__FILE__) . '/../../phplib_headers.php');

class UnitTest_Cimongo implements IBaseTest
{

    /**
     * 初始化
     */
    private static function init()
    {
//        $log_file = dirname(__FILE__) . '/data/test_log.txt';
//        $GLOBALS['LOG'] = array(
//            'log_level' => MeLog::LOG_LEVEL_ALL,
//            'log_file'  => $log_file,
//        );
//
//        MeLog::getInstance()->clean();


    }

    /**
     * getInstance测试用例
     */
    public static function Test_getInstance()
    {
        $mongo = new Cimongo();
        # 测试单条写入数据
        SimpleTest::assert_true($mongo->insert('test', ['test'  => 'test123']));
        # 测试写入id
        $id = (array)$mongo->insert_id();
        SimpleTest::assert_true(strlen($id['$id']) > 0);
        # 测试结果写入是否正确
        $flag = false;
        $data = $mongo->get('test')->result_array();
        foreach ($data as $i => $item) {
            if (isset($item['test'])) {
                $flag = true;
                SimpleTest::assert_true($item['test'] === 'test123');
                break;
            }
        }

        if (!$flag) {
            SimpleTest::assert_true(0);
        }

        $data = $mongo->get_where('test', ['test' => 'test123'], 1)->result_array();
        SimpleTest::assert_true(count($data) > 0);
        $item = current($data);
        SimpleTest::assert_true(isset($item['test']) && $item['test'] === 'test123');

        # 更新数据
        SimpleTest::assert_true($mongo->update('test', ['test'=>'demotest']));
        $data = current($mongo->get('test')->result_array());
        SimpleTest::assert_true($data['test'] === 'demotest');

        # 删除数据测试
        SimpleTest::assert_true($mongo->delete('test'));

        # 测试数据是否为空
        SimpleTest::assert_true(count($mongo->get('test')->result_array()) === 0);
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

UnitTest_Cimongo::run();