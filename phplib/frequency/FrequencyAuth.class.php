<?php

/**
 * Api频率认证管理
 * User: liangtaohy@163.com
 * Date: 16/5/18
 * Time: PM12:46
 */
class FrequencyAuth
{
    const CACHE_KEY_PRODUCT_FREQ_CONF = 'freq_pro_conf_%s';
    const CACHE_KEY_PRODUCT_FREQ_AUTH = 'freq_pro_auth_%s_%s_%s_%s';

    const FIELD_SECOND = 'second';
    const FIELD_HOUR = 'hour';
    const FIELD_DAY = 'day';
    const FIELD_MONTH = 'month';

    /**
     * 构建频次auth key
     * @param $product
     * @param $id
     * @param $api
     * @return string
     */
    protected static function buildCacheKeyProFreqAuth($product, $id, $api, $field)
    {
        return sprintf(self::CACHE_KEY_PRODUCT_FREQ_AUTH, $product, $id, $api, $field);
    }

    /**
     * 构建频次配置
     * @param $product
     * @return bool|string
     */
    public static function buildCacheKeyProFreqConf($product)
    {
        if (empty($product)) {
            return false;
        }

        return sprintf(self::CACHE_KEY_PRODUCT_FREQ_CONF, $product);
    }

    /**
     * 设置指定产品线的指定api的频次配置
     * @param $product
     * @param $api
     * @param $freq_conf
     * @return bool
     */
    public static function setFreqAuthConfigToCache($product, $api, $freq_conf)
    {
        if (empty($api) || empty($product)) {
            return false;
        }

        $api = trim($api);
        $product = trim($product);
        $api = str_replace("/", "_", $api);

        $key = self::buildCacheKeyProFreqConf($product);

        if ($key === false) {
            return false;
        }

        if (NewRedisProxy::getInstance()->hSet($key, $api, serialize($freq_conf)) === false) {
            MeLog::warning(sprintf('pro[%s] api[%s] freq_conf[%s] result[failed]', $product, $api, serialize($freq_conf)));
            return false;
        }

        return true;
    }

    /**
     * @param $product
     * @param $api
     * @return bool|string
     */
    public static function getFreqAuthConfigFromCache($product, $api)
    {
        if (empty($api) || empty($product)) {
            return array();
        }
        $api = trim($api);
        $product = trim($product);
        $api = str_replace("/", "_", $api);

        $key = self::buildCacheKeyProFreqConf($product);

        if ($key === false) {
             return array();
        }

        $v = NewRedisProxy::getInstance()->hGet($key, $api);
        if (empty($v)) {
            return array();
        }

        return serialize($v);
    }

    /**
     * @param $product
     * @param $api
     * @param $id
     * @return bool
     */
    public static function freqAuth($product, $api, $id)
    {
        $conf = self::getFreqAuthConfigFromCache($product, $api);
        if ($conf[self::FIELD_SECOND] > 0) {
            if (self::FreqDiAuth(self::buildCacheKeyProFreqAuth($product, $id, $api, self::FIELD_SECOND), $conf[self::FIELD_SECOND], 60) === false) {
                return false;
            }
        }

        if ($conf[self::FIELD_HOUR] > 0) {
            if (self::FreqDiAuth(self::buildCacheKeyProFreqAuth($product, $id, $api, self::FIELD_HOUR), $conf[self::FIELD_HOUR], 3600) === false) {
                return false;
            }
        }

        if ($conf[self::FIELD_DAY] > 0) {
            if (self::FreqDiAuth(self::buildCacheKeyProFreqAuth($product, $id, $api, self::FIELD_DAY), $conf[self::FIELD_DAY], 86400) === false) {
                return false;
            }
        }

        if ($conf[self::FIELD_MONTH] > 0) {
            if (self::FreqDiAuth(self::buildCacheKeyProFreqAuth($product, $id, $api, self::FIELD_MONTH), $conf[self::FIELD_MONTH], 2592000) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 基于不同维度的频次校验
     * @param $key
     * @param $maxcount
     * @param $ttl
     * @return bool
     */
    protected static function FreqDiAuth($key, $maxcount, $ttl)
    {
        $cache = NewRedisProxy::getInstance();

        $v = $cache->get($key);

        if ($v === false) {
            $cache->set($key, $maxcount);
            $cache->expire($key, $ttl);
            $v = $maxcount;
        }

        if ($v <= 0) {
            return false;
        }

        $cache->decrBy($key, -1);
        return true;
    }
}