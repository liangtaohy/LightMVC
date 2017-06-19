<?php

/**
 * 路由控制器
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 22:11
 */
/**
 * 路由规则说明
 * 支持三种路由规则：
 * 1. hash_mapping
 * 2. prefix_mapping
 * 3. regex_mapping
 * 优先级定义为：1>2>3
 * 路由控制器依次匹配，直到匹配上或所有规则已匹配完为止
 */
class Controller
{
    const TYPE_HASH_MAPPING     = 'hash_mapping';
    const TYPE_PREFIX_MAPPING   = 'prefix_mapping';
    const TYPE_REGEX_MAPPING    = 'regex_mapping';

    const CLASS_NOT_FOUND_ACTION    = 'DefaultAction';

    /**
     * 哈希匹配
     * @var array
     */
    private $HashMap;

    /**
     * 前辍匹配
     * @var array
     */
    private $PrefixMap;

    /**
     * 正则匹配
     * @var array
     */
    private $RegexMap;

    /**
     * 单实例
     * @var
     */
    private static $instance;

    /**
     * 获取Controller实例
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 构造方法
     */
    private function __construct()
    {
        $this->init(self::$config);
    }

    /**
     * 路由配置
     * @var array
     */
    public static $config = array();

    /**
     * @param $config
     */
    private function init($config = array())
    {
        if (is_array($config) && !empty($config)) {
            $this->HashMap = isset($config[self::TYPE_HASH_MAPPING]) ? $config[self::TYPE_HASH_MAPPING] : array();
            $this->PrefixMap = isset($config[self::TYPE_PREFIX_MAPPING]) ? $config[self::TYPE_PREFIX_MAPPING] : array();
            $this->RegexMap = isset($config[self::TYPE_REGEX_MAPPING]) ? $config[self::TYPE_REGEX_MAPPING] : array();
        } else {
            MeLog::fatal("Failed to init Controller");
            exit;
        }
    }

    /**
     * Controller的执行入口
     * @param $context
     * @return bool
     */
    private function execute($context)
    {
        $act = "";

        $urlOjb = parse_url($context->getUrl());

        if (is_array($urlOjb)) {
            $act = $urlOjb['path'];
        }

        $params = $this->determineRoute2Action($act);

        if (is_array($params) && !empty($params)) {
            return $context->invokeAction($params);
        } else {
            MeLog::fatal("route2Action failed: " . $act);
            return false;
        }
    }

    /**
     * 提取路由对象
     * @param $act
     * @return array
     */
    public function determineRoute2Action($path)
    {
        if (!is_string($path) || empty($path)) {
            throw new XdpOpenAPIException(XDPAPI_EC_METHOD);
            //return array(self::CLASS_NOT_FOUND_ACTION);
        }

        $result = array();

        if ($this->hitHashMap($path, $result)) {
            return $result;
        } else if ($this->hitPrefixMap($path, $result)) {
            return $result;
        } else if ($this->hitRegexMap($path, $result)) {
            return $result;
        }
        throw new XdpOpenAPIException(XDPAPI_EC_METHOD);
        //return array(self::CLASS_NOT_FOUND_ACTION);
    }

    /**
     * 哈希路由规则
     * @param $act
     * @param $result
     * @return bool
     */
    private function hitHashMap($act, &$result)
    {
        if (array_key_exists($act, $this->HashMap)) {
            $result = array_merge($result, $this->HashMap[$act]);
            return true;
        }
        return false;
    }

    /**
     * 前辍路由规则
     * @param $act
     * @param $result
     * @return bool
     */
    private function hitPrefixMap($act, &$result)
    {
        if (!empty($this->PrefixMap)) {
            foreach ($this->PrefixMap as $pattern => $action) {
                if (stripos($act, $pattern) === 0) {
                    $result = array_merge($result, $action);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 正则路由规则
     * @param $act
     * @param $result
     * @return bool
     */
    private function hitRegexMap($act, &$result)
    {
        foreach ($this->RegexMap as $pattern => $action) {
            if (preg_match($pattern, $act, $matches)) {
                $result = array_merge($result, $action);
                return true;
            }
        }
        return false;
    }
}