<?php

class Market_LoginController extends Better_Controller_Market
{
	protected $output = array();
	
    public function init()
    {
    	parent::init();
    	
    	$this->commonMeta();	
    }

    public function indexAction()
    {
    	
    }
    
    
    public function loginAction()
    {
    	$post = $this->getRequest()->getParams();
    	$username = $post['name'];
    	$password = $post['pwd'];
		$flag = false;
    	$messgae = '';
    	
    	if($username=='kaikai' && $password=='123456'){
    		$this->sess->set('market_uid', 1000);
    		$flag = true;
    	}else if($username=='partner' && $password=='123456'){
    		$this->sess->set('market_uid', 2000);
    		$flag = true;
    	}else{
    		$messgae = 'Error username and password';
    	}
    	
    	$this->view->error = $messgae;
    	
    	if($flag){
    		$this->_helper->getHelper('Redirector')->gotoUrl('http://'.$_SERVER['HTTP_HOST'].'/market/index?status=not_check');
    		exit(0);
    	}
    	
    }
    
    
    public function logoutAction(){
    	$this->sess->set('market_uid', null);
    	$this->_helper->getHelper('Redirector')->gotoUrl('http://'.$_SERVER['HTTP_HOST'].'/market/login');
    	exit(0);
    }
    
}