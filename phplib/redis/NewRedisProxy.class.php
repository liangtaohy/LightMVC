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

    private function __construct($host, $port = 6379, $pwd = '')
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_pwd = $pwd;
        $this->_cache = new Redis();
        if ($this->_cache->pconnect($host, $port) === false) {
            MeLog::fatal(sprintf(self::W_LOG, 'connect', SysErrors::E_CACHED_CONNECTION_FAILURE, $this->_cache->getLastError(), $this->_host, $this->_port, 'pwd:' . $pwd));
            exit(0);
            //return SysErrors::E_CACHED_CONNECTION_FAILURE;
        }

        if (!empty($pwd)) {
            if ($this->_cache->auth($this->_pwd) === false) {
                MeLog::fatal(sprintf(self::W_LOG, 'auth', SysErrors::E_CACHED_AUTH_FAILURE, $this->_cache->getLastError(), $this->_host, $this->_port, 'pwd:' . $pwd));
                exit(0);
            }
        }
    }

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
            MeLog::fatal($this->errmsg);
            return SysErrors::E_CACHED_FAILURE;
        }

        if (false === $res) { // data not existed
            $this->errmsg = "redis_error: not exist cmd[get] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
            MeLog::warning($this->errmsg);
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
            MeLog::fatal($this->errmsg);
            return SysErrors::E_CACHED_FAILURE;
        }

        if (false === $res) { // 数据写失败
            $this->errmsg = "redis_error: failed cmd[set] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
            MeLog::warning($this->errmsg);
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
            MeLog::fatal($this->errmsg);
            return false;
        }

        if (false === $res) {
            $this->errmsg = "redis_error: failed or unsupported value type cmd[{$cmd}] " .
                "key[{$key}] step[{$step}] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
            MeLog::warning($this->errmsg);
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

        try {
            $res = $this->_cache->decr($key , $step);
        } catch (RedisException $e) {
            $this->errmsg = "redis_error: redis is down or overload cmd[{decr}] key[{$key}] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            MeLog::fatal($this->errmsg);
            return false;
        }


        if (false === $res) {
            $this->errmsg = "redis_error: failed cmd[decrBy] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}]";
        }

        return $res;
    }

    /**
     * 对key的某个域自增
     * @param $key
     * @param $field
     * @param $step
     * @return bool|float|int
     */
    public function hIncrBy($key, $field, $step)
    {
        if (empty($key) || empty($field) || empty($step)) {
            return false;
        }

        $cmd = '';

        try {
            $type = gettype($step);
            if ($type == 'integer') {
                $cmd = 'hIncrBy';
                $res = $this->_cache->hIncrBy($key, $field, $step);
            } elseif ($type == 'double') {
                $cmd = 'hIncrByFloat';
                $res = $this->_cache->hIncrByFloat($key, $field, $step);
            } else {
                $this->errmsg = "redis_error: cmd[{$cmd}] invalid step type[{$step}]";
                MeLog::warning($this->errmsg);
                return false;
            }
        } catch (RedisException $e) {
            $this->errmsg = "redis_error: redis is down or overload cmd[{$cmd}] key[{$key}] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            MeLog::fatal($this->errmsg);
            return false;
        }

        if (false === $res) {
            $this->errmsg = "redis_error: failed or unsupported value type cmd[{$cmd}] " .
                "key[{$key}] step[{$step}] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
            MeLog::warning($this->errmsg);
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
            MeLog::fatal($this->errmsg);
            return false;
        }

        if($ret===false){
            $this->errmsg = "redis_error: failed cmd[hGet] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
            MeLog::warning($this->errmsg);
        }

        return $ret;
    }

    /**
     * 获取哈希表中key的所有域
     * @param $key
     * @return array|bool
     */
    public function hGetAll($key)
    {
        if(empty($key)){
            return false;
        }

        try{
            $cmd = 'hGetAll';
            $ret = $this->_cache->hGetAll($key);
        }catch (RedisException $e){
            $this->errmsg = "redis_error: redis is down or overload cmd[{$cmd}] key[{$key}] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            MeLog::fatal($this->errmsg);
            return false;
        }

        if($ret===false){
            $this->errmsg = "redis_error: failed cmd[hGetAll] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
            MeLog::warning($this->errmsg);
        }

        return $ret;
    }

    /**
     * hash multi set
     * @param $key
     * @param array $values
     * @return bool
     */
    public function hMSet($key, array $values)
    {
        if(empty($key) || empty($values)) {
            return false;
        }

        try{
            $cmd = 'hMSet';
            $ret = $this->_cache->hMset($key, $values);
        }catch (RedisException $e){
            $this->errmsg = "redis_error: redis is down or overload cmd[{$cmd}] key[{$key}] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            MeLog::fatal($this->errmsg);
            return false;
        }

        if($ret===false){
            $this->errmsg = "redis_error: failed cmd[hMSet] key[$key] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
            MeLog::warning($this->errmsg);
        }

        return $ret;
    }

    /**
     * 获取指定pattern的key列表
     * @param $pattern
     * @return array|bool
     */
    public function keys($pattern = "*")
    {
        if (empty($pattern)) {
            return false;
        }

        try {
            $cmd = 'keys';
            $ret = $this->_cache->keys($pattern);
        } catch (RedisException $e) {
            $this->errmsg = "redis_error: redis is down or overload cmd[{$cmd}] key[{$pattern}] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            MeLog::fatal($this->errmsg);
            return false;
        }

        if($ret===false){
            $this->errmsg = "redis_error: failed cmd[hMSet] key[$pattern] host[" .
                "{$this->_host}] port[{$this->_port}] errmsg[{$this->_cache->getLastError()}]";
            MeLog::warning($this->errmsg);
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
        try {
            $ret = $this->_cache->del($key);
        } catch (RedisException $e) {
            $this->errmsg = "redis_error: redis is down or overload cmd[{del}] key[*] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            MeLog::fatal($this->errmsg);
            return false;
        }

        return $ret;
    }

    /**
     * 队列弹出数据
     *
     * @param $key
     * @return string
     */
    public function rPop($key)
    {
        try {
            $ret = $this->_cache->rPop($key);
        } catch (RedisException $e) {
            $this->errmsg = "redis_error: redis is down or overload cmd[{rPop}] key[*] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            MeLog::fatal($this->errmsg);
            return false;
        }

        return $ret;
    }

    /**
     * 队列添加数据
     *
     * @param $key
     * @param $value1
     * @return mixed
     */
    public function lPush($key, $value1)
    {
        $args = func_get_args();

        try {
            $ret = call_user_func_array(
                array($this->_cache, 'lPush'),
                $args
            );
        } catch (RedisException $e) {
            $this->errmsg = "redis_error: redis is down or overload cmd[{lPush}] key[*] " .
                "host[{$this->_host}] port[{$this->_port}] errno[{$e->getCode()}] errmsg[{$e->getMessage()}]";
            MeLog::fatal($this->errmsg);
            return false;
        }

        return $ret;
    }
}