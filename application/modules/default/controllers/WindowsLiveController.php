<?php

class WindowsLiveController extends Better_Controller_Front 
{

	public function init()
	{
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 1);
		parent::init();	
	}
	
	public function indexAction()
	{
		phpinfo();
		$this->output();
	}
	
	protected function output()
	{
		exit(0);
	}
}