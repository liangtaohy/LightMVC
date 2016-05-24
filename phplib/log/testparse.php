<?php

/**
 * Created by PhpStorm.
 * User: xlegal
 * Date: 16/5/19
 * Time: PM2:42
 */

$message = array(
    'log_tag' => '',
    'timestamp'  => '',
    'errno' => '',
    'client_ip' => '',
    'logid' => '',
    'sid' => '',
    'reqid' => '',
    'uid' => '',
    'role' => '',
    'uri' => '',
    'time_used' => '',
    'method' => '',
    'request_body' => '',
    'result' => '',
    'upstream' => '',
    'host' => '',
    'api' => '',
    'cost' => '',
    'end' => '',
    'start' => '',
    'response' => '',
    'params' => '',
    'notify' => '',
    'level' => '',
    'reason' => '',
    'msg' => '',
);
$log = 'NOTICE: 05-19 15:56:07: [Application.class.php:48] errno[0] ip[118.194.242.107] logid[2708705824] sid[sdbp7akemtk45vk0j4inqtwi30-9352] reqid[asfasfasffdasf] uri[/order/api/v1/list] time_used[22] uid[0000000000000000000000000000000000000000] role[ft,crm_admin] time[16] method[POST] request[{"sid":"sdbp7akemtk45vk0j4inqtwi30-9352","order_no":"2016051018200029796412","reqId":"asfasfasffdasf"}] result[null]';
$log = 'FATAL: 05-19 06:38:01: [PageGoPayLogic.class.php:67] errno[0] ip[127.0.0.1] logid[3655096840] sid[0] reqid[0] uri[/order/api/internal/v1/walletwebhook] time_used[12] notify level[HIGH] method[PageGoPayLogic::handleGoPayResponse] msg[order_was_paid: 0000000000000000000000000000000000000000;20160518210273503883;2016051812200073503808;1;1463577338;1463577338]';
//$log = preg_quote(trim($log));
$pattern = '/^([A-Z]+): (\d\d-\d\d \d\d:\d\d:\d\d): .*? errno\[(\d+)\] ip\[(\d+\.\d+\.\d+\.\d+)\] logid\[(\d+)\] sid\[([a-zA-Z0-9\-]+)\] reqid\[([a-zA-Z0-9\-]+)\] uri\[([a-zA-Z0-9\/]+).*?\] time_used\[(\d+)\] (.*)/';
$matches = array();
$ret = preg_match_all($pattern, $log, $matches);

if ($ret) {
    $message['log_tag'] = $matches[1][0];
    $message['timestamp'] = $matches[2][0];
    $message['errno'] = $matches[3][0];
    $message['client_ip'] = $matches[4][0];
    $message['logid'] = $matches[5][0];
    $message['sid'] = $matches[6][0];
    $message['reqid'] = $matches[7][0];
    $message['url'] = $matches[8][0];
    $message['time_used'] = $matches[9][0];
    $extra = $matches[10][0];
    unset($matches);
    if ($message['log_tag'] == 'NOTICE') {
        $np = '/^uid\[([A-Z0-9]+)\] role\[([a-zA-Z0-9\-\_,]+)\] (.*)/';
        $ret = preg_match_all($np, $extra, $matches);
        var_dump($ret);
        if ($ret) {
            $message['uid'] = $matches[1][0];
            $message['role'] = $matches[2][0];
            $extra = $matches[3][0];
        }
    }

    unset($matches);
    $np = '/^time\[[0-9]+\] method\[([a-zA-Z]+)\] (.*)/';
    $ret = preg_match_all($np, $extra, $matches);
    if ($ret) {
        $message['method'] = $matches[1][0];
        $extra = $matches[2][0];
    }
    unset($matches);
    var_dump($extra);
    $np = '/^notify level\[([a-zA-Z]+)\] method\[([a-zA-Z:]+)\] msg\[(.*)?\].*/';
    $ret = preg_match_all($np, $extra, $matches);
    if ($ret) {
        $message['notify'] = 1;
        $message['level'] = $matches[1][0];
        $message['method'] = $matches[2][0];
        $message['msg'] = $matches[3][0];
    }
}

var_dump($message);

?>