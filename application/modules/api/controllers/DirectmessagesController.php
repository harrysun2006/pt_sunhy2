<?php

/**
 * 站内私信api
 * 
 * @package Controllers
 * @author leip
 *
 */
class Api_DirectmessagesController extends Better_Controller_Api
{
	
	public function init()
	{
		parent::init();
		
		$userInfo = $this->auth();
	}
	
	/**
	 * 显示私信
	 * 
	 * @return
	 */
	public function indexAction()
	{
		$this->xmlRoot = 'direct_messages';
		$page = $this->getRequest()->getParam('page', 1);
		$since = intval($this->getRequest()->getParam('since_id', 0));
		$count = $this->getRequest()->getParam('count', 20);
		$count = $count>50 ? 50 : $count;
		$count = $count<=0 ? 20 : $count;
		
		$msg = Better_User_DirectMessage::getInstance($uid);
		$results = $msg->getReceiveds($page, $count, $since, false);

		foreach ($results['msgs'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'direct_message' => Better_Api::translateDirectMessage($row, $results['users'][$row['from_uid']], $userInfo),
				);
		}		
		
		$this->output();
	}
	
	/**
	 * 删除私信
	 * 
	 * @return
	 */
	public function destroyAction()
	{
		$this->needPost();
		$this->xmlRoot = 'direct_message';
		$id = $this->getRequest()->getParam('id', 0);

		if ($id) {
			$msg = Better_User_DirectMessage::getInstance($uid)->getReceived($id);
			if ($msg['msg_id'] && Better_User_DirectMessage::getInstance($uid)->delReceived($id)) {
				$this->data[$this->xmlRoot] = Better_Api::translateDirectMessage($msg, $userInfo, $userInfo);
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.direct_messages.invalid_msg_id');
		}		
		
		$this->output();
	}

	/**
	 * 已发送的私信
	 * 
	 * @return
	 */
	public function sentAction()
	{
		$this->xmlRoot = 'direct_messages';
		$since = intval($this->getRequest()->getParam('since_id', 0));
		$results = Better_User_DirectMessage::getInstance($uid)->getSents($this->page, $this->count, $since, false);

		foreach ($results['msgs'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'direct_message' => Better_Api::translateDirectMessage($row, $results['users'][$row['to_uid']], $userInfo),
				);
		}		
		
		$this->output();
	}
	
	/**
	 * 发送私信
	 * 
	 * @return
	 */
	public function newAction()
	{
		$this->xmlRoot = 'direct_message';
		$this->needPost();
		
		$receiver = trim($this->getRequest()->getParam('user', ''));
		$text = trim($this->getRequest()->getParam('text', ''));
		
		if ($receiver=='') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.direct_messages.msg_receiver_required');
		} else if ($text=='') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.direct_messages.msg_text_required');
		} else if ($receiver==$this->userInfo['username']) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.direct_messages.cannot_send_to_self');
		} else {

			$receiverUserInfo = Better_User::getInstance()->getUserByUsername($receiver);
			if($receiverUserInfo['uid']){
				$receiverUser = Better_User::getInstance($receiverUserInfo['uid']);
				
				if($this->uid==BETTER_SYS_UID || $receiverUserInfo['friend_sent_msg']=='0' || ($receiverUserInfo['friend_sent_msg']=='1' && in_array($this->uid, $receiverUser->friends))){
					if ($receiverUserInfo['username']==$receiver) {
						$msg_id = Better_User_DirectMessage::getInstance($this->uid)->send($text, $receiverUserInfo);
		
						if ($msg_id) {
							$row = Better_User_DirectMessage::getInstance($receiverUserInfo['uid'])->getReceived($msg_id);
							$this->data[$this->xmlRoot] = Better_Api::translateDirectMessage($row, $receiverUserInfo, $this->userInfo);
						} else {
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->error('error.direct_messages.msg_receiver_invalid');
						}
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.direct_messages.msg_receiver_invalid');
					}
				}else{
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.direct_messages.msg_only_friends');
				}		
			}else{
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.direct_messages.msg_receiver_invalid');
			}
		}
			
			

		$this->output();
	}
}