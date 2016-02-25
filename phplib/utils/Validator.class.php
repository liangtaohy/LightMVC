<?php

/**
 * 校验工具类
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 15:56
 */
class Validator
{
    /**
     * 检查url的有效性
     * 支持schema：http, https
     *
     * @param string $url	URL to be checked
     * @return bool
     **/
    public static function isValidUrl($url)
    {
        if (strlen($url) > 0) {
            if (!preg_match('/^http?:\/\/[^\s&<>#;"\'\?]+(|#[^\s<>"\']*|\?[^\s<>"\']*)$/i',
                $url, $match)) {
                return false;
            }

            if (!preg_match('/^https?:\/\/[^\s&<>#;"\'\?]+(|#[^\s<>"\']*|\?[^\s<>"\']*)$/i',
                $url, $match)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 检查email地址有效性
     *
     * @param string $email Email to be checked
     * @return bool
     **/
    public static function isValidEmail($email)
    {
        if (strlen($email) > 0) {
            if (!preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,3})$/i',
                $email, $match)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 检查电话有效性
     *
     * @param string $phone	Phone number to be checked
     * @return bool
     **/
    public static function isValidPhone($phone)
    {
        if (strlen($phone) > 0) {
            if (!preg_match('/^([0-9]{11}|[0-9]{3,4}-[0-9]{7,8}(-[0-9]{2,5})?)$/i',
                $phone, $match)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 检查一系列IP地址的有效性，分隔符为','
     *
     * @param string $iplist Ip list string to be checked
     * @return bool
     **/
    public static function isValidIplist($iplist)
    {
        $iplist = trim($iplist);
        if (strlen($iplist) > 0) {
            if (!preg_match('/^(([0-9]{1,3}\.){3}[0-9]{1,3})(,(\s)*([0-9]{1,3}\.){3}[0-9]{1,3})*$/i',
                $iplist, $match)) {
                return false;
            }
        }
        return true;
    }
}