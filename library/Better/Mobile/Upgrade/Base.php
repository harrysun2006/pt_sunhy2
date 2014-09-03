<?php

/**
 * 客户端升级基类
 * 
 * @package Better.Mobile.Upgrade
 * @author <leip@peptalk.cn>
 *
 */
class Better_Mobile_Upgrade_Base
{

	public function &loadConfig()
	{
		$cacher = Better_Cache::local();
		
		$config = APPLICATION_ENV=='production' ? $cacher->get('upgrade_config') : null;
		if (!$config) {
			$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/upgrade.ini', APPLICATION_ENV);
			$config = $config->toArray();
			$cacher->set('upgrade_config', $config);
		}
		
		return $config;
	}
	
	
	/**
	 * 比较版本号
	 * @param $ver
	 * @param $verNow
	 * @return unknown_type
	 */
	public function checkVer($ver, $verNow)
	{
		$ver_array = explode('.', $ver);
		$verNow_array = explode('.', $verNow);
		
		$flag = false;
		foreach ($verNow_array as $k=>$v) {	
			if ($v > (int)$ver_array[$k]) {
				$flag = true;		
				break;
			}
		}
		
		return $flag;
	}
}