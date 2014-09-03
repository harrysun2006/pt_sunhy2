<?php

/**
 * 
 * @package Controllers
 * @author yangl	
 * 
 */

class Admin_UsernameController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/resetuser.js?ver='.BETTER_VER_CODE);
		$this->view->title="用户名管理";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		
		$result=Better_Admin_User::getUsers($params);
		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	
}