<?php

/**
 * Session 工厂
 *
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Session
{

	public static function factory($namespace='front')
	{
		if ($namespace=='front') {
			// 前台session
			include_once dirname(__FILE__).'/Session/Front.php';
			$className = 'Better_Session_Front';
		} else {
			// 后台session
			include_once dirname(__FILE__).'/Session/Admin.php';
			$className = 'Better_Session_Admin';
		}
		 
		$sess = new $className();		
		return $sess;
	}
	
}

?>