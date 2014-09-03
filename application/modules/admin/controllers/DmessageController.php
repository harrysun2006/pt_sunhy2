<?php

/**
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 * 
 */

class Admin_DmessageController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/dmessage.js?ver='.BETTER_VER_CODE);
		$this->view->title="用户私信管理";		
	}
	
	public function indexAction()
	{
		$params = $this->getRequest()->getParams();
		
		$params['from']= $params['from']? $params['from'] : date('Y-m-d', time()-BETTER_ADMIN_DAYS+BETTER_8HOURS);
		$params['to']= $params['to']? $params['to']: date('Y-m-d', time()+BETTER_8HOURS);
		
		/**
		 * 是否是通过bedo号精确查找
		 */
		if(isset($params['bedo_no']) && $params['bedo_no']!=""){
			//精确查找
			$uid = Better_DAO_Bedo::getInstance()->getUidByJid($params['bedo_no']);
			$params['uid']=$uid;
		}
		if($params['uid'] || !(isset($params['bedo_no'])&& $params['bedo_no']!="")){
			$result = Better_Admin_Dmessage::getReceived($params);
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
		$ids = &$post['ids'];
		$fids = &$post['fids'];
		
		if (is_array($ids) && count($ids)>0) {
			Better_Admin_Dmessage::delReceived($ids) && $result = 1;
		}
		
		if(is_array($fids) && count($fids)>0){
			//Better_Admin_Dmessage::delSended($fids) && $result = 1;
		}
		
		if($result==1){
			foreach($ids as $bid){
				Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>0), array('bid'=>$bid));
			}
		}
		
		$this->sendAjaxResult($result);
	}

}