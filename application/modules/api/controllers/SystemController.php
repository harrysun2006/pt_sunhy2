<?php

/**
 * 版本检查
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_SystemController extends Better_Controller_Api
{

	public function init()
	{
		parent::init();
	}
		
	public function upgradeAction()
	{
		$this->xmlRoot = 'version';
		/*
		 * 0 windows
		 * 1 Symbian S60
		 * 2 Symbian UIQ
		 * 3 PocketPC
		 * 4 SmartPhone
		 * 5 BREW
		 * 6 PalmOS
		 * 7 J2ME
		 * 11 Blackberry
		 */
		$platform = $this->getRequest()->getParam('platform', '');
		$model = $this->getRequest()->getParam('model', '');
		$language = $this->getRequest()->getParam('language', '');
		$ver = $this->getRequest()->getParam('ver', $this->getRequest()->getParam('version', ''));
		$testingMode = $this->getRequest()->getParam('testing_mode', '');
		$partner = $this->getRequest()->getParam('kai_partner', ''); //
		$secret = $this->getRequest()->getParam('secret', ''); //		
		
		//Log
		if ($secret && $partner) {
			$_platforms = array(
								'ppc' => 3,
								'ifn' => 8,
								's60' => 'S60',
								'and' => 10,
								'bb' => 11,
								);
			
			$imei = Better_Imei::decrypt($secret);
			$data = array();
			$data['secret'] = $secret;
			$data['imei'] = $imei;
			if (substr($partner, 0, 2) == 'BB') {
				$data['platform'] = substr($partner, 0, 2);
				$data['partner'] = substr($partner, 2);					
			} else {
				$data['platform'] = substr($partner, 0, 3);
				$data['partner'] = substr($partner, 3);					
			}
			$data['platform'] = $_platforms[strtolower($data['platform'])];
			
			$data['dateline'] = time();
			$data['ip'] = Better_Functions::getIP();
			if(Better_DAO_SecretLog::getInstance()->getByFiled($imei)){
				$data['isfirsttime']=0;
			}else{
				$data['isfirsttime']=1;
			}
			Better_DAO_SecretLog::getInstance()->insert($data);
		}
		
		
		if ($testingMode) {
			$result = array(
				'action' => $testingMode,
				'message' => '['.$testingMode.'] testing upgrade',
				'code' => rand(1000,9999),
				'url' => BETTER_BASE_URL.'/files/clients/better_3rd.sisx',
				'ver' => '9.0.0',
				);
		} else {
			$result = Better_Mobile_Upgrade::getInstance()->parse(array(
				'lang' => $this->langKey,
				'platform' => $platform,
				'model' => $model,
				'language' => $language,
				'ver' => $ver
				));
		}
		$this->data[$this->xmlRoot] = array(
			'action' => $result['action'],
			'platform' => $platform,
			'model' => $model,
			'language' => $language,
			'ver' => $result['ver'],
			'message' => $result['message'],
			'code' => $result['code'],
			'url' => $result['url'],
			);
			
		$this->output();
	}
	
}