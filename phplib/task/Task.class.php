<?php
/**
 * Created by unkown ide ps.
 * User: deliang
 * DateTime: 5/17/16 6:02 PM
 */
require_once __DIR__ . '/TaskConfig.class.php';
require_once __DIR__ . '/TaskException.php';

class Task
{

    const TASK_DEFAULT = 0x1;
    const TASK_BACKGROUND = 0x2;
    const TASK_HIGHT = 0x3;
    const TASK_HIGH_BACKGROUND = 0x4;
    const TASK_LOW = 0x5;
    const TASK_LOW_BACKGROUND = 0x6;

    const TASK_DO = 0x7;
    const TASK_DO_BACKGROUND = 0x8;
    const TASK_DO_HIGH = 0x9;
    const TASK_DO_HIGH_BACKGROUND = 0xA;
    const TASK_DO_LOW = 0xB;
    const TASK_DO_LOW_BACKGROUND = 0xC;
    const TASK_DO_NORMAL = 0xD;

    protected static $taskMap = array(
        self::TASK_DEFAULT => 'addTask',
        self::TASK_BACKGROUND => 'addTaskBackground',
        self::TASK_HIGHT => 'addTaskHigh',
        self::TASK_HIGH_BACKGROUND => 'addTaskHighBackground',
        self::TASK_LOW => 'addTaskLow',
        self::TASK_LOW_BACKGROUND => 'addTaskLowBackground',
    );

    protected static $taskDoMap = array(
        self::TASK_DO => 'do',
        self::TASK_DO_BACKGROUND => 'doBackground',
        self::TASK_DO_HIGH => 'doHigh',
        self::TASK_DO_HIGH_BACKGROUND => 'doHighBackground',
        self::TASK_DO_LOW => 'doLow',
        self::TASK_DO_LOW_BACKGROUND => 'doLowBackground',
        self::TASK_DO_NORMAL => 'doNormal',
    );

    protected static $client;

    protected static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Task();
        }

        return self::$instance;
    }

    private function __construct()
    {
        if (!isset(TaskConfig::$config['server'])) {
            throw new TaskException('not set the server config');
        }
        $serverConfig = implode(',', TaskConfig::$config['server']);
        self::$client = new GearmanClient();
        self::$client->addServers($serverConfig);
    }

    /**
     * 添加并行任务
     *
     * @param $name
     * @param array $data
     * @param int $type
     * @return mixed
     * @throws TaskException
     */
    public static function addTask($name, array $data, $type = self::TASK_DEFAULT)
    {
        //var_dump($name, $data, $type);
        if (!isset(self::$taskMap[$type])) {
            throw new TaskException('not exsit type', 300010);
        }
        self::getInstance();

        $method = self::$taskMap[$type];
        $data = json_encode($data);
        return self::$client->$method($name, $data);
    }

    /**
     * 添加异步任务
     *
     * @param $name
     * @param array $data
     * @param int $type
     * @return mixed
     * @throws TaskException
     */
    public static function addDo($name, array $data, $type = self::TASK_DO_HIGH_BACKGROUND)
    {
        if (!isset(self::$taskDoMap[$type])) {
            throw new TaskException('not exsit type', 300010);
        }
        self::getInstance();

        $method = self::$taskDoMap[$type];
        $data = json_encode($data);
        return self::$client->$method($name, $data);
    }

    /**
     * 提交并行任务
     */
    public static function run()
    {
        self::getInstance();

        self::$client->runTasks();
    }
}