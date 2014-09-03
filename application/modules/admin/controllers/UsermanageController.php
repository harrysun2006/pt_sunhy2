<?php

/**
 * @package Controllers
 * @author yangl
 */

class Admin_UsermanageController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/usermanage.js?ver='.BETTER_VER_CODE);
		$this->view->title="用户账号管理";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		
		$params['from']= $params['from']? $params['from'] : date('Y-m-d', time()-BETTER_ADMIN_DAYS+BETTER_8HOURS);
		$params['to']= $params['to']? $params['to']: date('Y-m-d', time()+BETTER_8HOURS);
		
		$result=Better_Admin_Usermanage::getUsers($params);
		
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];
	}
	
	
	public function banAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		
		$result = Better_Admin_Usermanage::banAccount($params);
		
		$this->sendAjaxResult($result);
	}
	
	
	public function unbanAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		$result = Better_Admin_Usermanage::unbanAccount($params);
		
		$this->sendAjaxResult($result);
		
	}
	
	
	public function lockAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		
		$result = Better_Admin_Usermanage::lockAccount($params);
		
		$this->sendAjaxResult($result);
	}
	
	
    public function unlockAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		
		$result = Better_Admin_Usermanage::unlockAccount($params);
		
		$this->sendAjaxResult($result);
	}
	
	
	public function muteAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uid = $params['uid']? $params['uid'] :'';
		
		$result = Better_DAO_Admin_Usermanage::muteAccount($uid);
		
		$this->sendAjaxResult($result);
	}
	
	
	public function unmuteAction(){
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uid = $params['uid']? $params['uid'] :'';
		
		$result = Better_DAO_Admin_Usermanage::unmuteAccount($uid);
		
		$this->sendAjaxResult($result);
	}
	
	
}
	
	