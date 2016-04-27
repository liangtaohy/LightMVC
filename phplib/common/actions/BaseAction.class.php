<?php

/**
 * BaseAction基类
 * User: liangtao
 * Date: 16/3/31
 * Time: AM12:20
 */
class BaseAction extends Action
{
    const T_MOBILE = 'T_MOBILE';
    const T_EMAIL = 'T_EMAIL';

    /**
     * Whether we are under debug mode or not.
     *
     * @var bool
     */
    protected $is_debug = false;

    protected $_params = null;

    /**
     * 获取请求参数
     *
     * @return array
     */
    protected function getRequestParams()
    {
        return isset($this->_params) ? $this->_params : $this->_params = array("trim", array_merge($_GET, $_POST));
    }

    /**
     * 获取请求方法
     *
     * @return string
     */
    protected function getUrlPath()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * 设置http response header
     *
     * @return void
     */
    protected function setHttpResponseHeader()
    {
        $request = $this->getRequestParams();
        if (isset($request['callback'])) {
            header('Content-type: text/javascript');
        } else {
            header('Content-type: application/json');
        }
    }

    /**
     * 返回数据
     *
     * @param string $result
     * @return string
     */
    protected function sendResponseBody($result)
    {
        $request = $this->getRequestParams();
        if(isset($request['callback'])){
            $callback = preg_replace('/[^\w\.\'"()]/', '', $request['callback']);
            $result = $callback . '(' . $result . ');';
        }
        return $result;
    }

    /**
     * 初始化入口
     * @return mixed
     */
    public function init($context)
    {
        if (defined('IS_DEBUG') && IS_DEBUG) {
            $this->is_debug = true;
        }
    }

    protected function isApi()
    {
        return false;
    }

    /**
     * 检查必选参数
     * @param $action_params
     * @param $params
     * @return bool
     * @throws XdpOpenAPIException
     */
    protected function checkRequiredParams(&$action_params, &$params)
    {
        if (empty($action_params) || !isset($action_params['required']) || empty($action_params['required'])) {
            return true;
        }

        foreach ($action_params['required'] as $item => $desc) {
            if (isset($params[$item]) && !empty($params[$item])) {
                return self::itemValidation($item, $params[$item], $desc);
            }

            throw new XdpOpenAPIException(XDPAPI_EC_PARAM, null, $item . '_required');
        }
    }

    /**
     * 类型校验
     * @param $item_name
     * @param $value
     * @param $desc
     * @return bool
     * @throws XdpOpenAPIException
     */
    public static function itemValidation($item_name, $value, $desc)
    {
        $ret = false;
        $len = mb_strlen($value, 'UTF-8');
        $type = $desc['type'];

        switch ($type) {
            case self::T_MOBILE :
                $ret = Validator::isValidPhone($value);
                break;
            case self::T_EMAIL :
                $ret = Validator::isValidEmail($value);
                break;
        }

        if (!$ret) {
                MeLog::warning("errno[" . XDPAPI_EC_PARAM . "]" . " errmsg[name:" . $item_name . "; value:" . $value . "]" );
                throw new XdpOpenAPIException(XDPAPI_EC_PARAM, null, "param invalid name:" . $item_name . "; value:" . $value);
        }

        return true;
    }
}