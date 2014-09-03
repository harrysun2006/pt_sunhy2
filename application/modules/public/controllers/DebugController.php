<?php

/**
 * API调试
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_DebugController extends Better_Controller_Public
{

	public function init()
	{
		parent::init();
	}
		
	public function aesencAction()
	{
		$body = $this->getRequest()->getRawBody();
		die(Better_Mobile_Contacts::enc($body));
	}
	
	public function aesdecAction()
	{
		$body = $this->getRequest()->getRawBody();
		die(Better_Mobile_Contacts::decrpt($body));
	}
	
	public function dezipAction()
	{
		$d = $this->getRequest()->getParam('d', 0);
		$str = 'hahahahahahaha';

		ob_start('ob_gzhandler');


		echo $str;

		exit(0);		
	}
}