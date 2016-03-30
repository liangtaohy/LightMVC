<?php
/**
 * @author {{AUTHOR}}
 */

class SampleApi extends Action
{
    /**
     * 初始化入口
     * @return mixed
     */
    public function init($context)
    {
        //
    }

    /**
     * Action的执行入口
     * @param $context
     * @return mixed
     */
    public function execute($context, $action_params = array())
    {
        header('HTTP/1.1 200 Ok');
        header('status: 200 Ok');

        echo 'Hi,guy! Welcome to {{APP_NAME}}';
        return true;
    }
}