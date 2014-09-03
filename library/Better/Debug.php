<?php

/**
 * dump调试信息
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Debug
{
	
	public static function dump(&$data, $exit=false)
	{
		$sess = Better_Registry::get('sess');
		
		if ($sess) {
			$sessUid = $sess->get('uid');
			if ($sessUid==Better_Config::getAppConfig()->debug_uid) {
				Zend_Debug::dump($data);
				if ($exit==true) {
					exit(0);
				}
			}
		}
	}
}