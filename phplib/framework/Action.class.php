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
    public static function getDelegateAction($context, $clsname)
    {
        if (is_string($clsname)
            && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/i', $clsname) && class_exists($clsname, true)) {

            $obj = new $clsname;

            if ($obj instanceof Action) {
                $obj->init($context);
                return $obj;
            } else {
                // $obj不是Action的子类，打印Fatal日志
                Context::getInstance()->setErrno(SysErrors::E_CLASS_NOT_ACTION);
                MeLog::fatal('ActionClass[' . $clsname . '] not instance of Action', SysErrors::E_CLASS_NOT_ACTION);
                return false;
            }
        } else {
            Context::getInstance()->setErrno(SysErrors::E_CLASS_NOT_FOUND);
            MeLog::fatal('ActionClass[' . $clsname . '] not existed', SysErrors::E_CLASS_NOT_FOUND);
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