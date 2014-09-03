<?php

/**
 * 黑莓客户端升级
 * 
 * @package Better.Mobile.Upgrade
 * @author fengj <fengj@peptalk.cn>
 *
 *5.0_v1.4.0(build 20110530)
 */
class Better_Mobile_Upgrade_Blackberry extends Better_Mobile_Upgrade_Base
{
	/**
	 * 
	 * @param $ver
	 * @return unknown_type
	 */
	public static function getModel($ver)
	{
		$model = explode('_', $ver);
		$model = str_replace('.', '', $model[0]);
		return $model;
	}
	
	
	/**
	 * 
	 * @param $ver
	 * @return unknown_type
	 */
	public static function getVer($ver)
	{
		$ver = explode('(', $ver);
		$ver = explode('_', $ver[0]);
		
		return substr($ver[1], 1);
	}
	
	public static function parse(array $params)
	{
		$result = array(
			'action' => 'none',
			'code' => '',
			'url' => '',
			'message' => '',
			'ver' => '', 
			);
					
		$model = self::getModel($params['ver']);
		switch ($model) {
			case 45:
				$result = self::parseModel45($params);
				break;
			case 46:
				$result = self::parseModel46($params);
				break;
			case 50:
			default:	
				$result = self::parseModel50($params);
				break;
		}
		
		return $result;		
	}
	
	protected static function parseModel45(array $params)
	{
		$result = array(
			'action' => 'none',
			'code' => '',
			'url' => '',
			'message' => '',
			'ver' => '',
			);		
					
		$config = &self::loadConfig();
		$platform = strtolower($params['platform']);
		$model = self::getModel($params['ver']);
		$ver = self::getVer($params['ver']);

		$language = $params['language'] ? $params['language'] : 'zh-cn';		
		$verNow = $config['bb']['m45']['version']['now'];
		
		$int_verNow = (int)str_replace('.', '', $verNow);
		$int_ver = (int)str_replace('.', '', $ver);		
		
		if ($int_ver < $int_verNow) {
			$verKey = str_replace('.', '_', $ver);

			$url = BETTER_BASE_URL . str_replace('{MODEL}', str_replace('.', '', $model), $config['bb']['m45']['url']);
			switch ($ver) {
				default:
					$result['action'] = $config['bb']['m45']['upgrade']['action'];
					$result['message'] = $params['lang']=='zh-cn' ? $config['bb']['m45']['upgrade']['message']: $config['bb']['m45']['upgrade']['message_en'];
					$result['url'] = $url;
					$result['ver'] = $verNow;					
					
					break;
			}
		}
					
		return $result;
	}
	
	protected static function parseModel46(array $params)
	{
		$result = array(
			'action' => 'none',
			'code' => '',
			'url' => '',
			'message' => '',
			'ver' => '',
			);		
					
		$config = &self::loadConfig();
		$platform = strtolower($params['platform']);
		$model = self::getModel($params['ver']);
		$ver = self::getVer($params['ver']);

		$language = $params['language'] ? $params['language'] : 'zh-cn';		
		$verNow = $config['bb']['m46']['version']['now'];
		
		$int_verNow = (int)str_replace('.', '', $verNow);
		$int_ver = (int)str_replace('.', '', $ver);		
		
		if ($int_ver < $int_verNow) {
			$verKey = str_replace('.', '_', $ver);

			$url = BETTER_BASE_URL . str_replace('{MODEL}', str_replace('.', '', $model), $config['bb']['m46']['url']);
			switch ($ver) {
				default:
					$result['action'] = $config['bb']['m46']['upgrade']['action'];
					$result['message'] = $params['lang']=='zh-cn' ? $config['bb']['m46']['upgrade']['message']: $config['bb']['m46']['upgrade']['message_en'];
					$result['url'] = $url;
					$result['ver'] = $verNow;					
					
					break;
			}
		}
					
		return $result;
	}	
	
	
	protected static function parseModel50(array $params)
	{
		$result = array(
			'action' => 'none',
			'code' => '',
			'url' => '',
			'message' => '',
			'ver' => '',
			);		
					
		$config = &self::loadConfig();
		$platform = strtolower($params['platform']);
		$model = self::getModel($params['ver']);
		$ver = self::getVer($params['ver']);

		$language = $params['language'] ? $params['language'] : 'zh-cn';		
		$verNow = $config['bb']['m50']['version']['now'];	
		
		$int_verNow = (int)str_replace('.', '', $verNow);
		$int_ver = (int)str_replace('.', '', $ver);		
		
		if ($int_ver < $int_verNow) {
			$verKey = str_replace('.', '_', $ver);
			$url = BETTER_BASE_URL . str_replace('{MODEL}', str_replace('.', '', $model), $config['bb']['m50']['url']);
			switch ($ver) {
				default:
					$result['action'] = $config['bb']['m50']['upgrade']['action'];
					$result['message'] = $params['lang']=='zh-cn' ? $config['bb']['m50']['upgrade']['message']: $config['bb']['m50']['upgrade']['message_en'];
					$result['url'] = $url;
					$result['ver'] = $verNow;					
					
					break;
			}
		}
					
		return $result;
	}		
	
}
