<?php

/**
 * 
 * @package Controllers
 * @author yangl	
 * 
 */

class Admin_KaimessageController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/kaimessage.js?ver='.BETTER_VER_CODE);
		$this->view->title="KAI私信管理";	
	}
	
	public function indexAction()
	{
		
		$params = $this->getRequest()->getParams();
		$params['kuid'] = '10000';
		
		$result = Better_Admin_Dmessage::getReceived($params);
	
		$this->view->params = $params;
		$this->view->rows = $result['rows'];
		$this->view->count = $result['count'];	
	}
	
	
	public function sendAction()
	{
		$result = 0;
		$params = $this->getRequest()->getParams();
		$uid = $params['uids'];
		$content = trim($params['content']);
		$omsg_id = $params['msg_id'];
		
		if (empty($uid)) {
			$result = '收信人不能为空';
		} else if (empty($content)) {
			$result = '私信内容不能为空';
		} else {
			$msg_id = Better_User_DirectMessage::getInstance(BETTER_HELP_UID)->send($content, $uid);
			$result = 1;	
		}
		
		if($result){
			$mes = Better_DAO_DmessageReceive::getInstance(BETTER_SYS_UID)->get($omsg_id);
			if($mes['msg_id']){
				$text = $mes['reply_content'].$this->sess->admin_uid.'的回复：<br>'.$content.'<br>';
				Better_DAO_DmessageReceive::getInstance(BETTER_SYS_UID)->update(array('reply_flag'=>1, 'reply_content'=>$text), $omsg_id);
				
				Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('回复KAI私信：<br>'.$content, 'reply_kaimessage');
			}	
		}
		
		$this->sendAjaxResult($result);
	}
	
}