<?php

/**
 * 调试用Controller
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class DebugController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{

		$ns = Better_User::getInstance($this->uid)->notify()->get(array(
			'page' => 1,
			'type' => array('blog', 'friend_request', 'follow_request'),
			));
		Zend_Debug::dump($ns);
		exit(0);
	}
}
