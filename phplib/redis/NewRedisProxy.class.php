<?php

/**
 * Redis代理类
 * User: liangtaohy@163.com
 * Date: 16/4/26
 * Time: PM12:12
 */

require_once (dirname(__FILE__) . "/../memcached/CacheConfig.class.php");

class NewRedisProxy
{
    const W_LOG = "NewRedisProxy method[%s] errno[%d] errmsg[%s] host[%s] port[%d] params[%s]";
    private $_cache = null;
    private $_host = '';
    private $_port = '';
    private $_pwd = '';

    public $errmsg = null;


    private static $inst = array();

    public static function getInstance()
    {
        $config = CacheConfig::$cache_config[CacheConfig::CACHE_REDIS];
        $host = $config['host'];
        $port = $config['port'];
        $pwd = $config['pwd'];

        $tag = $host . $port;

        if (isset(self::$inst[$tag])) {
            return self::$inst[$tag];
        }

        $redis = new self($host, $port, $pwd);

        self::$inst[$tag] = $redis;

        return $redis;
    }

    public function __construct()
    {
        var_dump(11111111);
    }

    public function ddd()
    {
        var_dump(11111111111111);
    }

//    private function __construct($host, $port = 6379, $pwd = '')
//    {
//        $this->_host = $host;
//        $this->_port = $port;
//        $this->_pwd = $pwd;
//        $this->_cache = new Redis();
//        if ($this->_cache->pconnect($host, $port) === false) {
//            MeLog::fatal(sprintf(self::W_LOG, 'connect', SysErrors::E_CACHED_CONNECTION_FAILURE, $this->_cache->getLastError(), $this->_host, $this->_port, 'pwd:' . $pwd));
//            exit(0);
//            //return SysErrors::E_CACHED_CONNECTION_FAILURE;
//        }
//
//        if (!empty($pwd)) {
//            if ($this->_cache->auth($this->_pwd) === false) {
//                MeLog::fatal(sprintf(self::W_LOG, 'auth', SysErrors::E_CACHED_AUTH_FAILURE, $this->_cache->getLastError(), $this->_host, $this->_port, 'pwd:' . $pwd));
//                exit(0);
//            }
//        }
//    }

    /**
     * 在当前机房尝试获取一个key对应的value
     * 注意：如果不使用序列化，非字符串的类型会自动转成string
     *
     * @param string $key 变量的key
     * @param bool   $need_unserialize 指定是否需要对Redis中存储的数据进行反序列化，默认不会进行反序列化
     *
     * @return bool
     */
    public function get($key)
    {
        if(empty($key)){
            return SysErrors::E_CACHED_INVALID_ARGUMENTS;
        }

        try {
            $res = $this->_cache->get($key);
        } catch (RedisException $e) {
            $this->errmsg = "redis_error: redis is down or overload cmd[get] key[$key] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            return SysErrors::E_CACHED_FAILURE;
        }

        if (false === $res) { // data not existed
            $this->errmsg = "redis_error: not exist cmd[get] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
        }

        return $res;

    }

    /**
     * 设置 kv
     * @param $key
     * @param $val
     * @param string $opt
     * @param int $ttl
     * @return bool
     */
    public function set($key, $val, $opt = '', $ttl = 0)
    {
        try {
            if ($ttl&&empty($opt)) {
                $res = $this->_cache->set($key, $val, $ttl);
            }elseif($ttl&&!empty($opt)){
                $res = $this->_cache->set($key, $val, array($opt, 'EX' => $ttl));
            }else {
                $res = $this->_cache->set($key, $val);
            }
        } catch (RedisException $e) {
            $this->errmsg = "redis_error: redis is down or overload cmd[set] key[$key] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            return SysErrors::E_CACHED_FAILURE;
        }

        if (false === $res) { // 数据写失败
            $this->errmsg = "redis_error: failed cmd[set] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
            return SysErrors::E_CACHED_NOTSTORED;
        }

        return $res;
    }

    /**
     * 自增
     * @param $key
     * @param int $step
     * @return bool|float|int
     */
    public function incrBy($key, $step = 1)
    {
        if(empty($key)){
            return false;
        }

        $cmd = '';

        try {
            $type = gettype($step);
            if ($type == 'integer') {
                $cmd = 'incrBy';
                $res = $this->_cache->incrBy($key, $step);
            } elseif ($type == 'double') {
                $cmd = 'incrByFloat';
                $res = $this->_cache->incrByFloat($key, $step);
            } else {
                $this->errmsg = "redis_error: cmd[incrBy] invalid step type[{$step}]";
                return false;
            }
        } catch (RedisException $e) {
            $this->errmsg = "redis_error: redis is down or overload cmd[{$cmd}] key[{$key}] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            return false;
        }

        if (false === $res) {
            $this->errmsg = "redis_error: failed or unsupported value type cmd[{$cmd}] " .
                "key[{$key}] step[{$step}] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
        }
        return $res;
    }

    /**
     * 在当前机房，对一个key进行自减操作，如果key之前不存在，则初始化值为0，然后在进行decr操作
     *
     * @param string $key      变量的key
     * @param int|float $step  指针的步长
     *
     * @return bool
     */
    public function decrBy($key , $step)
    {
        if(empty($key)){
            return false;
        }

        $res = $this->_cache->decr($key , $step);

        if (false === $res) {
            $this->errmsg = "redis_error: failed cmd[decrBy] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}]";
        }

        return $res;
    }

    /**
     * 在当前机房，为hash插入某个key=》v
     *
     * @param string $key hash的key
     * @param string $field
     * @param string $val
     *
     * @return bool
     */
    public function hSet($key, $field, $val){
        if(empty($key) || empty($field) || empty($val)){
            return false;
        }

        $res = $this->_cache->hSet($key, $field, $val);

        if (false === $res) {
            $this->errmsg = "redis_error: failed cmd[hSet] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
        }
        return $res;
    }

    /**
     * 获得哈希表中key给定域的field
     *
     * @param string $key
     * @param string $field
     * @return bool|string
     */
    public function hGet($key,$field){
        if(empty($key)||empty($field)){
            return false;
        }

        try{
            $cmd = 'hGet';
            $ret = $this->_cache->hGet($key,$field);
        }catch (RedisException $e){
            $this->errmsg = "redis_error: redis is down or overload cmd[{$cmd}] key[{$key}] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            return false;
        }

        if($ret===false){
            $this->errmsg = "redis_error: failed cmd[hGet] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
        }

        return $ret;
    }

    /**
     * 删除当前数据库中所有Key
     * @return bool
     */
    public function flushDB()
    {
        try {
            $ret = $this->_cache->flushDB();
        } catch (RedisException $e) {
            $this->errmsg = "redis_error: redis is down or overload cmd[{flushDB}] key[*] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            return false;
        }

        return $ret;
    }

    /**
     * 设置过期时间
     * @param $key
     * @param $ttl
     */
    public function expire($key, $ttl)
    {
        $this->_cache->expire($key, $ttl);
    }

    /**
     * 删除指定的key
     * @param $key
     * @return int
     */
    public function del($key)
    {
        return $this->_cache->del($key);
    }
}