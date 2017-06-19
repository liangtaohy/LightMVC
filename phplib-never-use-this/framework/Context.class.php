<?php

/**
 * 应用上下文Context
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 22:12
 */
class Context
{
    /**
     * 用户请求，是一个参数数组，由$_GET和$_POST合并组成
     * @var array
     */
    private $_request;

    private $_response;

    /**
     * 用户notice日志数组，用户的notice日志均应该放在这里
     * @var
     */
    private $_notice_logs;

    /**
     * request url
     * @var
     */
    private $_url;

    /**
     * http方法
     * @var
     */
    private $_request_method;

    /**
     * 运行方式
     * cli: 当在终端执行时，为cli模式
     * cgi: 当在web浏览器访问时，为cgi模式
     * @var string
     */
    private $_run_mode;

    /**
     * 错误码
     * @var
     */
    private $_errno = 0;

    /**
     * 单例
     * @var
     */
    private static $_instance;

    /**
     * 获取Context实例
     * 每一个用户请求对应的上下文是唯一的
     * @return Context
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 构造方法
     */
    private function __construct()
    {
        $this->_run_mode = substr(php_sapi_name(), 0, 3);
        // cli mode
        if ($this->_run_mode == Application::RUN_MODE_CLI) {
            $this->processCliRequest();
        } else {
            $this->_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
            $this->_request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : "";
            $this->_request = array_merge($_GET, $_POST);
        }
        $this->_notice_logs = array();
    }

    /**
     * 处理cli请求
     */
    protected function processCliRequest()
    {
        $this->_url = isset($_SERVER['argv']) && isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : "";
        $urlObj = parse_url($this->_url);

        if (empty($urlObj)) {
            MeLog::fatal('url required');
            exit();
        }

        $this->_request = array();

        $this->_response = array();

        $query_str = isset($urlObj['query']) ? $urlObj['query'] : '';

        $query_arr = explode("&", $query_str);

        $query_arr = array_map('trim', $query_arr);

        foreach($query_arr as $query) {
            $q = explode("=", $query);
            isset($q[0]) && ($this->_request[$q[0]] = isset($q[1]) ? $q[1] : '');
        }

        $this->_request_method = 'cli';
    }

    /**
     * 获取用户请求(_GET合并_POST后的参数数组)
     * @return mixed
     */
    public function getRequest() { return $this->_request; }

    /**
     * 获取request url
     * @return mixed
     */
    public function getUrl()    { return $this->_url; }

    /**
     * 获取request method
     * @return string
     */
    public function getRequestMethod() { return $this->_request_method; }

    /**
     * 获取运行模式
     * @return string
     */
    public function getRunMode() { return $this->_run_mode; }

    /**
     * 应用执行入口
     * @return bool|mixed
     */
    public function execute()
    {
        $path = "";

        $urlOjb = parse_url($this->getUrl());

        if (is_array($urlOjb)) {
            $path = $urlOjb['path'];
        }

        $params = Controller::getInstance()->determineRoute2Action($path);

        if (is_array($params) && !empty($params)) {
            return $this->invokeAction($params);
        } else {
            MeLog::fatal("route2Action failed: " . $path);
            return false;
        }
    }

    /**
     * invoke action
     * @param $params array类型 第一个值为class name，第二个值为应用自定义action参数
     * @return mixed
     */
    protected function invokeAction($params)
    {
        $action = Action::getDelegateAction($this, $params[0]);

        if ($action !== false) {
            return $action->execute($this, isset($params[1]) ? $params[1] : array());
        }

        return false;
    }

    /**
     * 填加notice日志
     * @param $key
     * @param $value
     */
    public function addNotice($key, $value)
    {
        if (is_string($key) && !empty($key)) {
            $this->_notice_logs[$key] = $value;
        }
    }

    /**
     * 返回notice日志数组
     * @return array
     */
    public function getNoticeLogs() { return $this->_notice_logs; }

    /**
     * 设置错误码
     * @param $errno
     */
    public function setErrno($errno)
    {
        $this->_errno = intval($errno);
    }

    public function getErrno() { return $this->_errno; }

    public function addResult($key, $v) {
        $this->_response[$key] = $v;
    }

    public function addResults($results = array())
    {
        $this->_response = array_merge($this->_response, $results);
    }

    public function setResponse(array $res = array()) { $this->_response = $res; }

    public function getResponse() { return $this->_response; }
}