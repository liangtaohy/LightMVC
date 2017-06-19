<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty modifier plugin
 *
 * Type:     modifier<br>
 * Name:     subByte<br>
 * Purpose:  cut utf8 by length
 * @param string
 * @param int
 * @return string
 */
function smarty_modifier_subByte($str, $len, $end="") { 
	return mb_strimwidth($str,0,$len,$end,'utf-8');
}
?>
