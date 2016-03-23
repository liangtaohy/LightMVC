<?php

/**
 * Cache class
 * @author liangtao01
 * @date 16/3/10 17:39
 */
class Cache
{
    private $_cache;

    private $_config;

    private $_recv_timeout; // 接收数据超时时间
    private $_send_timeout; // 发送数据超时时间
    private $_tcp_nodelay;  // tcp nodely优化
    private $_sever_failure_limit;  // failover门限
    private $_connect_timeout;  // 连接超时
    private $_retry_timeout;    // 重试超时
    private $_distribution; // 分布式配置(默认为一致性哈希)
    private $_remove_failed_servers; // 移除挂掉的server
    private $_libketama_compatible; // ketama兼容

    private static $_instances;


    /**
     * return memcached instance
     * @return Cache
     */
    public static function getMemcached()
    {
        $tag = CacheConfig::CACHE_MEMCACHED . '_' . PubConf::currentIDC();

        if (array_key_exists($tag, self::$_intances)) {
            return self::$_instances[$tag];
        }

        $instance = new self(CacheConfig::$cache_config[CacheConfig::CACHE_MEMCACHED], $tag);
        if (!emtpy($instance)) {
            self::$_instances[$tag] = $instance;
        }

        return $instance;
    }

    /**
     * @param $config
     * @param $tag
     */
    private function __construct($config, $tag)
    {
        if (!is_array($config[$tag]) || empty($config[$tag])) {
            MeLog::fatal('cache instance[failed] config[' . json_encode($config) . ']');
            exit(0);
        }

        $this->_config = $config[$tag];

        $this->_cache = new Memcached($tag);

        $this->_recv_timeout = isset($config['recv_timeout']) ? $config['recv_timeout'] : 1000;
        $this->_send_timeout = isset($config['send_timeout']) ? $config['send_timeout'] : 1000;
        $this->_tcp_nodelay  = isset($config['tcp_nodelay']) ? $config['tcp_nodelay'] : true;
        $this->_sever_failure_limit  = isset($config['sever_failure_limit']) ? $config['sever_failure_limit'] : 50;
        $this->_connect_timeout  = isset($config['connect_timeout']) ? $config['connect_timeout'] : 500;
        $this->_retry_timeout    = isset($config['retry_timeout']) ? $config['retry_timeout'] : 300;
        $this->_distribution     = isset($config['distribution']) ? $config['distribution'] : Memcached::DISTRIBUTION_CONSISTENT;
        $this->_remove_failed_servers    = isset($config['remove_failed_servers']) ? $config['remove_failed_servers'] : true;
        $this->_libketama_compatible     = isset($config['libketama_compatible']) ? $config['libketama_compatible'] : true;

        if (empty($this->_cache->getServerList())) {
            $this->_cache->setOption(Memcached::OPT_RECV_TIMEOUT, $this->_recv_timeout);
            $this->_cache->setOption(Memcached::OPT_SEND_TIMEOUT, $this->_send_timeout);
            $this->_cache->setOption(Memcached::OPT_TCP_NODELAY, $this->_tcp_nodelay);
            $this->_cache->setOption(Memcached::OPT_SERVER_FAILURE_LIMIT, $this->_sever_failure_limit);
            $this->_cache->setOption(Memcached::OPT_CONNECT_TIMEOUT, $this->_connect_timeout);
            $this->_cache->setOption(Memcached::OPT_RETRY_TIMEOUT, $this->_retry_timeout);
            $this->_cache->setOption(Memcached::OPT_DISTRIBUTION, $this->_distribution);
            $this->_cache->setOption(Memcached::OPT_REMOVE_FAILED_SERVERS, $this->_remove_failed_servers);
            $this->_cache->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, $this->_libketama_compatible);
            $this->_cache->addServers($config[$tag]);
        }
    }

    /**
     * add a key-value into cached
     * @param $key
     * @param $value
     * @param $expiration
     * @return bool|int
     */
    public function add($key , $value, $expiration = 0)
    {
        if ($this->_cache->add($key, $value, $expiration)) {
            return SysErrors::E_CACHED_SUCCESS;
        }

        if ($this->_cache->getResultCode() === Memcached::RES_NOTSTORED) {
            return SysErrors::E_CACHED_KEY_EXISTED;
        }

        MeLog::warning(sprintf('cache method[add] result[failed] errno[%d] errmsg[%s] params[key:%s, value:%s,expire:%d]', $this->_cache->getResultCode(), $this->_cache->getResultMessage(), $key, json_encode($value), intval($expiration)));
        return false;
    }

    /**
     * delete an item
     * @param $key
     * @param int $time
     * @return bool|int
     */
    public function delete($key, $time = 0)
    {
        if ($this->_cache->delete($key, $time)) {
            return SysErrors::E_CACHED_SUCCESS;
        }

        if ($this->_cache->getResultCode() === Memcached::RES_NOTFOUND) {
            return SysErrors::E_CACHED_NOTFOUND;
        }

        MeLog::warning(sprintf('cache method[delete] result[failed] errno[%d] errmsg[%s] params[key:%s,time:%d]', $this->_cache->getResultCode(), $this->_cache->getResultMessage(), $key, $time));
        return false;
    }

    /**
     * store multiple items
     * @param array $items
     * @param int $expiration
     * @return bool|int
     */
    public function setMulti (array $items, $expiration = 0)
    {
        if (empty($items)) {
            MeLog::warning(sprintf('cache method[setMulti] result[failed] errno[%d] errmsg[%s] params[items:%s,time:%d]', $this->_cache->getResultCode(), $this->_cache->getResultMessage(), json_encode($items), $expiration));
            return false;
        }

        $expiration = intval($expiration);

        if ($this->_cache->setMulti($items, $expiration)) {
            return SysErrors::E_CACHED_SUCCESS;
        }

        MeLog::warning(sprintf('cache method[setMulti] result[failed] errno[%d] errmsg[%s] params[items:%s,time:%d]', $this->_cache->getResultCode(), $this->_cache->getResultMessage(), json_encode($items), $expiration));
        return false;
    }

    /**
     * update value by key
     * @param $key
     * @param $value
     * @param int $expiration
     * @return bool|int
     */
    public function update($key, $value, $expiration = 0)
    {
        if ($this->_cache->replace($key, $value, $expiration)) {
            return SysErrors::E_CACHED_SUCCESS;
        }

        MeLog::warning(sprintf('cache method[update] result[failed] errno[%d] errmsg[%s] params[key:%s, value:%s,expire:%d]', $this->_cache->getResultCode(), $this->_cache->getResultMessage(), $key, json_encode($value), intval($expiration)));
        return false;
    }

    /**
     * increment by 1
     * @param $key
     * @return int
     */
    public function increment($key)
    {
        if ($this->_cache->increment($key) === false) {
            MeLog::warning(sprintf('cache method[increment] result[failed] errno[%d] errmsg[%s] params[key:%s]', $this->_cache->getResultCode(), $this->_cache->getResultMessage()));
            return SysErrors::E_CACHED_SOME_ERRORS;
        }

        return SysErrors::E_CACHED_SUCCESS;
    }

    /**
     * increment by 1
     * @param $key
     * @return int
     */
    public function decrement($key)
    {
        if ($this->_cache->increment($key) === false) {
            MeLog::warning(sprintf('cache method[decrement] result[failed] errno[%d] errmsg[%s] params[key:%s]', $this->_cache->getResultCode(), $this->_cache->getResultMessage()));
            return SysErrors::E_CACHED_SOME_ERRORS;
        }

        return SysErrors::E_CACHED_SUCCESS;
    }
}