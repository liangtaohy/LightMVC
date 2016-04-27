<?php
/**
 * phplib公共头文件
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 14:30
 */

require_once(dirname(__FILE__) . '/config/config.php');
require_once(dirname(__FILE__) . '/config/PubConf.class.php');
require_once(dirname(__FILE__) . "/config/PubAutoLoader.php");
require_once(dirname(__FILE__) . "/testfw/IBaseTest.class.php");
require_once(dirname(__FILE__) . "/testfw/SimpleTest.class.php");
require_once(dirname(__FILE__) . "/log/MeLog.class.php");
require_once(dirname(__FILE__) . "/utils/Utils.class.php");
require_once(dirname(__FILE__) . "/utils/Validator.class.php");
require_once(dirname(__FILE__) . '/db/CiMongo/Cimongo.php');
require_once(dirname(__FILE__) . '/sms/taobao-sdk/TopSdk.php');
require_once(dirname(__FILE__) . '/framework/XdpOpenAPIErrorCodes.class.php');