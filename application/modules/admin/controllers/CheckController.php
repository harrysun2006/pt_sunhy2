<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_CheckController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/check.js?ver='.BETTER_VER_CODE);
		$this->view->title="待审核微博管理";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$params['checked'] = '0';
		$result = Better_Admin_Blog::getBlogs($params);
		
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
			Better_Admin_Blog::delBlogs($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);
	}
	
	public function resetplaceAction()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Blog::resetPlace($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);		
	}
	

	public function resetplace2Action()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Blog::resetPlace2($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);		
	}
	
	public function delattachAction()
	{
		$result = 0;	
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Blog::delAttach($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);				
	}

	public function checkAction()
	{
		$result = 0;	
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Blog::passCheck($bids) && $result = 1;
		}
		
		$this->sendAjaxResult($result);		
	}
	

}