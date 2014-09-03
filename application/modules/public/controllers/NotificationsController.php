<?php

/**
 * API通知
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Public_NotificationsController extends Better_Controller_Public
{
	public function init()
	{
		Better_Log::getInstance()->logTime('Before_Notification_Controller_Init:['.__CLASS__.']');
		parent::init();
		
		Better_Log::getInstance()->logTime('Before_Api_Auth:['.__CLASS__.']');
		$this->auth();
		Better_Log::getInstance()->logTime('After_Api_Auth:['.__CLASS__.']');
		
		Better_Log::getInstance()->logTime('After_Notification_Controller_Init:['.__CLASS__.']');
	}
	
	/**
	 * 8.2取已读私信
	 * 
	 * @return
	 */
	public function readmAction()
	{
		$this->xmlRoot = 'notifications';
		$since = intval($this->getRequest()->getParam('since_id', 0));
		$keep = $this->getRequest()->getParam('keep', 'false')=='true' ? true : false;
		
		$msg = $this->user->notification()->DirectMessage();
		$results = $msg->getReceiveds(array(
			'page' => $this->page,
			'count' => $this->count,
			'since' => $since,
			'keep' => $keep,
			'delived' => null,
			));

		foreach ($results['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'notification' => $this->api->getTranslator('notification')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);
		}		
		
		$this->output();
	}
	
	/**
	 * 删除通知
	 * 
	 * @return
	 */
	public function destroyAction()
	{
		$this->xmlRoot = 'notification';
		$id = (int)$this->getRequest()->getParam('id', 0);
		$this->needPost();
		
		if ($id>0) {
			$direct = $this->getRequest()->getParam('direct', 'received');
			
			switch ($direct) {
				case 'sent':
					break;
				case 'received':
				default:
					$data = $this->user->notification()->getReceived($id);
					
					if ($data['msg_id']) {
						if ($data['uid']==$this->uid) {
							$result = $this->user->notification()->delReceived($id);
							if ($result) {
								$this->data[$this->xmlRoot] = $this->api->getTranslator('notification')->translate(array(
									'data' => &$data,
									'userInfo' => &$this->userInfo,
									));
							} else {
								$this->errorDetail = __METHOD__.':'.__LINE__;
								$this->serverError();
							}
						} else {
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->error('error.direct_messages.invalid_msg_id');
						}
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.direct_messages.invalid_msg_id');
					}
					break;
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.direct_messages.invalid_msg_id');
		}
		
		$this->output();
	}
	
	/**
	 * 8.3 发送站内私信
	 * 
	 * @return
	 */
	public function newAction()
	{
		$this->xmlRoot = 'notification';
		$userId = (int)$this->getRequest()->getParam('user_id', 0);
		$text = trim(urldecode($this->getRequest()->getParam('text', '')));
		
		$this->needPost();
		//$this->needSufficientKarma();
		
		if ($userId>0) {
			if ($text!='') {
				$result = $this->user->notification()->directMessage()->send(array(
					'content' => $text,
					'receiver' => $userId
					));

				$codes = &$result['codes'];
				switch ($result['code']) {
					case $codes['BLOCKED_BY_RECEIVER']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.direct_messages.blocks');
						break;
					case $codes['WORDS_R_BANNED']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.direct_message.ban_words');						
						break;
					case $codes['CANT_TO_SELF']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.direct_messages.cannot_send_to_self');
						break;
					case $codes['INVALID_RECEIVER']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.direct_messages.msg_receiver_invalid');
						break;
					case $codes['SUCCESS']:
						$this->data[$this->xmlRoot] = $this->api->getTranslator('notification')->translate(array(
							'data' => Better_User::getInstance($userId)->notification()->getReceived($result['id']),
							));
						break;
					case $codes['FAILED']:
					default:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->serverError();
						break;
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.direct_messages.msg_text_required');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.direct_messages.msg_receiver_required');
		}
		
		$this->output();
	}

	/**
	 * 8.1 取得所有通知
	 * 
	 * @return
	 */
	public function indexAction()
	{
		$this->xmlRoot = 'notifications';
		
		Better_Log::getInstance()->logTime('Before_Notification_Run:['.__CLASS__.']');

		$since = intval($this->getRequest()->getParam('since_id', 0));
		$keep = $this->getRequest()->getParam('keep', 'false')=='true' ? true : false;
		
		$msg = $this->user->DirectMessage()->All();
		$results = $msg->getReceiveds(array(
			'page' => $this->page,
			'count' => $this->count,
			'since' => $since,
			'keep' => $keep,
			));

		foreach ($results['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'notification' => $this->api->getTranslator('notification')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);
			if ($row['sid']) {
				Better_Log::getInstance()->logInfo('SessionId:['.$row['sid'].'], Msg:[NOTIFICATION_DELIVED], Method:['.__METHOD__.']', 'hunting');
			}
		}	
		
		Better_Log::getInstance()->logTime('After_Notification_Run:['.__CLASS__.']');
		$this->output();
	}
	
	/**
	 * 
	 * 取未读私信
	 */
	public function unreadedAction()
	{
		$this->xmlRoot = 'notifications';
		
		$since = (int)$this->getRequest()->getParam('since_id', 0);
		$msg = $this->user->notification()->All();
		$results = $msg->getUnreaded(array(
			'page' => 1,
			'page_size' => 100,
			'since' => $since,
			));
		
		foreach ($results['rows'] as $row) {
			$this->data[$this->xmlRoot][] = array(
				'notification' => $this->api->getTranslator('notification')->translate(array(
					'data' => &$row,
					'userInfo' => &$this->userInfo,
					)),
				);
		}
		
		$this->output();
	}

	/**
	 * 
	 * 8.5 标记为已读
	 */
	public function markreadAction()
	{
		$this->xmlRoot = 'notification';
		$id = (int)$this->getRequest()->getParam('id', 0);
		$this->needPost();
		
		if ($id>0) {
			$direct = $this->getRequest()->getParam('direct', 'received');
			
			switch ($direct) {
				case 'sent':
					break;
				case 'received':
				default:
					$data = $this->user->notification()->all()->getReceived($id);
					
					if ($data['msg_id']) {
						if ($data['uid']==$this->uid) {
							$result = $this->user->notification()->all()->updateReaded((array)$id);
							
							if ($result) {
								switch ($data['type']) {
									case 'friend_request':
										$cacher = $this->user->cache();
										$cacheKey = 'friend_request_count';
										$d = (int)$cacher->get($cacheKey);
										if ($d>1) {
											$cacher->set($cacheKey, $d-1);
										}
										break;
									case 'follow_request':
										$cacher = $this->user->cache();
										$cacheKey = 'follow_request_count';
										$d = (int)$cacher->get($cacheKey);
										if ($d>1) {
											$cacher->set($cacheKey, $d-1);
										}										
										break;
									case 'direct_message':
										$cacher = $this->user->cache();
										$cacheKey = 'direct_message_count';
										$d = (int)$cacher->get($cacheKey);
										if ($d>1) {
											$cacher->set($cacheKey, $d-1);
										}																				
										break;	
								}
								
								$this->data[$this->xmlRoot] = $this->api->getTranslator('notification')->translate(array(
									'data' => &$data,
									'userInfo' => &$this->userInfo,
									));
							} else {
								$this->errorDetail = __METHOD__.':'.__LINE__;
								$this->serverError();
							}
						} else {
							$this->errorDetail = __METHOD__.':'.__LINE__;
							$this->error('error.direct_messages.invalid_msg_id');
						}
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.direct_messages.invalid_msg_id');
					}
					break;
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.direct_messages.invalid_msg_id');
		}
		
		$this->output();		
	}	
}