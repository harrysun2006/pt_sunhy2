<?php

/**
 * 版本检查
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_SystemController extends Better_Controller_Public
{

	public function init()
	{
		parent::init();
	}
		
	public function upgradeAction()
	{
		$userInfo = $this->auth();
		
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
		 */
		$platform = $this->getRequest()->getParam('platform', '');
		$model = $this->getRequest()->getParam('model', '');
		$language = $this->getRequest()->getParam('language', '');
		$ver = $this->getRequest()->getParam('ver', '');
		
		$testingMode = $this->getRequest()->getParam('testing_mode', '');
		
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
		
		$this->user->cache()->set('client', array(
			'platform' => $platform,
			'model' => $model,
			'language' => $language,
			'ver' => $ver,
			));
		
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
			
		Better_Log::getInstance()->logInfo('Platform:['.$platform.'], Model:['.$model.'], Language:['.$language.'], Ver:['.$ver.']', 'client');
			
		$this->output();
	}
	public function checksyncAction()
	{
		$this->xmlRoot = 'sync';
		$this->needPost();
		$site_username = $this->getRequest()->getParam('email', 0);
		$site_name = $this->getRequest()->getParam('site', 0);
		
		$data = Better_DAO_SyncQueue::getSyncbysiteuser($site_name,$site_username);
		if(is_array($data) && $data['uid']>0){			
			$this->data[$this->xmlRoot] = &$data;
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.sync_not_found');
		}
		$this->output();
	}
}