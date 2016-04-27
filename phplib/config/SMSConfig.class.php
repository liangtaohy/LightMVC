<?php

/**
 * 短信配置文件
 * User: liangtaohy@163.com
 * Date: 16/4/27
 * Time: AM9:41
 */
class SMSConfig
{
    const CHANNEL_ALIDAYU = 'alidayu'; // 阿里大鱼通道

    public static $conf = array(
        self::CHANNEL_ALIDAYU   => array(
            'appkey'    => 'test',
            'appsecret' => 'test_secret',
        ),
    );
}