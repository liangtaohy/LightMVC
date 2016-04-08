<?php
/**
 * 全局配置
 * @author LiangTao (liangtaohy@163.com)
 * @date 16/2/26 16:26
 */
define('HTDOCS_PATH', __DIR__ . '/..');
define('SMARTY_PATH', HTDOCS_PATH . '/extlib/smarty/libs');
define('CLASS_EXT', '.class.php');

define('TEMPLATE_PATH', HTDOCS_PATH . '/../templates');
define('SMARTY_TEMPLATE_DIR', TEMPLATE_PATH . '/templates');
define('SMARTY_COMPILE_DIR', TEMPLATE_PATH . '/templates_c');
define('SMARTY_CONFIG_DIR', TEMPLATE_PATH . '/config');
define('SMARTY_CACHE_DIR', TEMPLATE_PATH . '/cache');
define('SMARTY_PLUGIN_DIR', TEMPLATE_PATH . '/plugins');