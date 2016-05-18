<?php
/**
 * Created by unkown ide ps.
 * User: deliang
 * DateTime: 5/18/16 10:23 AM
 */
class TaskWorker
{
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
        'exmaple' => array(
            'class' => 'democlass',
            'method' => 'demomethod',
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
                $data = json_decode($workload, true);
                try {
                    call_user_func_array(
                        array(
                            $config['class'],
                            $config['method']
                        ),
                        array($data)
                    );
                } catch (Exception $ex) {
                    // todo something
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

}