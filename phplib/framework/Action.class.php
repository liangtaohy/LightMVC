<?php

/**
 * 抽象类Action
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 22:11
 */
abstract class Action
{
    /**
     * 获取Action
     * @param $clsname
     * @return mixed 失败，返回false
     */
    public static function getDelegateAction($clsname)
    {
        if (is_string($clsname)
            && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/i', $clsname) && class_exists($clsname, true)) {

            $obj = new $clsname;

            if ($obj instanceof Action) {
                $obj->init();
                return $obj;
            } else {
                // $obj不是Action的子类，打印Fatal日志
                MeLog::fatal('ActionClass[' . $clsname . '] not instance of Action');
                return false;
            }
        } else {
            MeLog::fatal('ActionClass[' . $clsname . '] not existed');
            return false;
        }
    }

    /**
     * 初始化入口
     * @return mixed
     */
    abstract public function init($context);

    /**
     * Action的执行入口
     * @param $context
     * @return mixed
     */
    abstract public function execute($context, $action_params = array());
}