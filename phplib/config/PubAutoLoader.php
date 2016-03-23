<?php
/**
 * 默认类加载器
 * 默认类文件的文件名格式为：ClassName.class.php
 * 类文件的后缀可以通过常量CLASS_EXT进行修改
 * ==========
 * 加载规则说明
 * 检查PubClassManager中是否存在该类的映射，如果存在，则直接加载
 * 否则，扫描include path，查找类文件是否存在。如果存在，则加载
 * 加载外部代码库统一使用方法：require_once
 * ==========
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/18 17:21
 */
require_once(dirname(__FILE__) . "/PubClassManager.class.php");
require_once(dirname(__FILE__) . "/PubConf.class.php");

function _pub_auto_loader($clssname)
{
    $clzMap = PubClassManager::getInstance()->getRegisterClassMap();

    if (!empty($clzMap) && array_key_exists($clssname, $clzMap)) {
        require_once($clzMap[$clssname]);
    } else {
        if (defined(CLASS_EXT)) {
            $clssfile = $clssname . CLASS_EXT;
        } else {
            $clssfile = $clssname . ".class.php";
        }

        $include_paths = explode(":", get_include_path());

        foreach ($include_paths as $ipath) {
            $file = rtrim($ipath, "/") . "/" . $clssfile;
            if (file_exists($file)) {
                require_once($file);
            }
        }
    }
}

spl_autoload_register(_pub_auto_loader);