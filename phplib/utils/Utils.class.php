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

    /**
     * 生成加密后的token,加密方法为md5,不可反解
     * @param $src 原串
     * @param $key 加密使用的key
     * @param $srcex 附加信息字符串,如时间戳
     * @param $dst 生成的token存放在dst中
     * @return bool
     */
    public static function getMd5Token($src, $key, $srcex, &$dst) {
        //check to be encrypt src type is string
        if ( !(is_string($src)&&is_string($key)) ){
            $dst = sprintf("the fisrt two args should be string type:%d,%d!",
                intval(is_string($src)),intval(is_string($key)));
            return false;
        }
        //check src string and key
        if ( '\0'==$src[0] || '\0'==$key[0]){
            $dst = "first or second arg len is 0 ";
            return false;
        }
        $tmp = $src.$key;
        if (NULL != $srcex){
            if (!is_string($srcex)){
                $dst = "third arg should be string type";
                return false;
            }
            $tmp = $tmp.$srcex;
        }
        $dst = md5($tmp);
        return true;
    }

    /**
     * 校验toekn是否合法
     * @param $token
     * @param $src
     * @param $key
     * @param $srcex
     * @return bool|string
     */
    public static function checkMd5Token($token, $src, $key, $srcex) {
        //check to be encrypt src type is string
        if ( !(is_string($src)&&is_string($key)&&is_string($token)) ){
            $error = sprintf("the fisrt three args should be string type:%d,%d,%d!",
                intval(is_string($token)),intval(is_string($src)),intval(is_string($key)));
            return $error;
        }

        $tmp = $src.$key;
        if (NULL != $srcex){
            if (!is_string($srcex)){
                $error= "fouth arg should be string type!";
                return $error;
            }
            $tmp = $tmp.$srcex;
        }

        $dst = md5($tmp);

        if ( 0==strncmp($token, $dst, strlen($dst)) && strlen($dst)==strlen($token) ){
            return true;
        }

        $error = sprintf("md5stoken cmp error:right md5stoken is %s!", $dst);
        return $error;
    }

    /**
     * int to ip str
     * @param $num
     * @return string
     */
    public static function int2ip($num) {
        $tmp = (double)$num;
        return sprintf('%u.%u.%u.%u', $tmp & 0xFF, (($tmp >> 8) & 0xFF),
            (($tmp >> 16) & 0xFF), (($tmp >> 24) & 0xFF));
    }

    /**
     * ip str to int
     * @param $ip
     * @return int
     */
    public static function ip2int($ip) {
        $n = ip2long($ip);

        /** convert to network order */
        $n = (($n & 0xFF) << 24)
            | ((($n >> 8) & 0xFF) << 16)
            | ((($n >> 16) & 0xFF) << 8)
            | (($n >> 24) & 0xFF);

        return $n < (1 << 31) ? $n : $n - (1 << 32);
    }

    /**
     * Redirect to the specified page
     *
     * @param string $url	the specified page's url
     * @param bool $top_redirect	Whether need to redirect the top page frame
     **/
    public static function redirect($url, $top_redirect = true)
    {
        if ($top_redirect) {
            // make sure baidu.com url's load in the full frame so that we don't
            // get a frame within a frame.
            echo '<script type="text/javascript"> top.location.href = "' . $url . '";</script>';
        } else {
            header('Location: ' . $url);
        }
        exit();
    }

    /**
     * Generate a unique random key using the methodology
     * recommend in php.net/uniqid
     *
     * @return string a unique random hex key
     **/
    public static function generate_rand_key()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * Check whether it is a valid ip list, each ip is delemited by ','
     *
     * @param string $iplist Ip list string to be checked
     * @return bool
     **/
    public static function is_valid_iplist($iplist)
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

    /**
     * Generate a signature.  Should be copied into the client
     * library and also used on the server to validate signatures.
     *
     * @param array	$params	params to be signatured
     * @param string $secret	secret key used in signature
     * @param string $namespace	prefix of the param name, all params whose name are equal
     * with $namespace will not be put in the signature.
     * @return string md5 signature
     **/
    public static function generate_sig($params, $secret, $namespace = 'sig')
    {
        $str = '';
        ksort($params);
        foreach ($params as $k => $v) {
            if ($k != $namespace) {
                $str .= "$k=$v";
            }
        }
        $str .= $secret;
        return md5($str);
    }

    /**
     * 使用rc4算法加密
     * @param string $plain 明文
     * @param string $key   秘钥
     * @param int $expiry   失效时间
     * @return string
     */
    public static function rc4_encode($plain,$key='',$expiry=0){
        return self::rc4($plain,'ENCODE',$key,$expiry);
    }

    /**
     * 使用rc4算法解密
     * @param $cipher $cipher
     * @param string  $key
     * @param int     $expiry
     * @return string
     */
    public static function rc4_decode($cipher,$key='',$expiry=0){
        return self::rc4($cipher,'DECODE',$key,$expiry);
    }

    /**
     * rc4加密、解密
     * @param string $string  输入字符串
     * @param string $operation 操作ENCODE(加密) or DECODE(解密)
     * @param string $key 秘钥
     * @param int $expiry 有效期，时间单位为秒
     * @return string 加密（解密）后的字符串
     *
     * @example:  rc4('a'); 用默认的key对字符 a 进行加密
     * @example:  rc4('a', 'DECODE'); 用默认的key对a进行解密
     * @example:  rc4('a', 'ENCODE', 'abc'); 用指定的 key 'abc'对字符a进行加密
     * @example:  rc4('a', 'ENCODE', 'abc', 15); 用指定的 key 'abc'对字符a进行加密, 设定有效期 15 秒
     *
     */
    private static function rc4($string, $operation = 'ENCODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;

        $key = md5($key != '' ? $key : 'BaiduRc4Key');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $box[$i] = $box[$i] ^ $box[$j];
            $box[$j] = $box[$i] ^ $box[$j];
            $box[$i] = $box[$i] ^ $box[$j];
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $box[$a] = $box[$a] ^ $box[$j];
            $box[$j] = $box[$a] ^ $box[$j];
            $box[$a] = $box[$a] ^ $box[$j];
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0)
                && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)
            ) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}