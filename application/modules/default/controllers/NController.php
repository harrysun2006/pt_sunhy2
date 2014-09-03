<?php

/**
 * 姓名定向首页
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class NController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
    	$this->commonMeta();
	}
	
	public function __call($m, $p)
	{
		$params = $this->getRequest()->getParams();
		$n = trim(urldecode($params['action']));
		
		if (strlen($n)) {
			$user = Better_User::getInstance();
			$userInfo = $user->getUserByNickname($n);
			
			if ($userInfo['uid']) {
				header('Location: '.BETTER_BASE_URL.'/'.$userInfo['username']);
			} else {
				throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
			}
		} else {
			throw new Zend_Controller_Action_Exception($this->lang->error->user->not_found, 404);
		}
		exit(0);
	}
}