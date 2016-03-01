<?php

/**
 * 视图工厂
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/25 16:34
 */
class ViewFactory
{
    /**
     * Smarty instance
     * @var Smarty
     */
    private static $smarty = null;

    /**
     * 获取Smarty实例
     * @return Smarty
     */
    public static function getSmartyInstance()
    {
        if (self::$smarty === null) {
            $smarty = new Smarty();
            $smarty->setTemplateDir(SMARTY_TEMPLATE_DIR);
            $smarty->setCompileDir(SMARTY_COMPILE_DIR);
            $smarty->setConfigDir(SMARTY_CONFIG_DIR);
            $smarty->setCacheDir(SMARTY_CACHE_DIR);
            $smarty->addPluginsDir(SMARTY_PLUGIN_DIR);
            $smarty->left_delimiter = '{{';
            $smarty->right_delimiter = '}}';

            self::$smarty = $smarty;
        }

        return self::$smarty;
    }
}