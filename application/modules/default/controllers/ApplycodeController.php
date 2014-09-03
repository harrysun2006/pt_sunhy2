<?php

/**
 * 申请邀请码
 *
 * @package Controllers
 */

class ApplycodeController extends Better_Controller_Front
{
	protected $output = array();
	
    public function init()
    {
		parent::init();
		$this->commonMeta();
		
    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/applycode.js');
    	//$this->view->css = 'index';
  
    }

    public function indexAction()
    {
        
    }
    
    
    public function doAction(){
    	$result = false;
    	$email = $this->getRequest()->getParam('email' , '');
    	
    	$invitecode = Better_Invitecode::getInstance()->getOneCode();
    	$code = $invitecode['code'];
    	
    	if($email && $code){
    		$result = Better_DAO_Applycode::getInstance()->insert(array('email'=>$email, 'code'=>$code));
    	}
    	
    	if($result){
    		Better_DAO_Invitecode::getInstance()->update(array('enable'=>'0'), $invitecode['id']);
    		
    		$this->view->ifsuccess=true;
    		$this->view->message=$this->lang->global->aplycode_success;
    	}else{
    		$this->view->ifsuccess=false;
    		$this->view->message=$this->lang->global->aplycode_failed;
    	}
    	
    }
}

