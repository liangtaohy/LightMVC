<?php

/**
 * 测试接口定义
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 14:10
 */
interface IBaseTest
{
    /**
     * run接口，测试框架调用该方法执行测试用例
     * @return mixed
     */
    public static function run();
}