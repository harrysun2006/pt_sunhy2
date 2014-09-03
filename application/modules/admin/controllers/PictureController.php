<?php

/**
 * 
 * @package Controllers
 * @author yangl
 * 
 */

class Admin_PictureController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();
		
		$this->view->headScript()->appendFile('js/controllers/admin/picture.js?ver='.BETTER_VER_CODE);
		$this->view->title="所有图片";		

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
		$params['checked'] = '';
		$params['page_size'] = isset($params['page_size'])? $params['page_size'] : 50;
		
		$result = Better_Admin_Allpicture::getImageLog($params);

		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	public function searchAction()
	{
		$params = $this->getRequest()->getParams();
		$params['checked'] = '';
		$params['page_size'] = isset($params['page_size'])? $params['page_size'] : 50;
		
		$params['from']= $params['from']? $params['from'] : date('Y-m-d', time()-BETTER_ADMIN_DAYS+BETTER_8HOURS);
		$params['to']= $params['to']? $params['to']: date('Y-m-d', time()+BETTER_8HOURS);
		
		$result = Better_Admin_Allpicture::getAllpictures($params);

		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}	
	
	
	public function checkAction(){
		$result = $result1 = $result2 = 1;
		$post = $this->getRequest()->getPost();
		$uids = &$post['uids'];
		$bids = &$post['bids'];
		$ids = &$post['ids'];

		if (is_array($ids) && count($ids)) {
			array_splice($ids, 50); // 保护一下
			Better_Admin_Allpicture::clearImageLog($ids);
		}
		
		if (is_array($bids) && count($bids)>0) {
			$result1 = Better_Admin_Blog::delAttach($bids);
		}
	
		if (is_array($uids) && count($uids)>0) {
			$result2 = Better_Admin_User::delAvatars($uids);
		}
		
		if(!$result1 || !$result2){
			$result = 0;
		}
		
		$this->sendAjaxResult($result);
	}
	
	public function delAction(){
		$result = $result1 = $result2 = 1;
		$post = $this->getRequest()->getPost();
		$uids = &$post['uids'];
		$bids = &$post['bids'];

		if (is_array($bids) && count($bids)>0) {
			$result1 = Better_Admin_Blog::delAttach($bids);
		}
	
		if (is_array($uids) && count($uids)>0) {
			$result2 = Better_Admin_User::delAvatars($uids);
		}
		
		if(!$result1 || !$result2){
			$result = 0;
		}
		
		$this->sendAjaxResult($result);
	}

}