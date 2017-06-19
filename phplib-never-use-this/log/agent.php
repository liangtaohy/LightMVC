<?php
/**
 * php-logstash base configure
 */
require __DIR__ .'/LogStash.class.php';
$cfg = [
	'redis' => 'tcp://127.0.0.1:6379',
];

$cfg_full = [
	'redis'             => 'tcp://127.0.0.1:6379',   // redis地址，支持认证不支持数组。认证tcp://auth:密码@127.0.0.1:6379
	'type'              => 'log',                     // redis 队列key,及es的index type
	'agent_log'         => __DIR__ .'/agent.log',    // 日志保存地址
	'input_sync_memory' => 5*1024*1024,               // 输入信息到达指定内存后同步
	'input_sync_second' => 5,                         // 输入信息等待超过指定秒数后同步，以上2个条件共同触发
	'parser'            => [$this,'parser'],          // 自定义输入端日志的处理格式，默认与程序提供的logformat json一致

	'elastic'           => 'http://127.0.0.1:9200',  # elastic search通信地址，支持数组,可配置多个随机访问
                                                      # 支持密码 程序采用 http auth_basic 认证方式
                                                      # 使用密码 http://user:pssword@127.0.0.1:9200
	'prefix'            => 'phplogstash',            # es 默认索引前缀名字为 phplogstash-2015.12.12
	'shards'            => '5',                      # es 分片数量
	'replicas'          => '2',                      # es 副本数量
];


(new LogStash())->handler($cfg)->run();
?>