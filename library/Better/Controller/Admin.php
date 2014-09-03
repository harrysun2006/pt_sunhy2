<?php

/**
 * 后台控制器
 * 
 * @package Better.Controller
 * @author leip <leip@peptalk.cn>
 *
 */
abstract class Better_Controller_Admin extends Better_Controller
{
	protected $sess = null;
	
	public function init($needLogin=true)
	{
		parent::init();
		
		$this->sess = Better_Session::factory();
		$this->sess->init();		
		
		$this->view->admin_url = BETTER_BASE_URL.'/admin';
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/admin.js');

		$needLogin==true && $this->needLogin();
		
		$this->view->userInfo = $this->sess->admin_uid ? Better_Admin_Administrators::getInstance($this->sess->admin_uid)->getInfo() : array();
	
		$this->getResponse()->setHeader('Content-Type', 'text/html; charset=utf-8');
		$this->getResponse()->sendHeaders();

		//	加载语言包
		Better_Language::load();
	}
	
	protected function needLogin()
	{
		if (!$this->sess->admin_uid) {
			$this->_helper->getHelper('Redirector')->gotoUrl($this->view->admin_url.'/kaiadminlogin');
			exit(0);
		}
	}
	
	protected function sendAjaxResult($result)
	{
		$out = array(
			'result' => $result,
			);
		$output = json_encode($out);
		//$this->getResponse()->setHeader('Content-Length', strlen($output));
		//$this->getResponse()->setHeader('Content-Type', 'application/json');

		$this->getResponse()->sendHeaders();
		
		echo $output;
		exit(0);
	}
}