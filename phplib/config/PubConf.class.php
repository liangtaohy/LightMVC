<?php

/**
 * 全局配置
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/3/1 19:41
 */

define('PHPLIB_PATH', dirname(__FILE__) . '/../');

class PubConf
{
    /**
     * 审核状态值定义
     */
    /** 未提交审核 */
    const DEVST_UNCOMMIT		= 1;
    /** 等待审核中 */
    const DEVST_WAIT_AUDIT		= 2;
    /** 审核不通过 */
    const DEVST_CANNOT_PASS		= 3;
    /** 审核通过 */
    const DEVST_PASS_AUDIT		= 4;
    /** 未提交复审 */
    const DEVST_UNREAUDIT		= 5;
    /** 等待复审中 */
    const DEVST_WAIT_REAUDIT	= 6;
    /** 复审不通过 */
    const DEVST_FAILED_REAUDIT	= 7;
    /** 被管理员关闭下线 */
    const DEVST_BANNED			= 8;
    /** 开发者自己关闭下线 */
    const DEVST_DISABLED		= 9;
    /** 已标记删除 */
    const DEVST_DELETED			= 10;

    public static $devst2str = array(
        self::DEVST_UNCOMMIT => '未提交',
        self::DEVST_WAIT_AUDIT => '待审核',
        self::DEVST_CANNOT_PASS => '审核未通过',
        self::DEVST_PASS_AUDIT => '审核通过',
        self::DEVST_UNREAUDIT => '未提交复审',
        self::DEVST_WAIT_REAUDIT => '待复审',
        self::DEVST_FAILED_REAUDIT => '复审失败',
        self::DEVST_BANNED  => '管理员下线',
        self::DEVST_DISABLED => '开发者下线',
        self::DEVST_DELETED => '删除',
    );

    /**
     * return current idc tag
     * @return string
     */
    public static function currentIDC()
    {
        return defined('CURRENT_TAG') ? CURRENT_TAG : 'default';
    }
}