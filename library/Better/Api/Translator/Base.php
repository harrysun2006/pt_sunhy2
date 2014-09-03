<?php

/**
 * API数据格式转换
 * 
 * @package Better.Api.Translate
 * @author leip <leip@peptalk.cn>
 *
 */
abstract class Better_Api_Translator_Base
{
	abstract public function &translate(array $params);
	
	public static function time($time)
	{
		$str = '';
		
		if ($time && preg_match('/^([0-9]{10})$/', $time)) {
			$str = date('Ymd', $time).'T'.date('Hi', $time).'Z';
		}
		
		return $str;
	}
}