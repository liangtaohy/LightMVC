<?php
/**
 * Created by unkown ide ps.
 * User: deliang
 * DateTime: 5/18/16 10:23 AM
 */
require_once __DIR__ . '/TaskConfig.class.php';
require_once __DIR__ . '/TaskException.php';
require_once __DIR__ . '/Task.class.php';

class TaskWorker
{

    const TASK_INTERVAL_USLEEP = 3000;

    // 回调方法
    const EVENT_FORWARD_CALLBACK = 100;
    // 执行url
    const EVENT_FORWARD_URL = 101;

    /**
     * 句柄
     * @var GearmanWorker
     */
    protected $worker;

    /**
     * 任务配置配置
     *
     * @var array
     */
    protected $funcsConfig = array(
        'aaa' => array(
            'class' => 'TestA',
            'method' => 'aaa',
        ),
    );

    public function __construct()
    {
        if (!isset(TaskConfig::$config['server'])) {
            throw new TaskException('not set the server config');
        }
        $serverConfig = implode(',', TaskConfig::$config['server']);
        $this->worker = new GearmanWorker();
        $this->worker->addServers($serverConfig);
    }

    /**
     * parse func
     */
    protected function addFunc()
    {
        foreach ($this->funcsConfig as $funcName => $config) {
            $this->worker->addFunction($funcName, function(GearmanJob $job, &$config) {
                $workload = $job->workload();
                $ret = json_decode($workload, true);

                if (empty($ret)) {
                    return false;
                }

                if(isset($ret['timeline']) && !empty($ret['timeline']) && strtotime($ret['timeline'])){
                    $t = strtotime($ret['timeline']);
                    if(time() < $t){
                        usleep(self::TASK_INTERVAL_USLEEP);
                        Task::addDo($job->functionName(), $ret, $ret['type']);
                        return;
                    }
                }

                try {
                    $_data = $ret['data'];
                    switch ($ret['action']) {
                        case self::EVENT_FORWARD_CALLBACK:
                            call_user_func_array(
                                array($config['class'],$config['method'],),
                                array($_data)
                            );
                            break;

                        case self::EVENT_FORWARD_URL:
                            $this->event_url_request(
                                $_data['url'],
                                $_data['params'],
                                $_data['method'],
                                $_data['headers']
                            );
                            break;
                    }
                } catch (Exception $ex) {
                    // todo something
                    MeLog::fatal($ex->getMessage() . '#' . $ex->getCode());
                }

            }, $config);
        }
    }

    /**
     * 执行
     */
    public function run()
    {
        $this->addFunc();

        while (true) {
            $this->worker->work();
            if ($this->worker->returnCode() !== GEARMAN_SUCCESS) {
                // todo:: log op
                echo '//todo:: log op error' . PHP_EOL;
            }
        }
    }

    /**
     * event forward request
     *
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $headers
     * @return mixed
     * @throws Exception
     * @throws TaskException
     * @throws bdHttpException
     */
    protected function event_url_request($url, array $params = array(), $method='post', array $headers = array())
    {
        try {
            switch ($method) {
                case 'post':
                case 'POST':
                    $result = bdHttpRequest::post($url, $params, array(), $headers);
                    break;
                case 'get':
                case 'GET':
                    $result = bdHttpRequest::get($url, $params, array(), $headers);
                    break;
                default:
                    throw new TaskException('未知请求方式: ' . $method, 800010);
                    break;
            }
        } catch (bdHttpException $ex) {
            MeLog::fatal($ex->getMessage() . '#' . $ex->getCode());
            throw new TaskException($ex->getMessage(), $ex->getCode());
        }
        $return = $result->getBody();
        MeLog::fatal($return);
        $data = json_decode($return, true);
        return $data;
    }

}