<?php

/**
 * 
 * @package Controllers
 * @author yangl	
 * 
 */

class Admin_EmailController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/email.js?ver='.BETTER_VER_CODE);
		$this->view->title="群发Email";	
	}
	
	public function indexAction()
	{
		
	}
	
	public function sendAction(){
		try{
			$params = $this->getRequest()->getParams();
			$count = Better_Email_Common::send($params);
			$this->view->result = 1;
			$this->view->count = $count;
		}catch(Exception $e){
			$this->view->result = 0;
		}
	}
	
	public function checkpoiAction(){
		$poi_id = $this->getRequest()->getParam('poiid', '');
		$result = array('result'=>0, 'message'=>'');
		if($poi_id){
			$poi = Better_Poi_Info::getInstance($poi_id)->getBasic();
			if($poi['poi_id']){
				$poi_name = $poi['name'];
				$result['result']=1;
				$result['message']=$poi_name;
			}else{
				$result['message']='找不到该POI';
			}
		}else{
			$result['message']='请输入POI ID';
		}
		
		$output = json_encode($result);
		$this->getResponse()->sendHeaders();
		echo $output;
		exit(0);
	}
	
	
	public function previewAction(){
	
	}
	
}