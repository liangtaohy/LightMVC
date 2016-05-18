<?php
require_once (dirname(__FILE__) . '/ISmsInterface.class.php');
require_once (dirname(__FILE__) . '/taobao-sdk/TopSdk.php');

/**
 * 阿里大鱼SDK封装
 * User: liangtaohy@163.com
 * Date: 16/4/25
 * Time: PM11:12
 */
class ALiDaYuSDK implements ISmsInterface
{
    // 正式码,耀龙提供

    const SMS_TEST = '大鱼测试';
    const SMS_VCODE = '变更验证';

    const IMPLODE_GLUE = ",";

    const MAX_NUM_MOBILES = 200;

    private $client;

    private static $inst = null;

    public static $templateIds = array(
        self::SMS_TEST   => 'SMS_8240044',
        self::SMS_VCODE     => 'SMS_8240038', // 信息变更验证
    );

    /**
     * 单例
     * @return ALiDaYuSDK|null
     */
    public static function getInstance($appkey, $appsecret)
    {
        if (self::$inst === null) {
            self::$inst = new self($appkey, $appsecret);
        }

        return self::$inst;
    }

    /**
     * 构造函数
     */
    private function __construct($appkey, $appsecret)
    {
        $this->client = new TopClient;
        $this->client->appkey = $appkey;
        $this->client->secretKey = $appsecret;
        $this->client->format = 'json'; // 要求返回json格式数据
    }

    /**
     * 发送测试短信
     * @param array $rev_nums 接收号码列表
     * @param $product 产品线
     * @return mixed
     */
    public function smstest(array $rev_nums, $customer = '泪之痕', $extend = 'test123')
    {
        $req = new AlibabaAliqinFcSmsNumSendRequest;

        $total = count($rev_nums);

        if ($total < 1 || $total > self::MAX_NUM_MOBILES) {
            throw new XdpOpenAPIException(XDPAPI_EC_SMS_TOO_MANY_RECVS, null, $total);
        }

        $rev_str = implode(self::IMPLODE_GLUE, $rev_nums);
        $param = array(
            'customer'  => $customer,
        );
        $req->setExtend($extend);
        $req->setSmsType("normal");
        $req->setSmsFreeSignName(self::SMS_TEST);
        $req->setSmsParam(json_encode($param));
        $req->setRecNum($rev_str);
        $req->setSmsTemplateCode(self::$templateIds[self::SMS_TEST]);
        $resp = $this->client->execute($req);
        
        if (isset($resp->code)) { // error
            MeLog::warning('sms code[' . $resp->code . ']' . ' errmsg[' . json_encode($resp) . ']');
            throw new XdpOpenAPIException(XDPAPI_EC_SMS_INTERNAL_ERROR, null, $resp->msg);
        }

        return XDPAPI_EC_SUCCESS; // success
    }

    /**
     * 发送短信验证码
     * @param array $rev_nums 接收号码列表
     * @param $code 验证码
     * @param $product  产品线
     * @param $vcode 验证码
     * @return mixed
     */
    public function sendvcode($rev_num, $product, $vcode, $extend = 'vcode1234')
    {
        $now = Utils::microTime();

        $req = new AlibabaAliqinFcSmsNumSendRequest;

        $param = array(
            'code'  => $vcode,
            'product'   => $product,
        );

        $req->setExtend($extend);
        $req->setSmsType("normal");
        $req->setSmsFreeSignName(self::SMS_VCODE);
        $req->setSmsParam(json_encode($param));
        $req->setRecNum($rev_num);
        $req->setSmsTemplateCode(self::$templateIds[self::SMS_VCODE]);
        $resp = $this->client->execute($req);

        $end = Utils::microTime();
        if (isset($resp->code)) { // error
            MeLog::warning('sms code[' . $resp->code . ']' . ' errmsg[' . json_encode($resp) . ']');
            MeLog::notice(sprintf('sms method[%s] cost [%d] code[%d] errmsg[%s]', __METHOD__, $end - $now, $resp->code, serialize($resp)));
            throw new XdpOpenAPIException(XDPAPI_EC_SMS_INTERNAL_ERROR, null, $resp->msg);
        }

        MeLog::notice(sprintf('sms method[%s] cost [%d] code[0] errmsg[%s]', __METHOD__, $end - $now, serialize($resp)));
        return XDPAPI_EC_SUCCESS; // success
    }

    /**
     * 发送短信通知
     * @param $rev_num
     * @param array $params
     * @param $signname
     * @param $tplid
     * @param string $extend
     * @return int
     * @throws XdpOpenAPIException
     */
    public function sendsms($rev_num, array $params, $signname, $tplid, $extend = 'liangtao')
    {
        $now = Utils::microTime();
        $req = new AlibabaAliqinFcSmsNumSendRequest;

        $req->setExtend($extend);
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($signname);
        $req->setSmsParam(json_encode($params));
        $req->setRecNum($rev_num);
        $req->setSmsTemplateCode($tplid);
        $resp = $this->client->execute($req);

        $end = Utils::microTime();
        if (isset($resp->code)) { // error
            MeLog::warning('sms code[' . $resp->code . ']' . ' errmsg[' . json_encode($resp) . ']');
            MeLog::notice(sprintf('sms method[%s] cost [%d] code[%d] errmsg[%s]', __METHOD__, $end - $now, $resp->code, serialize($resp)));
            return XDPAPI_EC_SMS_INTERNAL_ERROR;
        }

        MeLog::notice(sprintf('sms method[%s] cost [%d] code[0] errmsg[%s]', __METHOD__, $end - $now, serialize($resp)));
        return XDPAPI_EC_SUCCESS; // success
    }
}