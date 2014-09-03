<?php

/**
 * 后台POI点评管理
 * 
 * @package Controllers
 * @author yangl
 * 
 */

class Admin_PoitipsController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/poitips.js?ver='.BETTER_VER_CODE);
		$this->view->title="POI点评管理";	
		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$params['checked'] = '1';
		$params['type'] = 'tips';//poi 点评
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
	

}