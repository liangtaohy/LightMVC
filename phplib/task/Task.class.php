<?php
/**
 * Created by unkown ide ps.
 * User: deliang
 * DateTime: 5/17/16 6:02 PM
 */
class Task
{

    const TASK_DEFAULT = 0x1;
    const TASK_BACKGROUND = 0x2;
    const TASK_HIGHT = 0x3;
    const TASK_HIGH_BACKGROUND = 0x4;
    const TASK_LOW = 0x5;
    const TASK_LOW_BACKGROUND = Ox6;

    protected $taskMap = array(
        self::TASK_DEFAULT => 'addTask',
        self::TASK_BACKGROUND => 'addTaskBackground',
        self::TASK_HIGHT => 'addTaskHigh',
        self::TASK_HIGH_BACKGROUND => 'addTaskHighBackground',
        self::TASK_LOW => 'addTaskLow',
        self::TASK_LOW_BACKGROUND => 'addTaskLowBackground',
    );

    protected $client;

    public function __construct()
    {
        if (!isset(TaskConfig::$config['server'])) {
            throw new TaskException('not set the server config');
        }
        $serverConfig = implode(',', TaskConfig::$config['server']);
        $this->client = new GearmanClient();
        $this->client->addServers($serverConfig);
    }

    public function addTask($name, array $data, $type = self::TASK_DEFAULT)
    {
        if (!isset($this->taskMap[$type])) {
            throw new TaskException('not exsit type', 300010);
        }

        $data = json_encode($data);
        return $this->client->{$this->taskMap[$type]}($name, $data);
    }

    public function run()
    {
        $this->client->runTasks();
    }
}