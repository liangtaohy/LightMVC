<?php

/**
 * 短信工厂类
 * User: liangtaohy@163.com
 * Date: 16/4/26
 * Time: AM12:13
 */

require_once (dirname(__FILE__) . '/../config/SMSConfig.class.php');
require_once (dirname(__FILE__) . '/ALiDaYuSDK.class.php');
class SMSFactory
{
    /**
     * 根据渠道号,创建短信服务实例
     * @param $channel_num
     * @return ALiDaYuSDK|null
     */
    public static function create($channel_num = 'alidayu')
    {
        switch ($channel_num) {
            case SMSConfig::CHANNEL_ALIDAYU:
                $c = SMSConfig::$conf[SMSConfig::CHANNEL_ALIDAYU];
                return ALiDaYuSDK::getInstance($c['appkey'], $c['appsecret']);
            default:
                $c = SMSConfig::$conf[SMSConfig::CHANNEL_ALIDAYU];
                return ALiDaYuSDK::getInstance(SMSConfig::$conf['appkey'], SMSConfig::$conf['appsecret']);
        }
    }
}