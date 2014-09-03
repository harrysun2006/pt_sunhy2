<?php

/**
 * API相关的服务分配
 * 
 * @package Better.Api
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Allocate
{
	
	/**
	 * 分配
	 * 
	 * @param integer $uid
	 * @return array
	 */
	public static function allocate($uid)
	{
		$result = array();
		
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED==true) {
			$result['ppns'] = Better_Ppns::getInstance()->allocPpns($uid);
			$result['pts'] = Better_Ppns::getInstance()->allocPts($uid);
			
			$maps = self::allocMapService($uid);
			$result['map'] = $maps['map'];
			$result['map64'] = $maps['map64'];
		}		
		
		return $result;
	}
	
	/**
	 * 分配地图服务
	 * 
	 * @param unknown_type $uid
	 * @return string
	 */
	protected static function allocMapService($uid=0)
	{
		$urls = array(
			'map' => '',
			'map64' => '',
			);
			
		$mapUrls = explode('|', Better_Config::getAppConfig()->map_service->map->url);
		$map64Urls = explode('|', Better_Config::getAppConfig()->map_serviec->map64->url);
		
		$urls['map'] = $mapUrls[rand(0, count($mapUrls)-1)];
		$urls['map64'] = $mapUrls[rand(0, count($map64Urls)-1)];
		
		return $urls;
	}
}