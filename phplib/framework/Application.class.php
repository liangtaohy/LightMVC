<?php

/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 16/2/18
 * Time: 22:11
 */
class Application
{
    /**
     * 应用运行模式常量定义
     */
    const RUN_MODE_CLI = 'cli';
    const RUN_MODE_CGI = 'cgi';

    private $context;

    /**
     * 应用执行入口
     */
    public static function start()
    {
        MeLog::debug('app is running...');
        try {
            $ret = Context::getInstance()->execute();
        } catch (Exception $e) {
            trigger_error(var_export($e));
            $log = sprintf("time[%d] method[%s] request[%s] result[%s]",
                0,
                Context::getInstance()->getRequestMethod(),
                json_encode(Context::getInstance()->getRequest()),
                $e->getMessage()
            );
            MeLog::notice($log, 0, Context::getInstance()->getNoticeLogs());
            return false;
        }

        $log = sprintf("method[%s] request[%s] result[%s]",
                Context::getInstance()->getRequestMethod(),
                json_encode(Context::getInstance()->getRequest()),
                json_encode($ret)
            );

        MeLog::notice($log, 0, Context::getInstance()->getNoticeLogs());

        return $ret;
    }
}