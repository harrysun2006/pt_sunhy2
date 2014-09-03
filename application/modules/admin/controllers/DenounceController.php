<?php

/**
 * 
 * @package Controllers
 * @author yangl	
 * 
 */

class Admin_DenounceController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/denounce.js?ver='.BETTER_VER_CODE);
		$this->view->title="用户举报管理";
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		
		$data = Better_Admin_Denounce::getDenounceBlogs($params);
		
		$this->view->params = $params;
		$this->view->rows = $data['rows'];
		$this->view->count = $data['count'];
		
	}
	
	public function userAction(){
	
		$params = $this->getRequest()->getParams();
		
		$data = Better_Admin_Denounce::getDenounceUsers($params);
		
		$this->view->params = $params;
		$this->view->rows = $data['rows'];
		$this->view->count = $data['count'];
	}
	
	
	public function poiAction(){
	
		$params = $this->getRequest()->getParams();
		
		$data = Better_Admin_Denounce::getDenouncePois($params);
		
		$this->view->params = $params;
		$this->view->rows = $data['rows'];
		$this->view->count = $data['count'];
	}
	
	
	public function statusAction()
	{
		$result = 0;
		$params = $this->getRequest()->getParams();
		$ids = $params['ids']? $params['ids']:array();
		$status = $params['status']? $params['status']: '';
		$dtype = $params['dtype']? $params['dtype']: '';
		
		if($dtype=='index'){
			foreach($ids as $id){		
				Better_DAO_Admin_Denounce::getInstance()->changeStatus($id, $status) && $result = 1;
			}
		}else if($dtype=='user'){
			foreach($ids as $id){		
				Better_DAO_Admin_Denounceuser::getInstance()->changeStatus($id, $status) && $result = 1;
			}
		}else if($dtype=='poi'){
			foreach($ids as $id){		
				Better_DAO_Admin_Denouncepoi::getInstance()->changeStatus($id, $status) && $result = 1;
			}
		}
		
		$this->sendAjaxResult($result);
		
	}
	
	
	public function delAction()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		$ids = &$post['ids'];
		$admin_uid = $this->sess->admin_uid;
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Blog::delBlogs($bids) && $result = 1;
		}
		
		if($result){
			foreach($ids as $id){
				Better_DAO_Admin_Denounce::getInstance()->update(array(
					'act_result'=>'已删除','act_time'=>time(),'status'=>'have_progress', 'admin_uid'=>$admin_uid
				), $id);
			}
		}
		
		$this->sendAjaxResult($result);
	}
	
}