<?php

/**
 * 后台用户操作查询
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_ActController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		exit;
		$this->view->headScript()->appendFile('js/controllers/admin/act.js?ver='.BETTER_VER_CODE);
		$this->view->title="用户操作查询";		
	}
	
	public function indexAction()
	{
		
	}
	
	public function menuAction()
	{
		
	}
	
	public function searchAction()
	{
		$params = $this->getRequest()->getParams();
		
		if($params['index']==1){
			$result = array('count'=>0, 'rows'=>array());
		}else{
			$result = Better_Admin_Tracelog::getAll($params);
		}
	
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
}