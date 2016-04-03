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

    /**
     * 计算身份证校验码，根据国家标准GB 11643-1999
     * 要求18位身份证信息
     * @param $idcard_base
     * @return bool|mixed
     */
    public static function idcard_verify_number($idcard_base){
        if (strlen($idcard_base) != 17){ return false; }
        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        // 校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++){
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }

        $mod = strtoupper($checksum % 11);
        $verify_number = $verify_number_list[$mod];

        return $verify_number;
    }

    // 将15位身份证升级到18位
    public static function idcard_15to18($idcard){
        if (strlen($idcard) != 15){
            return false;
        }else{
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){
                $idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9);
            }else{
                $idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9);
            }
        }

        $idcard = $idcard . Utils::idcard_verify_number($idcard);

        return $idcard;
    }
}