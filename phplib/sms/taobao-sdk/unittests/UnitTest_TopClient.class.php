<?php

/**
 * 阿里大鱼-sms sdk TopClient测试类
 * User: liangtaohy@163.com
 * Date: 16/4/25
 * Time: PM8:10
 */

require_once(dirname(__FILE__) . "/../../../phplib_headers.php");
class UnitTest_TopClient implements IBaseTest
{
    const APPKEY = '23354053';
    const APPSECRET = '08adca50c0f40d3fff61b2827fac35fe';

    public static function run()
    {
        $c = new TopClient;
        $c->appkey = self::APPKEY;
        $c->secretKey = self::APPSECRET;

        $req = new AlibabaAliqinFcSmsNumSendRequest;

        $req->setExtend("123456");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("注册验证");
        $req->setSmsParam("{\"customer\":\"瞄瞄\",\"code\":\"1234\",\"product\":\"系统测试\"}");
        $req->setRecNum("13488715167");
        $req->setSmsTemplateCode("SMS_8240043");
        $resp = $c->execute($req);

        var_dump($resp);
    }
}

UnitTest_TopClient::run();
