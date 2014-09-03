<?php

/**
 * S60客户端升级
 * 
 * @package Better.Mobile.Upgrade
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Mobile_Upgrade_S60 extends Better_Mobile_Upgrade_Base
{
	
	public static function parse(array $params)
	{
		$result = array(
			'action' => 'none',
			'code' => '',
			'url' => '',
			'message' => '',
			'ver' => '',
			);		
		$model = substr(intval($params['model']), 0, 1);
		
		switch ($model) {
			case 2:
				$result = self::parseModel2($params);
				break;
			case 3:
				$result = self::parseModel3($params);
				break;
			case 5:
				$result = self::parseModel5($params);
				break;
		}
		
		return $result;		
	}
	
	protected static function parseModel2(array $params)
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
		$model = $params['model'];
		$ver = sprintf('%.2f', $params['ver']);
		$ver = $params['ver'];
		$language = $params['language'] ? $params['language'] : 'zh-cn';		
		$verNow = $config['s60']['version']['now'];
		
		$int_verNow = (int)str_replace('.', '', $verNow);
		$int_ver = (int)str_replace('.', '', $ver);
		
		if ($int_ver < $int_verNow) {
			$verKey = str_replace('.', '_', $ver);
			$url = BETTER_BASE_URL . str_replace('{MODEL}', str_replace('.', '', $model), $config['s60']['url']);
			$result['action'] = $config['s60']['upgrade']['action']['from']['ver_new'];
			$result['message'] = $params['lang']=='zh-cn' ? $config['s60']['upgrade']['message']['from']['ver_new'] : $config['s60']['upgrade']['message']['from']['ver_new_en'];
			$result['url'] = $url;
			$result['ver'] = $verNow;			
			
			/*			
			switch ($ver) {
				case '1.00':
				case '1.01':
				case '1.10':
				case '1.11':
				case '1.12':
				case '1.20':
				case '1.21':
				case '1.22':
				case '1.30':	
					$result['action'] = $config['s60']['upgrade']['action']['from']['ver_'.$verKey];
					$result['message'] = $params['lang']=='zh-cn' ? $config['s60']['upgrade']['message']['from']['ver_'.$verKey] : $config['s60']['upgrade']['message']['from']['ver_'.$verKey.'_en'];
					$result['url'] = $url;
					$result['ver'] = $verNow;
					break;
				default:
					$result['action'] = '';
					$result['code'] = '';
					$result['url'] = '';
					$result['message'] = '';
					break;
			}
			*/
			
		}
					
		return $result;
	}
	
	protected static function parseModel3(array $params)
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
		$model = $params['model'];
		$ver = sprintf('%.2f', $params['ver']);
		$ver = $params['ver'];
		$language = $params['language'] ? $params['language'] : 'zh-cn';		
		$verNow = $config['s60']['m3']['version']['now'];
		
		$int_verNow = (int)str_replace('.', '', $verNow);
		$int_ver = (int)str_replace('.', '', $ver);
		
		if ($int_ver < $int_verNow) {
			$verKey = str_replace('.', '_', $ver);

			$url = BETTER_BASE_URL . str_replace('{MODEL}', '30', $config['s60']['m3']['url']);
			$result['action'] = $config['s60']['m3']['upgrade']['action']['from']['ver_new'];
			$result['message'] = $params['lang']=='zh-cn' ? $config['s60']['m3']['upgrade']['message']['from']['ver_new'] : $config['s60']['m3']['upgrade']['message']['from']['ver_new_en'];
			$result['url'] = $url;
			$result['ver'] = $verNow;			
			
			/*
			switch ($ver) {
				case '1.00':
				case '1.01':
				case '1.10':
				case '1.11':
				case '1.12':
				case '1.20':
				case '1.21':
				case '1.22':
				case '1.30':					
					$result['action'] = $config['s60']['m3']['upgrade']['action']['from']['ver_'.$verKey];
					$result['message'] = $params['lang']=='zh-cn' ? $config['s60']['m3']['upgrade']['message']['from']['ver_'.$verKey] : $config['s60']['m3']['upgrade']['message']['from']['ver_'.$verKey.'_en'];
					$result['url'] = $url;
					$result['ver'] = $verNow;
					break;
				default:
					$result['action'] = '';
					$result['code'] = '';
					$result['url'] = '';
					$result['message'] = '';
					break;
			}
			*/
		}
					
		return $result;		
	}
	
	protected static function parseModel5(array $params)
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
		$model = $params['model'];	
		
		$ver = sprintf('%.2f', $params['ver']);
		$ver = $params['ver'];
		$language = $params['language'] ? $params['language'] : 'zh-cn';		
		$verNow = $config['s60']['m5']['version']['now'];
		
		$int_verNow = (int)str_replace('.', '', $verNow);
		$int_ver = (int)str_replace('.', '', $ver);
		
		if ($int_ver < $int_verNow) {
			$verKey = str_replace('.', '_', $ver);

			$url = BETTER_BASE_URL . str_replace('{MODEL}', str_replace('.', '', $model), $config['s60']['m5']['url']);			
			$result['action'] = $config['s60']['m5']['upgrade']['action']['from']['ver_new'];
			$result['message'] = $params['lang']=='zh-cn' ? $config['s60']['m5']['upgrade']['message']['from']['ver_new'] : $config['s60']['m5']['upgrade']['message']['from']['ver_new_en'];
			$result['url'] = $url;
			$result['ver'] = $verNow;			
			
			/*
			switch ($ver) {
				case '1.00':
				case '1.10':
				case '1.20':
					$result['action'] = $config['s60']['m5']['upgrade']['action']['from']['ver_'.$verKey];
					$result['message'] = $params['lang']=='zh-cn' ? $config['s60']['m5']['upgrade']['message']['from']['ver_'.$verKey] : $config['s60']['m5']['upgrade']['message']['from']['ver_'.$verKey.'_en'];
					$result['url'] = $url;
					$result['ver'] = $verNow;
					break;
				default:
					$result['action'] = '';
					$result['code'] = '';
					$result['url'] = '';
					$result['message'] = '';
					break;
			}
			*/
		}
					
		return $result;		
	}
}
