<?php

/**
 * iPhone版本升级
 * 
 * @package Better.Mobile.Upgrade
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Mobile_Upgrade_Iphone extends Better_Mobile_Upgrade_Base
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
			
		$config = &self::loadConfig();
		
		$ver = $params['ver'];
		if ('1.3' == $ver ) {
			$ver = '1.3.0';
		}

		$language = $params['language'] ? $params['language'] : 'zh-cn';		
		$verNow = $config['ifn']['version']['now'];

		$int_verNow = (int)str_replace('.', '', $verNow);
		$int_ver = (int)str_replace('.', '', $ver);
		
		$ck_update = self::checkVer($ver, $verNow);
		
		if ($ck_update) {
			$verKey = str_replace('.', '', $ver);
			$result['action'] = $config['ifn']['upgrade']['action']['from']['ver_new'];
			$result['message'] = $params['lang']=='zh-cn' ? $config['ifn']['upgrade']['message']['from']['ver_new'] : $config['ifn']['upgrade']['message']['from']['ver_new_en'];
			$result['url'] = BETTER_BASE_URL . $config['ifn']['url'];
			$result['ver'] = $verNow;			
			
/*
			switch ($ver) {
				case '1.0.0':
				case '1.0.1':
				case '1.0.2':
				case '1.0.3':
				case '1.0.4':
				case '1.0.5':
				case '1.0.6':
				case '1.0.7':
				case '1.0.8':
				case '1.0.9':
				case '1.1.0':
				case '1.1.1':
				case '1.2.0':
				case '1.2.1':
					$result['action'] = $config['ifn']['upgrade']['action']['from']['ver_'.$verKey];
					$result['message'] = $params['lang']=='zh-cn' ? $config['ifn']['upgrade']['message']['from']['ver_'.$verKey] : $config['ifn']['upgrade']['message']['from']['ver_'.$verKey.'_en'];
					$result['url'] = BETTER_BASE_URL . $config['ifn']['url'];
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