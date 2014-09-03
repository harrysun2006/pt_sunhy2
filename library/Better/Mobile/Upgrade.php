<?php

/**
 * 客户端升级
 * 
 * @package Better.Mobile
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Mobile_Upgrade
{
	private static $instance = null;
	private $config = array();
	
	private function __construct()
	{
		$this->config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/upgrade.ini', APPLICATION_ENV);	
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}	
		
		return self::$instance;
	}
	
	public function parse(array $params)
	{
		switch (strtolower($params['platform'])) {
			case '11':
				$result = Better_Mobile_Upgrade_Blackberry::parse($params);
				break;
			case 's60':
			case '1':
				$result = Better_Mobile_Upgrade_S60::parse($params);
				break;
			case 'and':
			case '10':
				$result = Better_Mobile_Upgrade_Android::parse($params);
				break;
			case '8':
			case 'ifn':
				$result = Better_Mobile_Upgrade_Iphone::parse($params);
				break;
			case '3':
			case 'wm':
				$result = Better_Mobile_Upgrade_Wm::parse($params);
				break;
			default:
				$result = array(
					'action' => 'none',
					'code' => '',
					'url' => '',
					'message' => '',
					);
					
				$platform = strtolower($params['platform']);
				$model = $params['model'];
				$ver = $params['ver'];
				$language = $params['language'] ? $params['language'] : 'zh-cn';				
				break;
		}
		
		if ($result['action']== 'none' || !$result['action']) 
			$result['message'] = '您的版本已经是最新版本';
		return $result;
	}
}