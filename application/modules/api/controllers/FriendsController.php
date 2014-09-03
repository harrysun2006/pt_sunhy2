<?php

/**
 * 好友
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_FriendsController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		
		$this->xmlRoot = 'user';
		
		$this->auth();
	}	
	
	/**
	 * 9.16 举报用户
	 * 
	 * @return
	 */
	public function reportAction()
	{
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id>0) {
			
		} else {
			
		}
		
		$this->output();
	}
	
	/**
	 * 9.15 拒绝好友请求
	 * 
	 * @return
	 */
	public function refuseAction()
	{
		$this->xmlRoot = 'message';
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id>0) {
			if (!in_array($id, $this->user->friends)) {
				if ($this->user->friends()->hasRequestToMe($id)) {
					$result = $this->user->friends()->reject($id);
					if ($result) {
						$this->data[$this->xmlRoot] = $this->lang->friends->request->rejected;
					} else {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->serverError();
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.friends.invalid_request');
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.friends.already_friend_cant_refuse');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.friends.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 9.14 通过好友请求
	 * 
	 * @return
	 */
	public function agreeAction()
	{
		$this->xmlRoot = 'user';
		$id = (int)$this->getRequest()->getParam('id', 0);
		$this->needPost();
		
		if ($id>0) {
			
			if (!in_array($id, $this->user->friends)) {
				$result = $this->user->friends()->agree($id);
				$codes = &$result['codes'];
	
				switch ($result['code']) {
					case $codes['INVALID_REQUEST']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.friends.already_refused');			
						break;
					case $codes['SUCCESS']:
						$this->user->push('friends', $id);
						$this->data[$this->xmlRoot] = $this->api->getTranslator('user')->translate(array(
							'data' => &$this->userInfo
							));
						break;
					case $codes['ALREADY']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.friends.already_friend');
						break;
					case $codes['FAILED']:
					default:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->serverError();
						break;
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.friends.already_friend');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.friends.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 9.13 所有好友请求
	 * @deprecated
	 * 
	 */
	public function pendingAction()
	{
		$this->xmlRoot = 'notifications';
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
		$this->output();
	}
	
	/**
	 * 9.12 删除好友
	 * 
	 * @return
	 */
	public function removeAction()
	{
		$this->needPost();
		
		$this->xmlRoot = 'user_concise';
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id>0 && in_array($id, $this->user->friends)) {
			$result = $this->user->friends()->delete($id);
			if ($result) {
				$this->user->clean('friends', $id);
				
				$this->data[$this->xmlRoot] = $this->api->getTranslator('user_concise')->translate(array(
					'data' => Better_User::getInstance($id)->getUserInfo(),
					'userInfo' => &$this->userInfo,
					));
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->serverError();
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.friends.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 9.11 发起好友请求
	 * 
	 * @return
	 */
	public function requestAction()
	{
		//$this->needPost();
		
		//$this->needSufficientKarma();
		
		$this->xmlRoot = 'user_concise';
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id>0) {
			$result = $this->user->friends()->request($id);
			$codes = &$result['codes'];

			switch ($result['result']) {
				case $codes['PENDING']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.friends.request_is_pending');
					break;
				case $codes['BLOCKED']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.friends.user_are_blocked');
					break;
				case $codes['ALREADY']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.friends.is_already_friends');
					break;
				case $codes['REQUESTED']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.friends.duplicate_request');
					break;
				case $codes['BLOCKEDBY']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.friends.you_are_blocked');
					break;
				case $codes['CANTSELF']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.friends.cant_to_self');
					break;
				case $codes['CANTSYS']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.friends.cant_to_sys');
					break;
				case $codes['KARMA_TOO_LOW']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.friends.too_low_larma');
					break;
				case $codes['SUCCESS']:
					$this->friends[] = $id;
					
					$this->data[$this->xmlRoot] = $this->api->getTranslator('user_concise')->translate(array(
						'data' => Better_User::getInstance($id)->getUserInfo(),
						'userInfo' => &$this->userInfo,
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
			$this->error('error.friends.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 发起加多个好友的请求。每次最多加50个好友，限制从配置文件读取。
	 * 
	 */
	public function requestsAction()
	{
		$this->needPost();
		//$this->needSufficientKarma();
		
		$this->xmlRoot = 'requests';
		$ids = trim($this->getRequest()->getParam('ids', ''));
		
		$maxfindfriends = Better_Config::getAppConfig()->api->maxfindfriends;
		$users = explode(',', $ids);
		array_splice($users, $maxfindfriends);
		
		$result = $this->user->friends()->requests($users);

		$this->data[$this->xmlRoot]['sent'] = $result['resultnum'] + $result['pendding'];
		$this->output();
	}

	/**
	 * 10.16 拒绝全部好友请求
	 */
	public function refusesAction()
	{
		$this->needPost();
		$this->xmlRoot = 'result';
		$since = (int)$this->getRequest()->getParam('since_id', 0);
		$count = (int)$this->getRequest()->getParam('count', $this->count);
		$count <= 0 && $count = $this->count;

		$fr = $this->user->notification()->FriendRequest();
		$params = array(
			'since' => $since,
			'count' => $count,
			'type' => 'friend_request',
			'delived' => 1,
			'act_result' => 0,
			'force' => 1,
		);
		$frs = $fr->getReceiveds($params);
		$total = min($frs['count'], $count);
		$refuses = 0;
		foreach ($frs['rows'] as $row) {
			$refuses += $this->user->friends()->reject($row['from_uid']);
		}
		// 更新缓存
		$cacher = $this->user->cache();
		$d = (int)$cacher->get('friend_request_count') - $refuses;
		$d < 0 && $d = 0;
		$cacher->set('friend_request_count', $d);
		// 返回
		$msg = $this->lang->friends->refuses;
		// $msg = str_replace('{REFUSED}', $refuses, $msg);
		// $msg = str_replace('{TOTAL}', $total, $msg);
		$this->data[$this->xmlRoot] = array(
			'message' => $msg,
			'total' => $total,
			'refused' => $refuses,
		);
		$this->output();
	}

	/**
	 * 10.14 通过全部好友请求
	 */
	public function agreesAction()
	{
		$this->needPost();
		$this->xmlRoot = 'result';
		$since = (int)$this->getRequest()->getParam('since_id', 0);
		$count = (int)$this->getRequest()->getParam('count', $this->count);
		$count <= 0 && $count = $this->count;

		$fr = $this->user->notification()->FriendRequest();
		$params = array(
			'since' => $since,
			'count' => $count,
			'type' => 'friend_request',
			'delived' => 1,
			'act_result' => 0,
			'force' => 1,
		);
		$frs = $fr->getReceiveds($params);
		$total = min($frs['count'], $count);
		$agreed = 0;
		foreach ($frs['rows'] as $row) {
			$r = $this->user->friends()->agree($row['from_uid']);
			if ($r['code'] == $r['codes']['SUCCESS']) $agreed++;
		}
		// 更新缓存
		$cacher = $this->user->cache();
		$d = (int)$cacher->get('friend_request_count') - $agreed;
		$d < 0 && $d = 0;
		$cacher->set('friend_request_count', $d);
		// 返回
		$msg = $this->lang->friends->agrees;
		// $msg = str_replace('{AGREED}', $agreed, $msg);
		// $msg = str_replace('{TOTAL}', $total, $msg);
		$this->data[$this->xmlRoot] = array(
			'message' => $msg,
			'total' => $total,
			'agreed' => $agreed,
		);
		$this->output();
	}
}