<?php

/**
 * 系统错误码
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/25 15:48
 */
class SysErrors
{
    const E_SUCCESS             = 0;
    const E_CLASS_NOT_FOUND     = 1;
    const E_REQUEST_INVALID     = 2;
    const E_CLASS_NOT_ACTION    = 3;
    // DB 2xx
    const E_DB_CONNECT_FAILED   = 201;
    const E_DB_CONFIG_INVALID   = 202;
    const E_DB_OPTIONS_FAILED   = 203;
    const E_DB_INIT_FAILED      = 204;
    const E_DB_CHARSET_FAILED   = 205;
    const E_DB_SELECT_ERROR     = 206;
    const E_DB_INSERT_ERROR     = 207;
    const E_DB_PARAM_INVALID    = 208;
    const E_DB_UPDATE_ERROR     = 209;
    const E_DB_SELECT_DB_ERROR  = 210;

    static $err2str = array(
        self::E_CLASS_NOT_FOUND     => 'class_not_found',
        self::E_REQUEST_INVALID     => 'request_invalid',
        self::E_CLASS_NOT_ACTION    => 'class_not_action',
        self::E_SUCCESS             => 'success',
        self::E_DB_CONNECT_FAILED   => 'db_connect_error',
        self::E_DB_CONFIG_INVALID   => 'db_config_error',
        self::E_DB_OPTIONS_FAILED   => 'db_options_error',
        self::E_DB_INIT_FAILED      => 'db_init_error',
        self::E_DB_INSERT_ERROR     => 'db_insert_error',
    );
}