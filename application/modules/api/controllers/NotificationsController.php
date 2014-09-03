<?php

/**
 * API通知
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_NotificationsController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		
		$this->auth();
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
		$userId = (int)$this->post['user_id'];
		$text = trim($this->post['text']);
		
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
					case $codes['ONLY_RECEIVE_FRIENDS']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.direct_messages.msg_only_friends');
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

		$since = intval($this->getRequest()->getParam('since_id', 0));
		$keep = $this->getRequest()->getParam('keep', 'false')=='true' ? true : false;
		$ver = trim($this->getRequest()->getParam('ver', '1'))=='2' ? 2 : 1;
		
		$nrSupportClients = array('8','IFN');
		$client = $this->user->cache()->get('client');
		$platform = (is_array($client) && isset($client['platform'])) ? $client['platform'] : '';
		$withoutNr = in_array($platform, $nrSupportClients) ? false : true;
$withoutNr = true;		
		$msg = $this->user->DirectMessage()->All();
		$results = $msg->getReceiveds(array(
			'page' => $this->page,
			'count' => $this->count,
			'since' => $since,
			'keep' => $keep,
			'act_result' => 0,
			'without_nr' => $withoutNr
			));

		foreach ($results['rows'] as $row) {
			if (!$this->config->dm_ppns && $ver==1 && $row['category']=='notification_readed') {
				continue;
			}
			
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
		
		$this->output();
	}
	
	/**
	 * 好友请求不处理，否则计数会有问题
	 * 8.5 标记为已读
	 */
	public function markreadAction()
	{
		$this->xmlRoot = 'notification';
		$id = (int)$this->getRequest()->getParam('id', 0);
		$this->needPost();

		$direct = '';
		if ($id > 0) {
			$direct = $this->getRequest()->getParam('direct', 'received');
		} else {
			$direct = 'error';
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.direct_messages.invalid_msg_id');
		}

		$data = null;
		switch ($direct) {
			case 'error':
			case 'sent':
				break;
			case 'received':
			default:
				$data = $this->user->notification()->all()->getReceived($id);
				break;
		}
		$key = '';
		$result = false;
		if ($data && $data['msg_id'] && $data['uid'] == $this->uid) {
			switch ($data['type']) {
				case 'friend_request': // friend_request_count表示未处理的好友请求数,不是已读并且不需要读
					$result = true;
					/* 
					$cacher = $this->user->cache();
					$cacheKey = 'friend_request_count';
					$d = (int)$cacher->get($cacheKey);
					if ($d>1) {
						$cacher->set($cacheKey, $d-1);
					}
					*/
					break;
				case 'follow_request':
					$key = 'follow_request_count';
					$result = $this->user->notification()->all()->updateReaded((array)$id);
					break;
				case 'direct_message':
					$key = 'direct_message_count';
					$result = $this->user->notification()->all()->updateReaded((array)$id);
					break;	
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.direct_messages.invalid_msg_id');
		}
		if ($result) {
			if ($key) {
				$cacher = $this->user->cache();
				$d = (int)$cacher->get($key);
				$d > 1 && $cacher->set($key, $d-1);
			}
			$this->data[$this->xmlRoot] = $this->api->getTranslator('notification')->translate(array(
				'data' => &$data,
				'userInfo' => &$this->userInfo,
			));
		}
		$this->output();		
	}
	
	/**
	 * 
	 * 8.6 通知状态同步
	 */
	public function syncAction()
	{
		$this->xmlRoot = 'notification_sync';
		
		$output = array(
			'direct_message' => array(
				'readed' => array(),
				'unread' => array()
				),
			'follow_request' => array(
				'readed' => array(),
				'unread' => array(),
				'confirmed' => array(),
				'rejected' => array(),
				),
			'friend_request' => array(
				'readed' => array(),
				'unread' => array(),
				'confirmed' => array(),
				'rejected' => array(),				
				),
			);
		
		$fromId = (int)trim($this->getRequest()->getParam('since_id', 0));
		$data = $this->user->notification()->directMessage()->sync($fromId);

		foreach ($data['readed'] as $row) {
			$output['direct_message']['readed'][] = array(
				'id' => $row['msg_id']
				);
		}
		
		foreach ($data['unread'] as $row) {
			$output['direct_message']['unread'][] = array(
				'id' => $row['msg_id']
				);
		}			

		//	关注请求
		foreach ($data['follow_request_agree'] as $row) {
			$output['follow_request']['confirmed'][] = array(
				'id' => $row['msg_id'],
				);
		}
		
		foreach ($data['follow_request_reject'] as $row) {
			$output['follow_request']['rejected'][] = array(
				'id' => $row['msg_id'],
				);
		}			
		
		foreach ($data['follow_request_readed'] as $row) {
			$output['follow_request']['readed'][] = array(
				'id' => $row['msg_id'],
				);
		}		

		foreach ($data['follow_request_unread'] as $row) {
			$output['follow_request']['unread'][] = array(
				'id' => $row['msg_id'],
				);
		}					
		
		//	好友请求
		foreach ($data['friend_request_agree'] as $row) {
			$output['friend_request']['confirmed'][] = array(
				'id' => $row['msg_id'],
				);
		}
		
		foreach ($data['friend_request_reject'] as $row) {
			$output['friend_request']['rejected'][] = array(
				'id' => $row['msg_id'],
				);
		}			
		
		foreach ($data['friend_request_readed'] as $row) {
			$output['friend_request']['readed'][] = array(
				'id' => $row['msg_id'],
				);
		}		

		foreach ($data['friend_request_unread'] as $row) {
			$output['friend_request']['unread'][] = array(
				'id' => $row['msg_id'],
				);
		}	
		
		$this->data[$this->xmlRoot] = &$output;
		
		$this->output();
	}

	/**
	 * 9.5 删除全部通知
	 */
	public function removesAction()
	{
		$this->needPost();
		$this->xmlRoot = 'result';
		$since = (int)$this->getRequest()->getParam('since_id', 0);
		$count = (int)$this->getRequest()->getParam('count', $this->count);
		$type = $this->getRequest()->getParam('type', 'direct_message');
		$page = $this->page;
		if ($type == 'all') $type = '';

		$count <= 0 && $count = $this->count;
		$n = $this->user->notification()->All();
		$params = array(
			'page' => $page,
			'count' => $count,
			'since' => $since,
			'force' => 1,
			'readed' => 0,
			'delived' => 1,
			'type' => split(',', $type),
		);
		$ns = $n->getReceiveds($params);
		$cacher = $this->user->cache();
		$d = (int)$cacher->get('friend_request_count');
		$ids = array(); // 私信
		$total = min($ns['count'], $count);
		$removed = 0;
		foreach ($ns['rows'] as $row) {
			if ($row['type'] == 'friend_request') {
				$r = $this->user->friends()->reject($row['from_uid']);
				$removed += $r;
				$d -= $r;
			} else {
				$ids[] = $row['msg_id'];	
			}
		}
		$d < 0 && $d = 0;
		$cacher->set('friend_request_count', $d);
		if (count($ids) > 0) {
			// 私信设为已读
			$r = Better_DAO_DmessageReceive::getInstance($this->uid)->updateByCond(array(
					'readed' => '1'
				), array(
					'msg_id' => $ids,
					'uid' => $this->uid
				));
			$removed += count($ids);
			$d = (int)$cacher->get('direct_message_count');
			$d -= $removed;
			$d < 0 && $d = 0;
			$cacher->set('direct_message_count', $d);
		}

		// 返回
		$msg = $this->lang->notifications->removes;
		// Better_Log::getInstance()->logInfo('$since=' . $since . ', $count='. $count . ', $total=' . $total . ', $removed=' . $removed, 'api_notifications');
		// $msg = str_replace('{REMOVED}', $removed, $msg);
		// $msg = str_replace('{TOTAL}', $total, $msg);

		$this->data[$this->xmlRoot] = array(
			'message' => $msg,
			'total' => $total,
			'removed' => $removed,
		);
		$this->output();
	}

	private function _refuseinv($msg_id)
	{
		$this->xmlRoot = 'message';
		if ($msg_id > 0) {
			$data = Better_User_DirectMessage::getInstance($this->uid)->getReceived($msg_id);
			if (isset($data['msg_id']) && $data['uid'] == $this->uid && $data['type'] == "invitation_todo") {
				$r = Better_User_DirectMessage::getInstance($this->uid)->delReceived($msg_id);
				Better_DAO_Todopoi::getInstance($this->uid)->delete($msg_id, 'msg_id');
				$this->data[$this->xmlRoot] = $this->lang->invitation_todo->refused;
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.invitation_todo.invalid_msg');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.invitation_todo.invalid_msg');
		}		
		$this->output();
	}

	private function _agreeinv($msg_id)
	{
		$this->xmlRoot = 'message';
		if ($msg_id > 0) {
			$invitationInfo = Better_User_DirectMessage::getInstance($this->uid)->getReceived($msg_id);
			$fuid = $invitationInfo['from_uid']; // 邀请人的id
			$poiInfo =Better_DAO_Todopoi::getInstance($this->uid)->getByMsgId($msg_id);
			$receiverUserInfo = Better_User::getInstance($fuid)->getUser();
			$message = '接受 {NICKNAME} 的邀请，打算和TA一起去{POINAME}。';
			$message = str_replace('{NICKNAME}', '@'.$receiverUserInfo['nickname'], $message);
			$message = str_replace('{POINAME}', $poiInfo['poi_name'], $message);
			// 发表我想去
			$post = array(
				'message'     => $message,
				'upbid'       => 0,
				'priv'        => 'public',
				'poi_id'      => $poiInfo['poi_id'],
				'type'        => 'todo',
				'passby_spam' => 1,
				'need_sync'   => 0
			);
			$bid = Better_User_Blog::getInstance($this->uid)->add($post);
			if ((float)$bid > 0) {
				if ($receiverUserInfo['uid'] && $receiverUserInfo['uid'] == $fuid) {						
					// 发送通知给邀请人，通知的类型为邀请类型，置状态为回复类型
					$content = "{NICKNAME}已经同意和你一起去{POINAME}，约个时间一起去吧。 ";
					$content = str_replace('{NICKNAME}',' @'.$this->userInfo['nickname'].' ',$content);
					$content = str_replace('{POINAME}',	' '.$poiInfo['poi_name'].' ',$content);
					$result = Better_User::getInstance(BETTER_SYS_UID)->notification()->invitationTodo()->send(array(
						'content' => $content,
						'receiver' => $fuid
					));
					if ($result['code'] == $result['codes']['SUCCESS']) {
						$data = array(
							'msg_id'       => $result['id'],
							'poi_id'       => $poiInfo['poi_id'],
							'poi_name'     => $poiInfo['poi_name'],
							'reply_msg_id' =>	$poiInfo['msg_id'],
							'dateline'=>time()
						);
						// 需要告诉好友这条邀请的相关poi信息
						Better_DAO_Todopoi::getInstance($fuid)->insert($data); // 新插入一条记录邀请POI id的记录
						// 更新改邀请的状态为已读
						Better_User_DirectMessage::getInstance($this->uid)->readed(	$poiInfo['msg_id']);
						$this->data[$this->xmlRoot] = $this->lang->invitation_todo->agreed;
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.invitation_todo.fail');
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.invitation_todo.fail');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.invitation_todo.fail');
			}		
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.invitation_todo.invalid_msg');
		}
		$this->output();
	}

	public function dealinvAction()
	{
		$this->needPost();
		$id = $this->getRequest()->getParam('id', 0);
		$dealing = $this->getRequest()->getParam('dealing', '');
		if ($dealing == 'agree') {
			$this->_agreeinv($id);
		} else if ($dealing == 'refuse') {
			$this->_refuseinv($id);
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.invitation_todo.invalid_action');	
			$this->output();
		}
	}
}