<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_DeletedController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/deleted.js?ver='.BETTER_VER_CODE);
		$this->view->title="已删除微博管理";		

		$todayFrom = Better_Functions::date('Y-m-d', BETTER_NOW);
		$todayTo = Better_Functions::date('Y-m-d', BETTER_NOW+3600*24);
		
		$this->view->headScript()->appendScript("
    	var Better_Today_From = '{$todayFrom}';
    	var Better_Today_To = '{$todayTo}';
    	");		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$result = Better_Admin_BlogDeleted::getBlogs($params);

		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];		
	}
	
	public function delAction()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_BlogDeleted::delBlogs($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);
	}	
	
	public function restoreAction()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_BlogDeleted::restore($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);		
	}
}