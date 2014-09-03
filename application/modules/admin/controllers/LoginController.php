<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_LoginController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init(false);	
	}
	
	public function indexAction()
	{
		$this->view->error = '登录失败';
		
		$username = $this->getRequest()->getParam('name', '');
		$password = $this->getRequest()->getParam('pwd', '');
		
		if ($username!='' && $password!='') {
			$user = Better_Admin_Administrators::login($username, $password);
			if ($user instanceof Better_Admin_Administrators) {
				$this->_helper->getHelper('Redirector')->gotoUrl($this->view->admin_url.'/home');
				exit(0);
			}
		}
	}
	
	public function logoutAction()
	{
		Better_Admin_Administrators::getInstance($this->sess->uid)->logout();
		$this->_helper->getHelper('Redirector')->gotoUrl($this->view->admin_url.'/kaiadminlogin');
		exit(0);
	}
}