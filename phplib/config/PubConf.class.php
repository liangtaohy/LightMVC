<?php

/**
 * 全局配置
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/3/1 19:41
 */

define('SMARTY_PATH', HTDOCS_PATH .'/phpsrc/extlib/smarty/libs');
define('PHPLIB_PATH', dirname(__FILE__) . '/../');

class PubConf
{
    /**
     * return current idc tag
     * @return string
     */
    public static function currentIDC()
    {
        return defined('CURRENT_TAG') ? CURRENT_TAG : 'default';
    }
}