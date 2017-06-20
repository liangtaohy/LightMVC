<?php
/**
 * xman helper
 * User: Liang Tao (liangtaohy@163.com)
 * Date: 17/6/20
 * Time: AM10:13
 */

class Xman {
    static $commands = [
        'make:model'    => 'makeModel',
    ];

    /**
     * 生成model, Model的类名为 Model{{$modelName}}
     * @param $modelName
     * @param string $modelParam
     */
    public function makeModel($modelName, $modelParam = '')
    {
        if (empty($modelName)) {
            echo "请指定modelname!\n";
            exit(0);
        }

        $content = <<< EOF
<?php

/**
 * $modelName model base on Eloquent
 * User: Liang Tao (liangtaohy@163.com)
 * Date: 17/6/20
 * Time: AM6:48
 */
require_once APP_PATH . '/library/EloquentBoot.php';

use  Illuminate\Database\Eloquent\Model;

class Model$modelName extends Model
{
    /**
     * 表名.
     *
     * @var string
     */
    protected \$table = '';
}
EOF;

        $filename = "./models/dao/Model" . $modelName . ".class.php";
        file_put_contents($filename, $content);
        echo "make:model success, file path: \033[32m" . $filename . "\033[0m\n";
        exit(0);
    }

    public function run($command, $param)
    {
        if (isset(self::$commands[$command])) {
            call_user_func(array($this, self::$commands[$command]), $param);
        }
    }

    public function help()
    {
        echo "php xman make:model \033[32m{{modename}}\033[0m\n";
    }
}

$xman = new Xman();

$c = $argc;

if ($c == 3) {
    $command = $argv[1];
    $param = $argv[2];
    $xman->run($command, $param);
} else {
    $xman->help();
}
