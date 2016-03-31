<?php

/**
 * 常用工具类
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 10:23
 */
class Utils
{
    /**
     * mongo object id
     * @return string
     */
    public static function getObjectId()
    {
        return (string)new MongoId();
    }
    /**
     * get micro time
     * @return int
     */
    public static function microTime() {
        $temp = explode(" ", microtime());
        return intval(bcadd($temp[0], $temp[1], 6) * 1000);
    }

    /**
     * try to get a real client ip
     * @return string
     */
    public static function getClientIP()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) &&
            $_SERVER['HTTP_X_FORWARDED_FOR'] != '127.0.0.1') {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = $ips[0];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '127.0.0.1';
        }

        $pos = strpos($ip, ',');
        if ($pos > 0) {
            $ip = substr($ip, 0, $pos);
        }

        return trim($ip);
    }

    /**
     * @param $obj
     */
    public static function obj2String($obj)
    {
        if (is_string($obj)) {
            return $obj;
        }

        if (is_int($obj)) {
            return '' . $obj;
        }

        if (is_float($obj)) {
            return '' . $obj;
        }

        if (is_array($obj)) {
            foreach($obj as $key => $value) {

            }
        }
    }
}