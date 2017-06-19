<?php

/**
 * 测试用例 HiAction
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/25 12:01
 */
class HiAction extends Action
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

        echo 'Hi,guy!';
        return true;
    }
}