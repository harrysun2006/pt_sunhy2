<?php

/**
 * 
 * @package Controllers
 * @author yangl	
 * 
 */

class Admin_CitymessageController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/citymessage.js');
		$this->view->title="城市消息";	
	}
	
	public function indexAction()
	{
		
	}
	
	public function sendAction(){
		$result = false;		 
		$params = $this->getRequest()->getParams();
		$type = $params['type']? $params['type']: 'email';
		$msgs = $params['content']? nl2br($params['content']): '';
		$live_city = strlen($params['live_city'])>0 ? $params['live_city']:'';
		$title = $params['title']? $params['title']: '';		
		
		if(Better_Registry::get('sess') && Better_Registry::get('sess')->admin_uid){
				$admin_uid = Better_Registry::get('sess')->admin_uid;
			}else{
				$admin_uid = '100';
			}
		$result = Better_DAO_Citymessage::getInstance()->insert(array(
			'uid' => $admin_uid,
			'type' => $type,
			'title' => $title,
			'content' => $msgs,
			'dateline' => time(),
			'result' => 0,	
			'city' => $live_city		
			));
		
		$this->view->result = $result;
		
	}
	public function previewAction(){

	

	}
}