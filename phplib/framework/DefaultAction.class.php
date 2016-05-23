<?php

/**
 * 默认Action
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/24 10:24
 */
class DefaultAction extends Action
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
        header('HTTP/1.1 200 OK');
        header('status: 200 OK');

        echo 'What do you want?';
        return true;
    }
}