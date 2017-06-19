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
 * Name:     cutGBK<br>
 * Purpose:  cut utf8 by length
 * @author   zhangtianlong@baidu.com
 * @param string
 * @param int
 * @return string
 */
function smarty_modifier_cutUTF8($str, $len,$end) { 
	$mlen = mb_strlen($str , 'utf-8');
	if ($mlen <= $len) {
		return $str;
	}	
	return mb_substr($str , 0 , $len , 'utf-8').$end;
}
?>
