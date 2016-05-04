<?php

/**
 * DB Proxy
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/26 23:36
 */
class DBProxy
{
    /**
     * 数据库句柄
     * @var
     */
    private $_handle;
    /**
     * 当前使用的配置
     * @var
     */
    private $_config;
    /**
     * 当前负载均衡指向的机器索引
     * @var
     */
    private $_index;
    /**
     * 实例缓存
     * 一个db对应一个实例
     * @var array
     */
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
        $charset = DBProxyConfig::$arrDBMap[$database]['charset'];
        $connect_timeout = intval(DBProxyConfig::$arrDBMap[$database]['connect_timeout']);
        $autocommit = isset(DBProxyConfig::$arrDBMap[$database]['autocommit']) ? intval(DBProxyConfig::$arrDBMap[$database]['autocommit']) : 0;
        $current_tag = defined('CURRENT_TAG') ? CURRENT_TAG : 'default';
        $machines = DBProxyConfig::$arrDBMap[$database][$current_tag];

        // 选择机器 - 负载均衡
        $index = self::selectMachineByTime(is_array($machines) ? $machines : array());
        if ($index === false) {
            MeLog::fatal('dbproxy[instance] dbname[' . $database . '] reason[no_config]');
            return false;
        }

        $dbProxy->_index = $index;

        $host = $machines[$index]['host'];
        $port = $machines[$index]['port'];

        $dbProxy->init($host, $port, $user, $password, $database, $connect_timeout, $charset, $autocommit);
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
    protected function init($host, $port, $user, $password, $dbname, $connect_timeout = 0, $charset = 'utf8', $autocommit = 0)
    {
        $this->_config = array(
            'host'              => $host,
            'port'              => $port,
            'user'              => $user,
            'password'          => $password,
            'dbname'            => $dbname,
            'connect_timeout'   => $connect_timeout,
            'charset'           => $charset,
            'autocommit'        => $autocommit,
        );
    }

    /**
     * ip
     * @return string
     */
    public function host()
    {
        return $this->_config['host'];
    }

    /**
     * 端口号
     * @return int
     */
    public function port()
    {
        return $this->_config['port'];
    }

    /**
     * 编码
     * @return string
     */
    public function charset()
    {
        return $this->_config['charset'];
    }

    /**
     * 数据库名
     * @return string
     */
    public function dbname()
    {
        return $this->_config['dbname'];
    }

    /**
     * connect超时时间
     * @return int
     */
    public function connecttimeout()
    {
        return $this->_config['connect_timeout'];
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
        if (is_array($config) && empty($config)) {
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
            MeLog::fatal('connect[' . $this->_config['dbname'] . '] config[' . json_encode($this->_config) . '] errmsg[' . mysqli_connect_error() . ']', SysErrors::E_DB_CONNECT_FAILED);
            return SysErrors::E_DB_CONNECT_FAILED;
        }

        return SysErrors::E_SUCCESS;
    }

    public function __destruct()
    {
        if ($this->_handle) {
            $this->_handle->close();
        }
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        if (!empty($this->_handle)) {
            $this->_handle->close();
            unset(self::$instances[$this->_config['dbname']]);
            $this->_config = array();
            $this->_handle = null;
        }
    }

    /**
     * 选择数据库（切换数据库）
     * @param $dbname
     * @return bool
     */
    public function selectDB($dbname)
    {
        if (is_string($dbname) && !empty($dbname)) {
            $this->_config['dbname'] = $dbname;
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
        if (!$this->_handle || !is_string($charset) || empty($charset)) {
            MeLog::fatal('mysql[set_charset] expected[' . $charset . '] result[failed]', SysErrors::E_DB_PARAM_INVALID);
            return false;
        }

        if (!$this->_handle->set_charset($charset)) {
            MeLog::fatal('mysql[set_charset] expected[' . $charset . '] result[failed]', SysErrors::E_DB_CHARSET_FAILED);
            return false;
        }
        $this->_config['charset'] = $charset;
        return true;
    }

    /**
     * 执行sql
     * 要使用些方法，需要定义QUERY_ENABLE常量，值为true；否则，该方法不可用
     * @param $sql
     * @return bool
     */
    public function query($sql)
    {
        if (!defined('QUERY_ENABLE') || QUERY_ENABLE === false) {
            MeLog::fatal('mysql[query] expected[mysqli_result] result[disabled]', SysErrors::E_DB_PARAM_INVALID);
            return false;
        }

        if (!$this->_handle || !is_string($sql) || empty($sql)) {
            MeLog::fatal('mysql[query] expected[mysqli_result] result[failed]', SysErrors::E_DB_PARAM_INVALID);
            return false;
        }

        $res = $this->_handle->query($sql);

        if (!$res) {
            MeLog::fatal('mysql[query] expected[mysqli_result] result[failed] sql[' . $sql . ']', SysErrors::E_DB_SELECT_ERROR);
            return false;
        }

        return $res;
    }
    /**
     * 查询数据
     * @note 只能是select语句，不可以是其他语句
     * @param $sql
     * @return array|bool 失败，返回false；成功，返回一个数组（可能为空）
     */
    public function select($sql)
    {
        if (!$this->_handle || !is_string($sql) || empty($sql)) {
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
     * Perform a select query on the database and retriev the first row in results
     * @param string $strSql	The query string
     * @return bool|array	Return result row on success or false on failure
     */
    public function queryFirstRow($strSql)
    {
        if (!$this->mysqli) {
            return false;
        }

        $this->lastSql = $strSql;
        $objRes = $this->mysqli->query($this->lastSql);
        if (!$objRes) {
            return false;
        }

        $arrResult = $objRes->fetch_assoc();
        if ($arrResult) {
            return $arrResult;
        }
        return false;
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
        if (!$this->_handle || !is_string($table) || empty($arrValue) || empty($table)) {
            MeLog::fatal('mysql[insert] expected[true] result[false] reason[param_needed]', SysErrors::E_DB_PARAM_INVALID);
            return false;
        }

        $fields = '';

        $values = '';

        $first = true;

        foreach ($arrValue as $field => $v) {
            if ($first === false) {
                $fields .= ',';
                $values .= ',';
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
     * $param $tables string 逗号分隔
     * @param $where string where条件
     * @return bool 成功时，返回true; 失败时，返回false
     */
    public function delete($tables, $where = '')
    {
        if (!is_string($tables) || empty($tables)) {
            MeLog::fatal('mysql[delete] expected[true] result[false] sql[' . $tables . ']', SysErrors::E_DB_PARAM_INVALID);
        }

        $tables = trim($tables);

        if (empty($where) || !is_string($where)) {
            $where = '1';
        }

        $sql = "DELETE FROM " . $tables . ' WHERE ' . $where;

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
        if (!$this->_handle || !is_string($sql) || empty($sql)) {
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
     * Do multiple sql queries as a transaction
     *
     * @param array $arrSql	Array of sql queries to be executed
     * @return bool Returns true on success or false on failure
     */
    public function doTransaction(array $arrSql)
    {
        if (!$this->_handle) {
            return false;
        }

        $this->_handle->autocommit(false);

        foreach ($arrSql as $strSql) {
            $ret = $this->_handle->query($strSql);
            if (!$ret) {
                $this->lastSql = $strSql;
                $this->_handle->rollback();
                $this->_handle->autocommit(true);
                return false;
            }
        }

        $this->_handle->commit();
        $this->_handle->autocommit(true);

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
     * Get the last inserted data's autoincrement id
     * @return int
     */
    public function getLastInsertID()
    {
        return mysqli_insert_id($this->_handle);
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

    /**
     * Return a safe SQL string according to the format and its arguments
     * Usage example:
     * <code>
     * $format = 'SELECT * FROM table WHERE age=%d and fav=%s';
     * $sql = $dbproxy->buildSqlStr($format, $age, $fav);
     * $res = $dbproxy->doSelectQuery($sql);
     * </code>
     * @param string $format	Template of SQL string
     * @return string	Safe SQL query string
     */
    public function buildSqlStr($format)
    {
        $argv = func_get_args();
        $argc = count($argv);

        $sql_params = array();

        if ($argc > 1) {
            if (!self::typeCheckVprintf($format, $argv, 1)) {
                return false;
            }
            for ($x = 1; $x < $argc; $x++) {
                if (is_string($argv[$x])) {
                    $sql_str = $argv[$x];
                    $sql_str = $this->realEscapeString($sql_str);
                    if ($sql_str === false) {
                        return false;
                    }
                    $sql_params[] = '\'' . $sql_str . '\'';
                } elseif (is_scalar($argv[$x])) {	// check for int/float/bool
                    // don't do anything to int types, they are safe
                    $sql_params[] = $argv[$x];
                } else {	// unsupported type (array, object, resource, null)
                    return false;
                }
            }
            $sql = vsprintf($format, $sql_params);
        } else {
            $sql = str_replace('%%', '%', $format);
        }

        return $sql;
    }

    /**
     * Build SQL query string for Insert operation
     *
     * @param array $arrFields	Data to be inserted in key/value array format
     * @param string $table		Table name
     * @return string	Safe SQL query string
     */
    public function buildInsertSqlStr(array $arrFields, $table)
    {
        if (!$this->_handle || count($arrFields) <= 0) {
            return false;
        }

        $strSql = 'INSERT INTO ' . $table . ' (';
        $strValues = '';
        $needComma = false;
        foreach ($arrFields as $field => $value) {
            if ($needComma) {
                $strSql .= ',';
                $strValues .= ',';
            }
            $needComma = true;
            $strSql .= '`' . $field. '`';
            if (is_string($value)) {
                $strValues .= "'" . mysqli_real_escape_string($this->_handle, $value) . "'";
            } elseif (is_array($value) || is_object($value) || is_null($value)) {
                continue;
            } else {
                $strValues .= "'$value'";
            }
        }
        $strSql .= ') VALUES (' . $strValues . ')';

        return $strSql;
    }

    /**
     * Build SQL query string <b>without WHERE condition</b> for update operation,
     * callers should add <code>WHERE</code> condition part them self.
     *
     * @param array $arrFields	Data to be update in key/value array format
     * @param string $table		Table name
     * @return string	Safe SQL query string
     */
    public function buildUpdateSqlStr(array $arrFields, $table)
    {
        if (!$this->_handle || count($arrFields) <= 0) {
            return false;
        }

        $strSql = 'UPDATE ' . $table . ' SET ';
        $needComma = false;
        foreach ($arrFields as $field => $value) {
            if ($needComma) {
                $strSql .= ',';
            }
            $needComma = true;
            $strSql .= '`' . $field. '`=';
            if (is_string($value)) {
                $strSql .= "'" . mysqli_real_escape_string($this->_handle, $value) . "'";
            } elseif (is_array($value) || is_object($value) || is_null($value)) {
                continue;
            } else {
                $strSql .= "'$value'";
            }
        }
        $strSql .= ' ';

        return $strSql;
    }

    /**
     * Escapes special characters in a string for use in a SQL query
     * @param string $str	String to be escaped
     * @return bool|string	Return escaped string on success or false on failure
     */
    public function realEscapeString($str)
    {
        if (!$this->_handle) {
            return false;
        }
        return $this->_handle->real_escape_string($str);
    }

    /**
     * 格式化sql
     *
     * @param $str
     * @return string
     */
    public function escape_str($str)
    {
        $str = $this->realEscapeString($str);

        return $str;
    }


    //=================== mysqli 事务 =======================

    /**
     * Transaction enabled flag
     *
     * @var	bool
     */
    public $trans_enabled		= TRUE;

    /**
     * Strict transaction mode flag
     *
     * @var	bool
     */
    public $trans_strict		= TRUE;

    /**
     * Transaction depth level
     *
     * @var	int
     */
    protected $_trans_depth		= 0;

    /**
     * Transaction status flag
     *
     * Used with transactions to determine if a rollback should occur.
     *
     * @var	bool
     */
    protected $_trans_status	= TRUE;

    /**
     * Transaction failure flag
     *
     * Used with transactions to determine if a transaction has failed.
     *
     * @var	bool
     */
    protected $_trans_failure	= FALSE;

    /**
     * Disable Transactions
     * This permits transactions to be disabled at run-time.
     *
     * @return	void
     */
    public function trans_off()
    {
        $this->trans_enabled = FALSE;
    }

    /**
     * Enable/disable Transaction Strict Mode
     *
     * When strict mode is enabled, if you are running multiple groups of
     * transactions, if one group fails all subsequent groups will be
     * rolled back.
     *
     * If strict mode is disabled, each group is treated autonomously,
     * meaning a failure of one group will not affect any others
     *
     * @param	bool	$mode = TRUE
     * @return	void
     */
    public function trans_strict($mode = TRUE)
    {
        $this->trans_strict = is_bool($mode) ? $mode : TRUE;
    }

    /**
     * Start Transaction
     *
     * @param	bool	$test_mode = FALSE
     * @return	bool
     */
    public function trans_start($test_mode = FALSE)
    {
        if ( ! $this->trans_enabled)
        {
            return FALSE;
        }

        return $this->trans_begin($test_mode);
    }

    // --------------------------------------------------------------------

    /**
     * Complete Transaction
     *
     * @return	bool
     */
    public function trans_complete()
    {
        if ( ! $this->trans_enabled)
        {
            return FALSE;
        }

        // The query() function will set this flag to FALSE in the event that a query failed
        if ($this->_trans_status === FALSE OR $this->_trans_failure === TRUE)
        {
            $this->trans_rollback();

            // If we are NOT running in strict mode, we will reset
            // the _trans_status flag so that subsequent groups of
            // transactions will be permitted.
            if ($this->trans_strict === FALSE)
            {
                $this->_trans_status = TRUE;
            }

            MeLog::fatal('debug: DB Transaction Failure');
            return FALSE;
        }

        return $this->trans_commit();
    }

    // --------------------------------------------------------------------

    /**
     * Lets you retrieve the transaction flag to determine if it has failed
     *
     * @return	bool
     */
    public function trans_status()
    {
        return $this->_trans_status;
    }

    // --------------------------------------------------------------------

    /**
     * Begin Transaction
     *
     * @param	bool	$test_mode
     * @return	bool
     */
    public function trans_begin($test_mode = FALSE)
    {
        if ( ! $this->trans_enabled)
        {
            return FALSE;
        }
        // When transactions are nested we only begin/commit/rollback the outermost ones
        elseif ($this->_trans_depth > 0)
        {
            $this->_trans_depth++;
            return TRUE;
        }

        // Reset the transaction failure flag.
        // If the $test_mode flag is set to TRUE transactions will be rolled back
        // even if the queries produce a successful result.
        $this->_trans_failure = ($test_mode === TRUE);

        if ($this->_trans_begin())
        {
            $this->_trans_depth++;
            return TRUE;
        }

        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Commit Transaction
     *
     * @return	bool
     */
    public function trans_commit()
    {
        if ( ! $this->trans_enabled OR $this->_trans_depth === 0)
        {
            return FALSE;
        }
        // When transactions are nested we only begin/commit/rollback the outermost ones
        elseif ($this->_trans_depth > 1 OR $this->_trans_commit())
        {
            $this->_trans_depth--;
            return TRUE;
        }

        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Rollback Transaction
     *
     * @return	bool
     */
    public function trans_rollback()
    {
        if ( ! $this->trans_enabled OR $this->_trans_depth === 0)
        {
            return FALSE;
        }
        // When transactions are nested we only begin/commit/rollback the outermost ones
        elseif ($this->_trans_depth > 1 OR $this->_trans_rollback())
        {
            $this->_trans_depth--;
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Begin Transaction
     *
     * @return	bool
     */
    protected function _trans_begin()
    {
        $this->_handle->autocommit(FALSE);
        return is_php('5.5')
            ? $this->_handle->begin_transaction()
            : $this->simple_query('START TRANSACTION'); // can also be BEGIN or BEGIN WORK
    }

    // --------------------------------------------------------------------

    /**
     * Commit Transaction
     *
     * @return	bool
     */
    protected function _trans_commit()
    {
        if ($this->_handle->commit())
        {
            $this->_handle->autocommit(TRUE);
            return TRUE;
        }

        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Rollback Transaction
     *
     * @return	bool
     */
    protected function _trans_rollback()
    {
        if ($this->_handle->rollback())
        {
            $this->_handle->autocommit(TRUE);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Simple Query
     * This is a simplified version of the query() function. Internally
     * we only use it when running transaction commands since they do
     * not require all the features of the main query() function.
     *
     * @param	string	the sql query
     * @return	mixed
     */
    public function simple_query($sql)
    {
        if ( ! $this->_handle)
        {
            #todo: 需要添加重试
            return FALSE;
//            if ( ! $this->initialize())
//            {
//
//            }
        }

        return $this->_execute($sql);
    }

    /**
     * Execute the query
     *
     * @param	string	$sql	an SQL query
     * @return	mixed
     */
    protected function _execute($sql)
    {
        return $this->_handle->query($this->_prep_query($sql));
    }

    /**
     * DELETE hack flag
     *
     * Whether to use the MySQL "delete hack" which allows the number
     * of affected rows to be shown. Uses a preg_replace when enabled,
     * adding a bit more processing to all queries.
     *
     * @var	bool
     */
    public $delete_hack = TRUE;

    /**
     * Prep the query
     *
     * If needed, each database adapter can prep the query string
     *
     * @param	string	$sql	an SQL query
     * @return	string
     */
    protected function _prep_query($sql)
    {
        // mysqli_affected_rows() returns 0 for "DELETE FROM TABLE" queries. This hack
        // modifies the query so that it a proper number of affected rows is returned.
        if ($this->delete_hack === TRUE && preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql))
        {
            return trim($sql).' WHERE 1=1';
        }

        return $sql;
    }

}