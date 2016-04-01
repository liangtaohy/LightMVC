<?php

/**
 * Created by PhpStorm.
 * User: liangtao
 * Date: 16/3/31
 * Time: AM12:02
 */
class CimongoConfig
{
    // This should be override by Application's conf
    public static $conf = [
        'mongo' => [
            'default' => [
                'host' => 'test.ydl.com',
                'port' => '27017',
                'user' => 'root',
                'password' => '123123',
                'dbname' => 'admin',
                'query_safety' => TRUE,
                'db_flag' => TRUE,
            ],
        ],
    ];
}