<?php
/**
 * 通用日志类
 * @file MeLog.php
 * @author LiangTao(liangtaohy@163.com)
 * @date 2016/02/17 22:31:44
 * @version $Revision: 1.1 $
 * @brief class for logging
 *
 **/

/**
 * @example:
 *
 <?php
 require_once('MeLog.php');

 $GLOBALS['LOG'] = array(
	'log_level'			=> 0x07,		//fatal, warning, notice
	'log_file'		=> '/home/space/space/log/test.log',	//test.log.wf will be the wf log file
	);
 **/

class MeLog
{
    const LOG_LEVEL_NONE    = 0x00;
    const LOG_LEVEL_FATAL   = 0x01;
    const LOG_LEVEL_WARNING = 0x02;
    const LOG_LEVEL_NOTICE  = 0x04;
    const LOG_LEVEL_TRACE   = 0x08;
    const LOG_LEVEL_DEBUG   = 0x10;
    const LOG_LEVEL_ALL     = 0xFF;

    public static $LogLevels = array(
        self::LOG_LEVEL_NONE    => 'NONE',
        self::LOG_LEVEL_FATAL   => 'FATAL',
        self::LOG_LEVEL_WARNING => 'WARNING',
        self::LOG_LEVEL_NOTICE  => 'NOTICE',
        self::LOG_LEVEL_TRACE	=> 'TRACE',
        self::LOG_LEVEL_DEBUG   => 'DEBUG',
        self::LOG_LEVEL_ALL     => 'ALL',
    );

    protected $level;
    protected $logfile;
    protected $logid;
    protected $starttime;
    protected $clientip;

    private static $instance = null;

    private function __construct($conf, $starttime)
    {
        if (!isset($conf) || empty($conf['log_file'])) {
            echo "Fatal load log conf failed!\n";
            exit;
        }

        $this->level        = intval($conf['log_level']);
        $this->logfile		= $conf['log_file'];
        $this->logid		= self::__logId();
        $this->starttime	= $starttime;
        $this->clientip     = Utils::getClientIP();
    }

	/**
	 * @return CLog
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			$stime = defined('PROCESS_START_TIME') ? PROCESS_START_TIME : microtime(true) *
				 1000;
			self::$instance = new self($GLOBALS['LOG'], $stime);
		}
		
		return self::$instance;
	}

    /**
     * Write debug log
     * 
     * @param string $str		Self defined log string
     * @param int $errno		errno to be write into log
     * @param array $arrArgs	params in k/v format to be write into log
     * @param int $depth		depth of the function be packaged
     */
    public static function debug($str, $errno = 0, $arrArgs = null, $depth = 0)
    {
        return self::getInstance()->writeLog(self::LOG_LEVEL_DEBUG, $str, $errno, $arrArgs, $depth + 1);
    }

    /**
     * Write trace log
     * 
     * @param string $str		Self defined log string
     * @param int $errno		errno to be write into log
     * @param array $arrArgs	params in k/v format to be write into log
     * @param int $depth		depth of the function be packaged
     */
	public static function trace($str, $errno = 0, $arrArgs = null, $depth = 0)
    {
        return self::getInstance()->writeLog(self::LOG_LEVEL_TRACE, $str, $errno, $arrArgs, $depth + 1);
    }

    public static function notice($str, $errno = 0, $arrArgs = null, $depth = 0)
    {
        return self::getInstance()->writeLog(self::LOG_LEVEL_NOTICE, $str, $errno, $arrArgs, $depth + 1);
    }

    /**
     * Write warning log
     * 
     * @param string $str		Self defined log string
     * @param int $errno		errno to be write into log
     * @param array $arrArgs	params in k/v format to be write into log
     * @param int $depth		depth of the function be packaged
     */
    public static function warning($str, $errno = 0, $arrArgs = null, $depth = 0)
    {
        return self::getInstance()->writeLog(self::LOG_LEVEL_WARNING, $str, $errno, $arrArgs, $depth + 1);
    }

    /**
     * Write fatal log
     * 
     * @param string $str		Self defined log string
     * @param int $errno		errno to be write into log
     * @param array $arrArgs	params in k/v format to be write into log
     * @param int $depth		depth of the function be packaged
     */
    public static function fatal($str, $errno = 0, $arrArgs = null, $depth = 0)
    {
        return self::getInstance()->writeLog(self::LOG_LEVEL_FATAL, $str, $errno, $arrArgs, $depth + 1);
    }

    /**
     * Get logid for current http request
     * @return int
     */
    public static function logId()
    {
        return self::getInstance()->intLogId;
    }

    /**
     * 写入日志
     * 使用file_put_contents
     * @param $level
     * @param $str
     * @param int $errno
     * @param null $arrArgs
     * @param int $depth
     * @return int|void
     */
	public function writeLog($level, $str, $errno = 0, $arrArgs = null, $depth = 0)
	{
		if ($level > $this->level || !isset(self::$LogLevels[$level])) {
			return;
		}
		
		$log_file = $this->logfile;
		if (($level & self::LOG_LEVEL_WARNING) || ($level & self::LOG_LEVEL_FATAL)) {
			$log_file .= '.wf';
		}
		
		$trace = debug_backtrace(); // 获取调试堆栈
		if ($depth >= count($trace)) {
			$depth = count($trace) - 1;
		}

		$file = basename($trace[$depth]['file']);
		$line = $trace[$depth]['line'];
		
		$strArgs = '';
		if (is_array($arrArgs) && count($arrArgs) > 0) {
			foreach ($arrArgs as $key => $value) {
				$strArgs .= $key . "[$value] ";
			}
		}

        $intTimeUsed = microtime(true)*1000 - $this->starttime;

        $str = sprintf( "%s: %s [%s:%d] errno[%d] ip[%s] logid[%u] uri[%s] time_used[%d] %s%s\n",
                        self::$LogLevels[$level],
                        date('m-d H:i:s:', time()),
                        $file, $line, $errno,
                        $this->clientip,
                        $this->logid,
                        isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
                        $intTimeUsed, $strArgs, $str);

        $path = substr($log_file, 0, strrpos($log_file, '/'));
        @mkdir($path, 0777, true);

        return file_put_contents($log_file, $str, FILE_APPEND);
    }

    /**
     * 清除日志文件
     */
    public function clean()
    {
        if (file_exists($this->logfile))
            unlink($this->logfile);
        if (file_exists($this->logfile . ".wf"))
            unlink($this->logfile . ".wf");
    }

    /**
     * 生成32位的logid，最高位始终为1
     * 生成规则：使用当前时间的秒数和微妙数
     * @return int
     */
	private static function __logId()
	{
        if (isset($_POST['reqId']) && !empty(trim($_POST['reqId']))) {
            return trim($_POST['reqId']);
        }
        if (isset($_GET['reqId']) && !empty(trim($_GET['reqId']))) {
            return trim($_GET['reqId']);
        }
		$arr = gettimeofday();
		return ((($arr['sec']*100000 + $arr['usec']/10) & 0x7FFFFFFF) | 0x80000000);
	}
}
/* vim: set ts=4 sw=4 sts=4 tw=90 noet: */
?>
