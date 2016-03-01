<?php

/**
 * DB Proxy
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/26 23:36
 */
class DBProxy
{
    private $_handle;
    private $_config;

    private static $instances = array();

    /**
     * 获取实例
     */
    public static function getInstance($database)
    {
        if (isset(self::$instances[$database]) && (self::$instances[$database] instanceof DBProxy)) {
            return self::$instances[$database];
        }

        $dbProxy = new self();
        $user = DBProxyConfig::$arrDBMap[$database]['user'];
        $password = DBProxyConfig::$arrDBMap[$database]['password'];
        $current_tag = defined('CURRENT_TAG') ? CURRENT_TAG : 'default';
        $machines = DBProxyConfig::$arrDBMap[$database][$current_tag];

        // 选择机器 - 负载均衡
        $index = self::selectMachineByTime($machines);
        if ($index === false) {
            MeLog::fatal('dbproxy[instance] dbname[' . $database . '] reason[no_config]');
            return false;
        }

        $host = $machines[$index]['host'];
        $port = $machines[$index]['port'];
        $charset = $machines[$index]['charset'];
        $connect_timeout = $machines[$index]['connect_timeout'];

        $dbProxy->init($host, $port, $user, $password, $database, $connect_timeout, $charset);
        if ($dbProxy->connect() !== SysErrors::E_SUCCESS) {
            return false;
        }

        self::$instances[$database] = $dbProxy;
        return $dbProxy;
    }

    /**
     * 根据当前时间选择机器
     * @param array $machines
     * @return int
     */
    private static function selectMachineByTime(array $machines = array())
    {
        if (empty($machines)) {
            return false;
        }

        $total = count($machines);
        return (time() % $total);
    }

    /**
     * 初始化
     * @param $host
     * @param $port
     * @param $user
     * @param $password
     * @param $dbname
     * @param int $connect_timeout
     * @param string $charset
     */
    protected function init($host, $port, $user, $password, $dbname, $connect_timeout = 0, $charset = 'utf8')
    {
        $this->_config = array(
            'host'              => $host,
            'port'              => $port,
            'user'              => $user,
            'password'          => $password,
            'dbname'            => $dbname,
            'connect_timeout'   => $connect_timeout,
            'charset'           => $charset,
        );
    }

    /**
     * 连接数据库
     * @param $config
     *      array(
     *          'host' => 'xxx.xxx.xxx.xxx',
     *          'port'  => 1234,
     *          'user'  => 'lt',
     *          'password'  => '123a232',
     *          'dbname'    => 'testdb',
     *          'connect_timeout'   => 1,
     *      )
     * @return int
     */
    public function connect()
    {
        $config = $this->_config;
        if (is_array($config) && !empty($config)) {
            MeLog::fatal('config is needed!!', SysErrors::E_DB_CONFIG_INVALID);
            return SysErrors::E_DB_CONFIG_INVALID;
        }

        $mysqli = $this->_handle = mysqli_init();

        if (!$mysqli) {
            MeLog::fatal('mysql[mysqli_init] result[failed]', SysErrors::E_DB_INIT_FAILED);
            return SysErrors::E_DB_INIT_FAILED;
        }

        if (!$mysqli->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = ' . $this->_config['autocommit'])) {
            MeLog::fatal('options[autocommit] expected[' . $this->_config['autocommit'] . '] result[failed]', SysErrors::E_DB_OPTIONS_FAILED);
            return SysErrors::E_DB_OPTIONS_FAILED;
        }

        if (!$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->_config['connect_timeout'])) {
            MeLog::fatal('options[connect_timeout] expected[' . $this->_config['connect_timeout'] . '] result[failed]', SysErrors::E_DB_OPTIONS_FAILED);
            return SysErrors::E_DB_OPTIONS_FAILED;
        }

        if ($mysqli->real_connect($this->_config['host'], $this->_config['user'], $this->_config['password'], $this->_config['dbname'], $this->_config['port']) === false) {
            MeLog::fatal('connect[' . $this->_config['dbname'] . '] config[' . json_encode($this->_config) . ']', SysErrors::E_DB_CONNECT_FAILED);
            return SysErrors::E_DB_CONNECT_FAILED;
        }

        return SysErrors::E_SUCCESS;
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        if (!empty($this->_handle)) {
            $this->_handle->close();
        }
    }

    /**
     * 选择数据库（切换数据库）
     * @param $dbname
     * @return bool
     */
    public function selectDB($dbname)
    {
        if (is_string($dbname) && !empty(trim($dbname))) {
            return $this->_handle->select_db($dbname);
        }

        MeLog::fatal('mysql[select_db] expected[' . $dbname . '] result[failed]', SysErrors::E_DB_SELECT_DB_ERROR);

        return false;
    }

    /**
     * 设置默认编码
     * @param string $charset
     * @return bool
     */
    public function setCharset($charset = 'utf8')
    {
        if (!$this->_handle || !is_string($charset) || empty(trim($charset))) {
            MeLog::fatal('mysql[set_charset] expected[' . $charset . '] result[failed]', SysErrors::E_DB_PARAM_INVALID);
            return false;
        }

        if (!$this->_handle->set_charset($charset)) {
            MeLog::fatal('mysql[set_charset] expected[' . $charset . '] result[failed]', SysErrors::E_DB_CHARSET_FAILED);
            return false;
        }
        return true;
    }

    /**
     * 查询数据
     * @note 只能是select语句，不可以是其他语句
     * @param $sql
     * @return array|bool 失败，返回false；成功，返回一个数组（可能为空）
     */
    public function select($sql)
    {
        if (!$this->_handle || !is_string($sql) || empty(trim($sql))) {
            MeLog::fatal('mysql[select] expected[mysqli_result] result[failed]', SysErrors::E_DB_PARAM_INVALID);
            return false;
        }

        $res = $this->_handle->query($sql);

        if (!$res) {
            MeLog::fatal('mysql[select] expected[mysqli_result] result[failed] sql[' . $sql . ']', SysErrors::E_DB_SELECT_ERROR);
            return false;
        }

        $result = array();

        $tmp = $res->fetch_assoc();

        while($tmp) {
            $result[] = $tmp;
            $tmp = $res->fetch_assoc();
        }

        $res->close(); // 释放结果

        return $result;
    }

    /**
     * 插入一条数据
     * @param array $arrValue
     * @param $table
     * @param $ignore_unique 是否使用ignore关键字。如果使用ignore关键字，则在插入时，如果发现语句违反了unique约束，则忽略
     * @return bool 成功，返回true；失败，返回false，并打印fatal日志
     */
    public function insert(array $arrValue = array(), $table, $ignore_unique = false)
    {
        if (!$this->_handle || !is_string($table) || empty($arrValue) || empty(trim($table))) {
            MeLog::fatal('mysql[insert] expected[true] result[false] reason[param_needed]', SysErrors::E_DB_PARAM_INVALID);
            return false;
        }

        $fields = '';

        $values = '';

        $first = true;

        foreach ($arrValue as $field => $v) {
            if ($first === false) {
                $fields = ',';
                $values = ',';
            }

            $first = false;

            $fields .= '`' . $field . '`';

            $values .= "'" . mysqli_real_escape_string($this->_handle, $v) . "'";
        }

        $sql = 'INSERT INTO ';
        if ($ignore_unique) {
            $sql = 'INSERT IGNORE INTO ';
        }
        $sql .= $table . ' (' . $fields . ') VALUES (' . $values . ')';

        if (!$this->_handle->query($sql)) {
            MeLog::fatal('mysql[insert] expected[true] result[false] sql[' . $sql . ']', SysErrors::E_DB_INSERT_ERROR);
            return false;
        }

        return true;
    }

    /**
     * 删除记录
     * @param $sql
     * @return bool 成功时，返回true; 失败时，返回false
     */
    public function delete($sql)
    {
        if (!$this->_handle || !is_string($sql) || empty(trim($sql))) {
            MeLog::fatal('mysql[delete] expected[true] result[false] sql[' . $sql . ']', SysErrors::E_DB_PARAM_INVALID);
            return false;
        }

        if (!$this->_handle->query($sql)) {
            MeLog::fatal('mysql[delete] expected[true] result[false] sql[' . $sql . ']', SysErrors::E_DB_UPDATE_ERROR);
            return false;
        }

        return true;
    }

    /**
     * 更新记录
     * @param $sql
     * @return bool 成功时，返回true; 失败时，返回false
     */
    public function update($sql)
    {
        if (!$this->_handle || !is_string($sql) || empty(trim($sql))) {
            MeLog::fatal('mysql[update] expected[true] result[false] sql[' . $sql . ']', SysErrors::E_DB_PARAM_INVALID);
            return false;
        }

        if (!$this->_handle->query($sql)) {
            MeLog::fatal('mysql[update] expected[true] result[false] sql[' . $sql . ']', SysErrors::E_DB_UPDATE_ERROR);
            return false;
        }

        return true;
    }

    /**
     * 获取受影响的行数（select, insert, delete, update）
     * @return bool | int 失败，返回false; 成功，返回受影响的行数，有可能为0
     */
    public function getAffectedRows()
    {
        if (!$this->_handle) {
            MeLog::fatal('mysql[affected_rows] expected[true] result[false]', SysErrors::E_DB_PARAM_INVALID);
            return false;
        }

        return $this->_handle->affected_rows;
    }

    /**
     * 获取错误码
     * @return int
     */
    public function getErrno()
    {
        if (!$this->_handle) {
            return -1;
        } else {
            return $this->_handle->errno;
        }
    }
}