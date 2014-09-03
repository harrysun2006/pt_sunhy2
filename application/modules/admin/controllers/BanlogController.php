<?php

/**
 * 
 * @package Controllers
 * @author yangl
 * 
 */

class Admin_BanlogController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/banlog.js?ver='.BETTER_VER_CODE);
		$this->view->title="封号禁言操作查询";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$result = Better_Admin_Banaccountlog::getAll($params);
		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];		
	}
}