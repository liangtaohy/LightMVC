<?php
/**
 *
 * 将所有已经assign的值都以json的形式输出出来
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */
function smarty_function_bd_json_dump($params, Smarty_Internal_Template $obj)
{
    echo json_encode($obj->getTemplateVars());
}
?>
