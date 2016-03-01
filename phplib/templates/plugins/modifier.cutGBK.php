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
 * Purpose:  cut gbk by length
 * @author   zhangtianlong@baidu.com
 * @param string
 * @param int
 * @return string
 */
function smarty_modifier_cutGBK($str, $len,$end) { 
	$strlen = strlen($str);
	if ($strlen <= $len) { 
		return $str;
	}
	$i = 0; 
	$cut_len = 0;
	while($i < $strlen && $cut_len < $len) { 

		if (ord($str{$i}) > 0X80) { 
			$i += 2;
		} else {
			$i++;   
		}       
		$cut_len++;
	}
	return substr($str, 0 , $i).$end;
}
?>
