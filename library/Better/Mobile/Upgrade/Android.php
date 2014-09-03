<?php

/**
 * Android版本升级
 * 
 * @package Better.Mobile.Upgrade
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Mobile_Upgrade_Android extends Better_Mobile_Upgrade_Base
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
		$ver = substr($ver, 1);
		list($ver, $foo) = explode('(', $ver);

		$language = $params['language'] ? $params['language'] : 'zh-cn';		
		$verNow = $config['and']['version']['now'];

		$int_verNow = (int)str_replace('.', '', $verNow);
		$int_ver = (int)str_replace('.', '', $ver);
		
		if ($int_ver < $int_verNow) {
			$verKey = str_replace('.', '', $ver);
			
			$result['action'] = $config['and']['upgrade']['action']['from']['ver_new'];
			$result['message'] = ($params['lang']=='zh-cn' || $params['lang']=='zh') ? $config['and']['upgrade']['message']['from']['ver_new'] : $config['and']['upgrade']['message']['from']['ver_new_en'];
			$result['url'] = BETTER_BASE_URL . $config['and']['url'];
			$result['ver'] = $verNow;
					
			/*switch ($ver) {
				case '1.0.0':
				case '1.0.1':
				case '1.0.2':
				case '1.0.3':
				case '1.0.4':
					$result['action'] = $config['and']['upgrade']['action']['from']['ver_'.$verKey];
					$result['message'] = $params['lang']=='zh-cn' ? $config['and']['upgrade']['message']['from']['ver_'.$verKey] : $config['and']['upgrade']['message']['from']['ver_'.$verKey.'_en'];
					$result['url'] = BETTER_BASE_URL . $config['and']['url'];
					$result['ver'] = $verNow;
					break;
				default:
					$result['action'] = '';
					$result['code'] = '';
					$result['url'] = '';
					$result['message'] = '';
					break;
			}*/
		}
				
		return $result;		
	}
}