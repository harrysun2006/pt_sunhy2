<?php

/**
 * 获取勋章缓存
 * 
 * @package Better.Cache.Module
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Cache_Module_Badge
{
	
	public static function load()
	{
		$cacher = Better_Cache::remote();
		
		$badgeKey = 'kai_badges';
		$data = array();
		
		if (!$cacher->get($badgeKey)) {
			$data = Better_DAO_Badge::getInstance()->getAllAvailable();
			$cacher->set($badgeKey, $data);
		}		
		
		return $data;
	}
}