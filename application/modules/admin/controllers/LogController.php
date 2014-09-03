<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_LogController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/log.js?ver='.BETTER_VER_CODE);
		$this->view->title="操作查询";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$result = Better_Admin_Log::getAll($params);
		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];		
	}
}