<?php

/**
 * 后台用户操作查询
 * 
 * @package Controllers
 * @author yangl <yangl@peptalk.cn>
 * 
 */

class Admin_AdministratorController extends Better_Controller_Admin
{
	public function init()
	{            
		if(!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])){ 
			Header("WWW-Authenticate: Basic"); 
			Header("HTTP/1.0 401 Unauthorized"); 
			echo "Enter username and password"; 
			exit; 
		}else{ 
			if (!($_SERVER['PHP_AUTH_USER']=="kaikai_admin" && $_SERVER['PHP_AUTH_PW']=="kaikai123456") ){ 
	
				Header("WWW-Authenticate: Basic"); 
				Header("HTTP/1.0 401 Unauthorized"); 
				echo "ERROR : username or password is invalid."; 
				exit; 
			} 
		}
		
		parent::init();	
		$this->view->headScript()->appendFile('js/controllers/admin/administrator.js?ver='.BETTER_VER_CODE);
		$this->view->title="管理员管理";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		$result=Better_Admin_Administrators::getInstance()->getAdministrators($params);
		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	public function addAction()
	{
		$result=0;
		
		$params = $this->getRequest()->getParams();
		$uid=$params['uid'];
		$username=$params['username'];
		$password=$params['pwd'];
		
		if($uid!='' && $username!='' && $password!=''){
		
			if(Better_DAO_Admin_Administrators::getInstance()->get($uid)){
				$result=0;
			}
			else{
				$data=array(
					'uid'=> $uid,
					'username'=> $username,
					'password'=> $password,
	        	 );
			Better_DAO_Admin_Administrators::getInstance()->insert($data) && $result=1;
			}
		
		}
		
		$this->sendAjaxResult($result);
	}
	
	
	public function delAction()
	{
		$result = 0;
		$post = $this->getRequest()->getPost();
		$bids = &$post['bids'];
		
		if (is_array($bids) && count($bids)>0) {
			Better_Admin_Administrators::getInstance()->delAdministrators($bids) && $result=1;
		}
		
		$this->sendAjaxResult($result);
	}
	
	
	public function updateAction()
	{
		$result=0;
	
		$params = $this->getRequest()->getParams();
		$id=$params['id'];
		$uid=$params['uid'];
		$username=$params['username'];
		$password=$params['pwd'];
		
		if(Better_DAO_Admin_Administrators::getInstance()->get($id)){
				$data=array(
					'uid'=> $uid,
					'username'=> $username,
					'password'=> $password,
	        	 );
	       
	       Better_DAO_Admin_Administrators::getInstance()->update($data, $id) && $result=1;
	       
		}
		$this->sendAjaxResult($result);
	}
}