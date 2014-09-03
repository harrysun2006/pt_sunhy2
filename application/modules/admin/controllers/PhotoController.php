<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_PhotoController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();
		
		$this->view->headScript()->appendFile('js/controllers/admin/photo.js?ver='.BETTER_VER_CODE);
		$this->view->title="图片管理";		

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
		$params['photo'] = 1;
		
		$params['from']= $params['from']? $params['from'] : date('Y-m-d', time()-BETTER_ADMIN_DAYS+BETTER_8HOURS);
		$params['to']= $params['to']? $params['to']: date('Y-m-d', time()+BETTER_8HOURS);
		$params['page_size'] = $params['page_size'] ? intval($params['page_size']) : 100;
		/**
		 * 是否是通过bedo号精确查找
		 */
		if(isset($params['bedo_no']) && $params['bedo_no']!=""){
			//精确查找
			$uid = Better_DAO_Bedo::getInstance()->getUidByJid($params['bedo_no']);
			$params['uid']=$uid;
		}
		if($params['uid'] || !(isset($params['bedo_no'])&& $params['bedo_no']!="")){
			$result = Better_Admin_Blog::getBlogs($params);
			$this->view->params = $params;
			$this->view->rows = $result['rows'];
			$this->view->count = $result['count'];
		}else{
			$this->view->params = $params;
			$this->view->rows = array();
			$this->view->count = 0;
		}	
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