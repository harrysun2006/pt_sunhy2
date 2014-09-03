<?php

/**
 * 用户通知类型基类
 * 
 * @package Better.User.Notification
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Notification_Base
{
	protected $uid = 0;
	protected $user = null;
	protected $type = 'direct_message';
	
	protected function __construct($uid)
	{
		$this->uid = (int)$uid;
		$this->user = Better_User::getInstance($this->uid);
	}
	
	/**
	 * 
	 * 取所有未读通知
	 * @param array $params
	 */
	public function getUnreaded(array $params)
	{
		$result = array(
			'rows' => array(),
			'count' => 0
			);
		
		$page = $params['page'] ? (int)$params['page'] : 1;
		$pageSize = $params['page_size'] ? (int)$params['page_size'] : BETTER_PAGE_SIZE;
		$since = $params['since'] ? (int)$params['since'] : 0;
		
		if ($since) {
			$rows = Better_DAO_DmessageReceive::getInstance($this->uid)->getMines(array(
				'readed' => 0,
				'page' => $page,
				'count' => $pageSize,
				'since' => $since
				));
				
			foreach ($rows['rows'] as $k=>$row) {
				$row['text'] = self::parseText($row);
				
				$result['rows'][$k] = $row;
			}
		}
		
		return $result;		
	}
	
	public function pushReadStateToPpns(array $ids)
	{
		$msgId = $this->getSeq();
		Better_DAO_DmessageReceive::getInstance($this->uid)->insert(array(
			'msg_id' => $msgId,
			'uid' => $this->uid,
			'from_uid' => $this->uid,
			'content' => implode(',', $ids),
			'dateline' => time(),
			'type' => 'notification_readed',		
			));
		
		Better_Ppns::getInstance()->simplePushToUid($this->uid, true);
	}
	
	/**
	 * 清理
	 * 
	 * @return
	 */
	public function clear($uid)
	{
		return Better_DAO_DmessageReceive::getInstance($this->uid)->deleteByCond(array(
			'uid' => $this->uid,
			'type' => $this->type,
			'from_uid' => $uid
			));
	}
	
	/**
	 * 计数
	 * 
	 * @return integer
	 */
	public function count(array $params=array())
	{
		return Better_DAO_DmessageReceive::getInstance($this->uid)->getMinesCount($params);
	}
	
	public function discard($msgId)
	{
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1,
			'INVALID_MSG' => -1,
			);
		$code = $codes['FAILED'];	
		
		if ($msgId) {
			$data = $this->getReceived($msgId);
			if ($data['uid']==$this->uid) {
				$this->readed($msgId);
				$code['SUCCESS'];
			} else {
				$code = $codes['INVALID_MSG'];
			}
		} else {
			$code = $codes['INVALID_MSG'];
		}
		
		return array(
			'codes' => &$codes,
			'code' => $code,
			);				
	}
	
	/**
	 * 标记为已读
	 * 
	 * @param $msg_id
	 * @return bool
	 */
	public function readed($msg_id)
	{
		return Better_DAO_DmessageReceive::getInstance($this->uid)->update(array(
			'readed' => 1,
			'delived' => 1,
			'im_delived' => 1
			), $msg_id);
	}

	/**
	 *	删除一个收到的通知
	 *
	 * @param integer $msg_id
	 *
	 */
	public function delReceived($msg_id)
	{
		$deleted = Better_DAO_DmessageReceive::getInstance($this->uid)->deleteByCond(array(
						'uid' => $this->uid,
						'msg_id' => $msg_id,
						));

		$userInfo = Better_User::getInstance($this->uid)->getUser();
		Better_User::getInstance($this->uid)->updateUser(array(
			'received_msgs' => $userInfo['received_msgs']-1,
			));
						
		return $deleted;
	}
	
	/**
	 *	删除一个发出的通知
	 *
	 * @param integer $msg_id
	 *
	 */
	public function delSent($msg_id)
	{
		$deleted = Better_DAO_DmessageSend::getInstance($this->uid)->deleteByCond(array(
						'uid' => $this->uid,
						'msg_id' => $msg_id,
						));

		$userInfo = Better_User::getInstance($this->uid)->getUser();
		Better_User::getInstance($this->uid)->updateUser(array(
			'sent_msgs' => $userInfo['sent_msgs']-1,
			));
						
		return $deleted;
	}

	/**
	 * 获取一个通知
	 *
	 * @param integer $msg_id
	 * @return array
	 */
	public function &getReceived($msg_id)
	{
		$row = Better_DAO_DmessageReceive::getInstance($this->uid)->get(array(
					'msg_id' => $msg_id,
					'uid' => $this->uid,
					));
					
		if ($row['sid']) {
			$tmp = Better_Game_Hunting::getInstance($this->uid)->getSession($row['sid']);
			$row['poi_id'] = $tmp['poi_id'];
		}
					
		return $row;
	}
	
	/**
	 * 获取一个发送过的通知
	 *
	 * @param integer $msg_id
	 * @return array
	 */
	public function getSent($msg_id)
	{
		return Better_DAO_DmessageSend::getInstance($this->uid)->get(array(
					'msg_id' => $msg_id,
					'uid' => $this->uid,
					));
	}
		
	/**
	 *  使用消息模板发送通知
	 *
	 * @param array $data
	 * @param array $receiverUserInfo
	 * @return integer
	 */
	public function sendTpl($tpl, $data, $userInfo)
	{
		
		$tplPath = APPLICATION_PATH.'/configs/language/msgs/'.$userInfo['language'].'/'.$tpl.'.html';
		$content = file_get_contents($tplPath);

		foreach ($data as $k=>$v) {
			$content = str_replace('{'.strtoupper($k).'}', $v, $content);
		}
		
		return $this->send(array('content'=>$content, 'receiver'=>$userInfo['uid']));
	}
	
	/**
	 * 发送一个站内信
	 *
	 * @param array $receiverUserInfo
	 * @param string $content
	 * @return integer
	 */
	public function send(array $params)
	{
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1,
			'INVALID_RECEIVER' => -1,
			'INVALID_CONTENT' => -2,
			'CANT_TO_SELF' => -3,
			'BLOCKED_BY_RECEIVER' => -4,
			'WORDS_R_BANNED'=> -5,
			'ONLY_RECEIVE_FRIENDS' => -6
			);
		$content = $params['content'];
		$receiver = $params['receiver'];
		$strip_tags = (isset($params['strip_tags']) && $params['strip_tags']) ? true : false;
		$sid = $params['sid'];
		
		$flag = 0;
		$strip_tags==true && $content = strip_tags($content);
		$receiverUser = Better_User::getInstance($receiver);
		$receiverUserInfo = $receiverUser->getUser();
		$sent = 0;
		$type = isset($params['type']) ? $params['type'] : $this->type;
		
		
		
		
		

		if ($receiverUserInfo['uid']) {
			
			if (($type=='direct_message' || $type=='invitation_todo') && $content=='') {
				$code = $codes['INVALID_CONTENT'];
			} else if (!defined('IN_API') && $receiverUserInfo['uid']==$this->uid) {
				$code = $codes['CANT_TO_SELF'];
			} else if (!defined('IN_GAME') && in_array($receiverUserInfo['uid'], $this->user->block()->getBlockedBy())) {
				$code = $codes['BLOCKED_BY_RECEIVER'];
			} else if($this->uid!=BETTER_SYS_UID && $type=='direct_message' && $receiverUserInfo['friend_sent_msg']=='1' && !in_array($this->uid, $receiverUser->friends)){
				$code = $codes['ONLY_RECEIVE_FRIENDS'];
			} else {
				
				if(!Better_Filter::getInstance()->filterBanwords($content)){	
					
					if(Better_Config::getAppConfig()->sysmsg->switch){						
						$tempuserinfo = Better_Registry::get('sess')->get('uid');						
						if($tempuserinfo!=$this->uid && Better_Config::getAppConfig()->user->sys_user_id==$this->uid){						
							$this->uid = Better_Config::getAppConfig()->sysmsg->uid;												
						}
									
					}	
					Better_Log::getInstance()->logInfo($this->uid,'syssms');			
					$msgId = $this->getSeq();					
					$this->user->getUserInfo();
					$senderHash = serialize($this->user->getUserInfo());
	
					$sent = Better_DAO_DmessageReceive::getInstance($receiverUserInfo['uid'])->insert(array(
									'msg_id' => $msgId,
									'uid' => $receiverUserInfo['uid'],
									'from_uid' => $this->uid,
									'content' => $content,
									'dateline' => time(),
									'type' => $type,
									'readed' => 0,
									'sid' => $sid,
									'sender' => $senderHash
									));
		
					if ($sent && ($type=='direct_message' || $type=='invitation_todo' || $type=='')) {
						$received = Better_DAO_DmessageSend::getInstance($this->uid)->insert(array(
									'msg_id' => $msgId,
									'uid' => $this->uid,
									'to_uid' => $receiverUserInfo['uid'],
									'content' => $content,
									'dateline' => time(),
									'type' => $type,
									));
									
						$userInfo = Better_User::getInstance($this->uid)->getUser();
	
						if ($this->type=='direct_message') {
							$hooks = array('Notify');
							if (!$params['skip_filter']) $hooks[] = 'Filter';
							$hooks[] = 'User';
							$hooks[] = 'Ppns';
							$hooks[] = 'Cache';
							Better_Hook::factory($hooks)->invoke('DirectMessageSent', array(
								'uid' => $this->uid,
								'receiver_uid' => $receiverUserInfo['uid'],
								'msg_id' => $msgId,
								'content' => $content,
								'type' => $type,
							));
						}else if($this->type=='invitation_todo'){
							Better_Hook::factory(array(
								'Notify', 'Filter', 'User', 'Ppns', 'Cache'
							))->invoke('InviteTodoSent', array(
								'uid' => $this->uid,
								'receiver_uid' => $receiverUserInfo['uid'],
								'msg_id' => $msgId,
								'content' => $content,
								'type' => $type,
							));
						}
					}			
					
					$code = $codes['SUCCESS'];
				}else{
					$code = $codes['WORDS_R_BANNED'];
				}				
			}
		} else {
			$code = $codes['INVALID_RECEIVER'];
		}
		
		$result = array(
			'codes' => &$codes,
			'code' => $code,
			'id' => $msgId,
			);

		return $result;
	}

	/**
	 * 获取所有我发送的消息
	 *
	 * @param integer $page
	 * @param integer $count
	 * @return array
	 */
	public function getSents($page=1, $count=BETTER_PAGE_SIZE, $since=0, $desc=true)
	{
		$results = array(
			'msgs' => array(),
			'users' => array(),
			'count' => 0,
			);
		$results['msgs'] = Better_DAO_DmessageSend::getInstance($this->uid)->getAll(array(
						'uid' => $this->uid,
						'__since__' => $since,
						'order' => $desc==true ? 'DESC' : 'ASC',
						), $page.','.$count, 'limitPage');
		$results['count'] = Better_DAO_DmessageSend::getInstance($this->uid)->getCount(array(
			'uid' => $this->uid,
			'__since__' => $since
			));
		
		if (count($results['msgs'])>0) {
			$uids = array();
			foreach ($results['msgs'] as $msg) {
				$uids[] = $msg['to_uid'];
			}
			$uids = array_unique($uids);
			$users = Better_DAO_User::getInstance()->getUsersByUids($uids);

			foreach ($users['rows'] as $user) {
				$results['users'][$user['uid']] = Better_Registry::get('user')->parseUser($user);
			}

		}
		
		return $results;
	}

	/**
	 * 获取所有我收到的消息
	 *
	 * fixbug0001233: 处理过期的未处理的好友请求
	 * @param integer $page
	 * @param integer $count
	 * @return array
	 */
	public function getReceiveds(array $params=array())
	{
		($this->type!='' && !$params['type']) && $params['type'] = $this->type;
		if($params['type'] == 'direct_message'){
			$params['type'] = array('direct_message','invitation_todo');
		}
		if (!isset($params['force'])) {
			if (defined('IN_API') && IN_API===true && !isset($params['readed'])) {
				$params['delived'] = false;
			} else if (!defined('IN_API') && $params['delived']===null) {
				$params['delived'] = null;
			}
		}
		
		$tm = time();
		$rows = Better_DAO_DmessageReceive::getInstance($this->uid)->getMines($params);
		$ids = array();
		$todoids = array();
		$todoPoi = Better_DAO_Todopoi::getInstance($this->uid);
		
		$_friends_msg_ids = array();
		
		foreach ($rows['rows'] as $k=>$row) {
			//判断出已经过期的好友请求
			if ( $row['type'] == 'friend_request' && $row['dateline'] < $tm - 3600 * 24 * 3 && $row['delived'] == 0) {
				$_friends_msg_ids[] = $row['msg_id'];
				continue;
			}
			
			$row['text'] = self::parseText($row);
			if($row['type'] =='invitation_todo'){
				//查看邀请的时候需要查找POI信息
				$poiInfo = $todoPoi->getByMsgId($row['msg_id']);
				$row['invitedpoi'] =  $poiInfo;
				$todoids[] = $row['msg_id'];
			}else{
				$ids[] = $row['msg_id'];
			}
			$rows['rows'][$k] = $row;
		}

		if (defined('IN_API') && IN_API===true && (isset($params['keep']) && $params['keep']===false)) {
			$this->updateDelived($ids);
			if(!empty($todoids)){
				$this->updateDelived($todoids);
			}
		}
		
		if (count($ids) && !$params['keep'] && $params['type'] && ($params['type']!='direct_message') && ($params['type']!='invitation_todo')) {
			$this->updateReaded($ids);
		}

		//处理过期的好友请求 现在改成已读
		//$this->updateDelived($_friends_msg_ids);
		foreach ($_friends_msg_ids as $v) {
			$this->readed($v);
		}
		
		return $rows;
	}	
	
	/**
	 * 
	 * 同步取数据
	 * @param array $params
	 */
	protected function syncGetReceiveds(array $params=array())
	{
		($this->type!='' && !$params['type']) && $params['type'] = $this->type;

		if (!isset($params['force'])) {
			if (defined('IN_API') && IN_API===true && !isset($params['readed'])) {
				$params['delived'] = false;
			}
		}

		$rows = Better_DAO_DmessageReceive::getInstance($this->uid)->syncGetMines($params);
		$ids = array();
		foreach ($rows['rows'] as $k=>$row) {
			$rows['rows'][$k] = $row;
			
			$ids[] = $row['msg_id'];
		}
			
		return $rows;		
	}
	
	/**
	 * 更新消息的是否递送
	 * 
	 * @param $msgIds
	 * @return null
	 */
	public function updateDelived(array $msgIds)
	{
		if (count($msgIds)>0) {
			$flag = Better_DAO_DmessageReceive::getInstance($this->uid)->updateByCond(array(
				'delived' => '1'
				), array(
					'msg_id' => $msgIds,
					'uid' => $this->uid
				));
		}
	}
	
	/**
	 * 根据会话id标记已发送
	 * 
	 * @return null
	 */
	public function updateDelivedBySid($sid)
	{
		Better_DAO_DmessageReceive::getInstance($this->uid)->updateByCond(array(
			'delived' => '1'
			), array(
				'sid' => $sid,
				'uid' => $this->uid
			));
	}
	
	/**
	 *	更新消息是否已读
	 *
	 *@param $msgIds
	 *@return null
	 */
	public function updateReaded(array $msgIds)
	{
		if (count($msgIds)>0) {
			Better_DAO_DmessageReceive::getInstance($this->uid)->updateByCond(array(
				'readed' => '1'
				), array(
					'msg_id' => $msgIds,
					'uid' => $this->uid
				));
		}		
		
		return true;
	}
	
	protected static function parseText(array $row)
	{
		$text = '';
		$lang = Better_Language::load()->notification;
		
		switch ($row['type']) {
			case 'follow_request':
				$userInfo = Better_User::getInstance($row['from_uid'])->getUser();
				$text = str_replace('{NICKNAME}', $userInfo['nickname'], $lang->follow_request);				
				break;
			case 'direct_message':
				$text = $row['content'];
				break;
			case 'friend_request':
				$userInfo = Better_User::getInstance($row['from_uid'])->getUser();
				$text = str_replace('{NICKNAME}', $userInfo['nickname'], $lang->friend_request);
				break;
		}
		
		return $text;
	}
	
	/**
	 * 获取序列并写入分配表
	 * 
	 * @return integer
	 */
	protected function getSeq()
	{
		$seq = Better_DAO_Notify_Sequence::getInstance()->get();
		
		Better_DAO_Notify_Assign::getInstance()->insert(array(
			'seq' => $seq,
			'uid' => $this->uid
			));
		
		return $seq;
	}	
		
	/**
	 * 
	 * 通知同步
	 * @param unknown_type $fromId
	 */
	public function sync($fromId)
	{
		$result = array(
			'readed' => array(),
			'unread' => array()
			);
			
		$offset = Better_Config::getAppConfig()->notification_sync_offset;
		$fromTime = time()-$offset;
					
		$tmp = $this->syncGetReceiveds(array(
			'page' => 1,
			'count' => 999,
			'since' => $fromId,
			'type' => 'direct_message',
			'readed' => 1,
			'from_time' => $fromTime,
			'keep' => 1,
			));
		$result['readed'] = $tmp['rows'];
		
		$tmp = $this->syncGetReceiveds(array(
			'page' => 1,
			'count' => 999,
			'since' => $fromId,
			'type' => 'direct_message',
			'readed' => 0,
			'from_time' => $fromTime,
			'keep' => 1,
			));
		$result['unread'] = $tmp['rows'];		
		
		//	关注请求
		$tmp = $this->syncGetReceiveds(array(
			'page' => 1,
			'count' => 999,
			'since' => $fromId,
			'type' => 'follow_request',
			'act_result' => 1,
			'from_time' => $fromTime,
			'force' => true,
			'keep' => 1,
			));
		$result['follow_request_agree'] = $tmp['rows'];
		
		$tmp = $this->syncGetReceiveds(array(
			'page' => 1,
			'count' => 999,
			'since' => $fromId,
			'type' => 'follow_request',
			'act_result' => 2,
			'from_time' => $fromTime,
			'force' => true,
			'keep' => 1,
			));
		$result['follow_request_reject'] = $tmp['rows'];		
		
		$tmp = $this->syncGetReceiveds(array(
			'page' => 1,
			'count' => 999,
			'since' => $fromId,
			'type' => 'follow_request',
			'readed' => 1,
			'from_time' => $fromTime,
			'act_result' => 0,
			'force' => true,
			'keep' => 1,
			));
		$result['follow_request_readed'] = $tmp['rows'];			
		
		$tmp = $this->syncGetReceiveds(array(
			'page' => 1,
			'count' => 999,
			'since' => $fromId,
			'type' => 'follow_request',
			'readed' => 0,
			'from_time' => $fromTime,
			'act_result' => 0,
			'force' => true,
			'keep' => 1,
			));
		$result['follow_request_unread'] = $tmp['rows'];				

		//	好友请求
		$tmp = $this->syncGetReceiveds(array(
			'page' => 1,
			'count' => 999,
			'since' => $fromId,
			'type' => 'friend_request',
			'act_result' => 1,
			'from_time' => $fromTime,
			'force' => true,
			'keep' => 1,
			));
		$result['friend_request_agree'] = $tmp['rows'];
		
		$tmp = $this->syncGetReceiveds(array(
			'page' => 1,
			'count' => 999,
			'since' => $fromId,
			'type' => 'friend_request',
			'act_result' => 2,
			'from_time' => $fromTime,
			'force' => true,
			'keep' => 1,
			));
		$result['friend_request_reject'] = $tmp['rows'];		
		
		$tmp = $this->syncGetReceiveds(array(
			'page' => 1,
			'count' => 999,
			'since' => $fromId,
			'type' => 'friend_request',
			'readed' => 1,
			'from_time' => $fromTime,
			'act_result' => 0,
			'force' => true,
			'keep' => 1,
			));

		$result['friend_request_readed'] = $tmp['rows'];			
		
		$tmp = $this->syncGetReceiveds(array(
			'page' => 1,
			'count' => 999,
			'since' => $fromId,
			'type' => 'friend_request',
			'readed' => 0,
			'from_time' => $fromTime,
			'act_result' => 0,
			'force' => true,
			'keep' => 1,
			));
		$result['friend_request_unread'] = $tmp['rows'];						

		return $result;
	}
}