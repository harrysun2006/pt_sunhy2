<?php

/**
 * api翻译器
 * 
 * @package Better.Api
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator
{
	protected static $instance = array();
	
	public static function getInstance($what)
	{
		$what = strtolower($what);
		if (!isset(self::$instance[$what])) {
			$arr = explode('_', $what);
			if (count($arr)>1) {
				$arr = array_map('ucfirst', $arr);
				$class = 'Better_Api_Translator_'.implode('_', $arr);
			} else {
				$class = 'Better_Api_Translator_'.ucfirst($what);
			}
			self::$instance[$what] = new $class();
		}
		
		return self::$instance[$what];
	}
}