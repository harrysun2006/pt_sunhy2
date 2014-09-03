<?php

/**
 * 版本检查
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_VersionController extends Better_Controller_Api
{
	
	public function __destruct()
	{
	}
		
	public function init()
	{
		parent::init();
	}
		
	public function indexAction()
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
		 */
		$platform = $this->getRequest()->getParam('platform');
		
		$this->data[$this->xmlRoot] = array(
			'action' => 'none',
			'platform' => '',
			'model' => '',
			'language' => '',
			'ver' => '',
			'message' => '',
			'code' => '',
			'url' => '',
			);
			
		$this->output();
	}
}