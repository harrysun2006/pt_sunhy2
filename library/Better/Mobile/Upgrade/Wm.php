<?php

/**
 * Windows Mobile 升级
 * 
 * @package Better.Mobile.Upgrade
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Mobile_Upgrade_Wm extends Better_Mobile_Upgrade_Base
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
		$tmp = explode('(', $ver);
		$ver = $tmp[0];	
			
		$language = $params['language'] ? $params['language'] : 'zh-cn';		
		$verNow = $config['ppc']['version']['now'];

		$int_verNow = (int)str_replace('.', '', $verNow);
		$int_ver = (int)str_replace('.', '', $ver);
		
		if ($int_ver < $int_verNow) {
			$verKey = str_replace('.', '', $ver);
			$result['action'] = $config['ppc']['upgrade']['action']['from']['ver_new'];
			$result['message'] = $params['lang']=='zh-cn' ? $config['ppc']['upgrade']['message']['from']['ver_new'] : $config['ppc']['upgrade']['message']['from']['ver_new_en'];
			$result['url'] = BETTER_BASE_URL . $config['ppc']['url'];
			$result['ver'] = $verNow;			
			
/*			
			switch ($ver) {
				case '1.00':
				case '1.10':
				case '1.20':
				case '1.30':
				case '1.31':
				case '1.40':
				case '1.50':
					$result['action'] = $config['ppc']['upgrade']['action']['from']['ver_'.$verKey];
					$result['message'] = $params['lang']=='zh-cn' ? $config['ppc']['upgrade']['message']['from']['ver_'.$verKey] : $config['ppc']['upgrade']['message']['from']['ver_'.$verKey.'_en'];
					$result['url'] = BETTER_BASE_URL . $config['ppc']['url'];
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