<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_UserlogController extends Better_Controller_Admin
{
	public function init()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/userlog.js?ver='.BETTER_VER_CODE);
		$this->view->title="操作用户查询";	
		
		parent::init();	
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$result = Better_Admin_Userlog::getAll($params);
		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];				
	}
}