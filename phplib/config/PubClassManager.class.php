<?php
/**
 * 公共类加载配置
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 17:50
 */

class PubClassManager
{
    /**
     * 类名到类文件的映射数组
     * @var array
     */
    private $clzmap;

    private static $instance = null;

    /**
     * 单例方法
     * @return null|PubClassManager
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 构造方法
     */
    private function __construct()
    {
        if (defined('PHPLIB_PATH')) {
            $this->clzmap = array(
                'MeLog'         => PHPLIB_PATH . '/log/MeLog.class.php',
                'IBaseTest'     => PHPLIB_PATH . '/testfw/IBaseTest.class.php',
                'SimpleTest'    => PHPLIB_PATH . '/testfw/SimpleTest.class.php',
                'Utils'         => PHPLIB_PATH . '/utils/Utils.class.php',
                'Validator'     => PHPLIB_PATH . '/utils/Validator.class.php',
                'Smtp'          => PHPLIB_PATH . '/utils/Smtp.class.php',
                'Action'        => PHPLIB_PATH . '/framework/Action.class.php',
                'Application'   => PHPLIB_PATH . '/framework/Application.class.php',
                'Context'       => PHPLIB_PATH . '/framework/Context.class.php',
                'Controller'    => PHPLIB_PATH . '/framework/Controller.class.php',
                'DefaultAction' => PHPLIB_PATH . '/framework/DefaultAction.class.php',
                'SysErrors'     => PHPLIB_PATH . '/framework/SysErrors.class.php',
                'ViewFactory'   => PHPLIB_PATH . '/smarty/ViewFactory.class.php',
                // db
                'DBProxyConfig' => PHPLIB_PATH . '/db/DBProxyConfig.class.php',
                'DBProxy'       => PHPLIB_PATH . '/db/DBProxy.class.php',
                // smarty
                'Smarty'        => SMARTY_PATH . '/Smarty.class.php',
                'BaseAction'    => PHPLIB_PATH . '/common/actions/BaseAction.class.php',
                'bdHttpRequest' => PHPLIB_PATH . '/bdHttp/bdHttpRequest.class.php',
                'bdURL'         => PHPLIB_PATH . '/bdHttp/bdURL.class.php',
                'NewRedisProxy' => PHPLIB_PATH . '/redis/NewRedisProxy.class.php',
                'Cache'         => PHPLIB_PATH . '/memcached/Cache.class.php',
                'SMSFactory'    => PHPLIB_PATH . '/sms/SMSFactory.class.php',
                'FrequencyAuth' => PHPLIB_PATH . '/frequency/FrequencyAuth.class.php',
                'LogStash'      => PHPLIB_PATH . '/log/LogStash.class.php',

                // task
                'Task'          => PHPLIB_PATH . '/task/Task.class.php',
                'TaskWorker'    => PHPLIB_PATH . '/task/TaskWorker.class.php',
            );
        } else {
            if (defined('DEBUG') && DEBUG == true) {
                MeLog::fatal('PHPLIB_PATH must be defined!');
            }
            exit;
        }
    }

    /**
     * 类注册
     * @param $clsname
     * @param $path
     * @return bool
     */
    public function registerClass($clsname, $path)
    {
        if (is_string($clsname) && is_string($path)) {
            $this->clzmap[$clsname] = $path;
            return true;
        }
        return false;
    }

    /**
     * 批量类注册
     * @param array $clsArr
     * @return void
     */
    public function registerClasses($clsArr)
    {
        if (!is_array($clsArr) || empty($clsArr)) {
            return false;
        }

        if (!isset($this->clzmap)) {
            $this->clzmap = $clsArr;
        } else {
            $this->clzmap = array_merge($this->clzmap, $clsArr);
        }
        return true;
    }

    /**
     * 获取类map
     * @return array
     */
    public function getRegisterClassMap()
    {
        return $this->clzmap;
    }
}