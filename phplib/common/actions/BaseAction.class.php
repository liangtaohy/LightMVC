<?php

/**
 * BaseAction基类
 * User: liangtao
 * Date: 16/3/31
 * Time: AM12:20
 */
class BaseAction extends Action
{
    /**
     * Whether we are under debug mode or not.
     *
     * @var bool
     */
    protected $is_debug = false;

    /**
     * 获取请求参数
     *
     * @return array
     */
    protected function getRequestParams()
    {
        return array_merge($_GET, $_POST);
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
}