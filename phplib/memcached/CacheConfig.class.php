<?php

/**
 * Cache config
 * @author liangtaohy@163.com
 * @date 16/3/10 20:53
 */
class CacheConfig
{
    const CACHE_MEMCACHED   = 'memcached';
    const CACHE_REDIS       = 'redis';

    public static $cache_config = array(
        self::CACHE_MEMCACHED   => array(
            /**
             * 'tag-1'    => array(
             *      // ip, port, weight
             *      array('11.11.11.11', 123, 11),
             * )
             */
        ),
        self::CACHE_REDIS       => array(

        ),
    );
}