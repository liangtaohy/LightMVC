<?php

/**
 * SMS接口
 * User: liangtaohy@163.com
 * Date: 16/4/24
 * Time: PM9:38
 */

interface ISmsInterface
{
    /**
     * 发送测试短信
     * @param array $rev_nums 接收号码列表
     * @param $product 产品线
     * @return mixed
     */
    public function smstest(array $rev_nums, $product);

    /**
     * 发送短信验证码
     * @param array $rev_nums 接收号码列表
     * @param $code 验证码
     * @param $product  产品线
     * @param $vcode 验证码
     * @return mixed
     */
    public function sendvcode($rev_num, $product, $vcode);
}