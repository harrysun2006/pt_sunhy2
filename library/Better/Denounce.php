<?php

/**
 * 举报
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Denounce
{
	
	public static function &factory($what)
	{
		$class = 'Better_Denounce_'.ucfirst($what);
		$object = null;
		
		if (class_exists($class)) {
			$object = call_user_func($class.'::getInstance');
		}
		
		return $object;
	}
}