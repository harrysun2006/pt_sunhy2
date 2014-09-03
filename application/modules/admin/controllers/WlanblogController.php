<?php

/**
 * 
 * @package Controllers
 * @author yangl
 * 
 */

class Admin_WlanblogController extends Better_Controller_Admin
{
	public function init()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/wlanblog.js?ver='.BETTER_VER_CODE);
		$this->view->title="所有最新文本";		
		
		parent::init();	
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();	
		$page = isset($params['page']) ? $params['page']:1;
		$params['page_size'] = isset($params['page_size'])? $params['page_size'] : 50;		
		$params['status']= $params['status']? $params['status'] : '0';		
		$result = Better_DAO_Wlanblog::getInstance()->getAll($params);					
		$this->view->count = $result['count'];
		$this->view->rows = $result['rows'];
		$this->view->page = $result['page'];
		$this->view->params = $params;		
	}
	
	
	public function passAction(){
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];		
		if (is_array($bids) && count($bids)>0) {
			foreach($bids as $bid){
				$blog = Better_DAO_Wlanblog::getInstance()->getInfo($bid);	
				if($blog['status']=='0'){
					$date =array(
						'id' => $bid,
						'checktm' => time(),
						'status' => 1
					);					
					$result = Better_DAO_Wlanblog::getInstance()->update($date);		
				}
				if($result){
					$content = '通过审核：<br>'.$blog['message'];
					Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog($content, 'pass_blog');
				}
			}
		}
		
		
		$this->sendAjaxResult($result);
		
	}
	
	
	public function falseAction(){
		$result = 0;
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];		
		if (is_array($bids) && count($bids)>0) {
			foreach($bids as $bid){
				$blog = Better_DAO_Wlanblog::getInstance()->getInfo($bid);	
				if($blog['status']=='0'){
					$date =array(
						'id' => $bid,
						'checktm' => time(),
						'status' => -1
					);
					$result = Better_DAO_Wlanblog::getInstance()->update($date);		
				}
				if($result){
					$content = '审核不通过：<br>'.$blog['message'];
					Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog($content, 'pass_blog');
				}
			}
		}
		$this->sendAjaxResult($result);
	
	}
	
	
	

}