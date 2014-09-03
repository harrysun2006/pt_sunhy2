<?php

/**
 * 通知
 *
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Notification extends Better_User_Base
{
	protected static $instance = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}
	
	public function __call($method, $params)
	{
		$className = 'Better_User_Notification_'.ucfirst($method);
		
		if (class_exists($className)) {
			return call_user_func($className.'::getInstance', $this->uid);
		} else {
			return null;
		}
	}
	
	/**
	 * 忽略请求
	 * 
	 * @param $msg_id
	 * @return
	 */
	public function discardRequest($msg_id)
	{

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
			'readed' => 1
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
	public function getReceived($msg_id)
	{
		return Better_DAO_DmessageReceive::getInstance($this->uid)->get(array(
					'msg_id' => $msg_id,
					'uid' => $this->uid,
					));
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
	public function sendTpl($tpl, $data, $receiverUserInfo)
	{
		$tplPath = APPLICATION_PATH.'/configs/language/msgs/'.Better_Registry::get('language').'/'.$tpl.'.html';
		$content = file_get_contents($tplPath);

		foreach ($data as $k=>$v) {
			$content = str_replace('{'.strtoupper($k).'}', $v, $content);
		}

		return $this->send($content, $receiverUserInfo, false);
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
		$module = $params['module'] ? $params['module'] : 'direct_message';
		
		$flag = 0;
		$strip_tags==true && $content = strip_tags($content);
		$receiverUser = Better_User::getInstance($receiver);
		$receiverUserInfo = $receiverUser->getUser();
		$sent = 0;
		
		if ($receiverUserInfo['uid']) {
			if ($module=='direct_message' && $content=='') {
				$code = $codes['INVALID_CONTENT'];
			} else if ($receiverUserInfo['uid']==$this->uid) {
				$code = $codes['CANT_TO_SELF'];
			} else if (in_array($receiverUserInfo['uid'], $this->user->block()->getBlockedBy())) {
				$code = $codes['BLOCKED_BY_RECEIVER'];
			} else if($this->uid!=BETTER_SYS_UID && $module=='direct_message' && $receiverUserInfo['friend_sent_msg']=='1' && !in_array($this->uid, $receiverUser->friends)){
				$code = $codes['ONLY_RECEIVE_FRIENDS'];
			} else {
				if(!Better_Filter::getInstance()->filterBanwords($content)){
					$sent = Better_DAO_DmessageReceive::getInstance($receiverUserInfo['uid'])->insert(array(
									'uid' => $receiverUserInfo['uid'],
									'from_uid' => $this->uid,
									'content' => $content,
									'dateline' => time(),
									'module' => $module,
									'readed' => 0,
									));
					
					if ($sent) {
						$received = Better_DAO_DmessageSend::getInstance($this->uid)->insert(array(
									'uid' => $this->uid,
									'to_uid' => $receiverUserInfo['uid'],
									'content' => $content,
									'dateline' => time(),
									'module' => $module,
									));
									
						$userInfo = Better_User::getInstance($this->uid)->getUser();
	
						Better_Hook::factory(array(
							'Notify', 'Filter', 'User'
						))->invoke('DirectMessageSent', array(
							'uid' => $this->uid,
							'receiver_uid' => $receiverUserInfo['uid'],
							'msg_id' => $sent,
							'content' => $content,
							'module' => $module,
						));
			
						$code = $codes['SUCCESS'];
					}	
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
			'id' => $sent,
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
			);
		$results['msgs'] = Better_DAO_DmessageSend::getInstance($this->uid)->getAll(array(
						'uid' => $this->uid,
						'__since__' => $since,
						'order' => $desc==true ? 'DESC' : 'ASC',
						), $page.','.$count, 'limitPage');

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
	 * @param integer $page
	 * @param integer $count
	 * @return array
	 */
	public function getReceiveds($page=1, $count=BETTER_PAGE_SIZE, $since=0, $desc=true)
	{
		$results = array(
			'msgs' => array(),
			'users' => array(),
			);
		$results['msgs'] = Better_DAO_DmessageReceive::getInstance($this->uid)->getAll(array(
						'uid' => $this->uid,
						'__since__' => $since,
						'order' => $desc==true ? 'DESC' : 'ASC',
						), $page.','.$count, 'limitPage');

		if (count($results['msgs'])>0) {
			$uids = array();
			foreach ($results['msgs'] as $msg) {
				$uids[] = $msg['from_uid'];
			}
			$uids = array_unique($uids);
			$users = Better_DAO_User::getInstance()->getUsersByUids($uids);

			foreach ($users['rows'] as $user) {
				$results['users'][$user['uid']] = Better_Registry::get('user')->parseUser($user);
			}		
		}
		
		return $results;
	}
	
	public function getFriendsrequstInfo($uid){
		$results = array(
			'msgs' => array(),
		);
		$results['msgs'] = Better_DAO_FriendsRequestToMe::getInstance($this->uid)->getRequestInfo($uid);		
		return $results;
	}
	
	public function getDirectmesssageInfo($msgid){
		$results = array(
			'msgs' => array(),
		);
		$results['msgs'] = Better_DAO_DmessageReceive::getInstance($this->uid)->getDirectmesssageInfo($msgid);		
		return $results;
	}
	
	public function getFollowrequstInfo($uid){
		$results = array(
			'msgs' => array(),
		);
		//$results['msgs'] = Better_DAO_FollowRequest::getInstance($this->uid)->getRequestInfo($uid);		
		return $results;
	}
	
	
}