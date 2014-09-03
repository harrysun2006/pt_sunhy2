<?php

/**
 * 
 * @package Controllers
 * @author yangl	
 * 
 */

class Admin_PrivmessageController extends Better_Controller_Admin
{
	public function init()
	{
		parent::init();	
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/admin/privmessage.js');
		$this->view->title="内部私信";	
	}
	
	public function indexAction()
	{
		
	}
	
	public function sendAction(){
		$result = array();
		 
		$params = $this->getRequest()->getParams();
		$uids = $params['uids']? $params['uids']: array();
		$msgs = $params['msg_content']? $params['msg_content']: '';
		
		if(is_array($uids) && count($uids)>0){
			if($msgs){
				foreach($uids as $uid){
					$result = Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
						'content' => $msgs,
						'receiver' => $uid
						));
				}
				$uid_string = implode(',', $uids);
				Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('发送内部私信：<br>'.$msgs.'<br>给：'.$uid_string , 'send_privmsg');
			}
		}
		
		$this->view->result = $result;
		
	}
	
}