<?php

/**
 * 好友
 * 
 * @package 
 * @author 
 *
 */
class Public_FriendsController extends Better_Controller_Public
{
	public function init()
	{
		parent::init();	
		$this->xmlRoot = 'user';
		$this->auth();
	}	

	/**
	 * 9.13 所有好友请求
	 * @deprecated
	 * 
	 */
	public function friendrequestAction()
	{
		$this->xmlRoot = 'users';
		$id = $this->uid;

		$rows = $this->user->notification()->friendRequest()->getReceiveds(array(
			'type' => 'friend_request',
			'page' => $this->page,
			'count' => $this->count
			));
			
		foreach ($rows['rows'] as $k=>$v) {		
			$data['uid'] = $v['userInfo']['uid'];
			$data['nickname'] = $v['userInfo']['nickname'];
			$data['dateline'] = $v['dateline'];
			
			$this->data[$this->xmlRoot][]['user'] = $data;
		}

		$this->output();
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
						$this->error('error.freinds.invalid_request');
						break;
					case $codes['SUCCESS']:
						$this->user->friends[] = $id;
						
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
						$this->error('error.freinds.already_refused');
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
				$this->friends = array_diff($this->user->friends, (array)$id);
				
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
		$this->needPost();
		
		$this->needSufficientKarma();
		
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
	
}