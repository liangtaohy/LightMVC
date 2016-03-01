<?php

/**
 * DBProxyConfig
 * 数据库公共配置类
 * @example
 * DBProxyConfig::$arrDBMap = array(
 *      'test_db'   => array(
 *          'user'          => 'test',
 *          'password'      => 'testpwd',
 *          'charset'       => 'utf8',
 *          'connect_timeout'   => 1,
 *          'cluster-tag1'  => array(
 *              array(
 *                  'host'  => '10.10.70.90',
 *                  'port'  => 1234,
 *              ),
 *          ),
 *      ),
 * );
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/3/1 19:11
 */
class DBProxyConfig
{

    public static $arrDBMap = array();
}