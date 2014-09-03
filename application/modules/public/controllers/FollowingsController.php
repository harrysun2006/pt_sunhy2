<?php

/**
 * 用户关注
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_FollowingsController extends Better_Controller_Public
{
	public function init()
	{
		parent::init();
		
		$this->xmlRoot = 'user';
		
		$this->needPost();
		$this->auth();
	}	
	
	/**
	 * 9.4取消关注某人
	 * 
	 * @return
	 */
	public function destroyAction()
	{
		$this->xmlRoot = 'user_concise';
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id>0) {
			$result = $this->user->follow()->delete($id);
			if ($result==1) {
				$this->user->followings = array_diff($this->user->followings, (array)$id);
				
				$this->data[$this->xmlRoot] = $this->api->getTranslator('user_concise')->translate(array(
					'data' => Better_User::getInstance($id)->getUserInfo(),
					'userInfo' => &$this->userInfo
					));
			} else if ($result==-1) {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.followings.not_following');
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->serverError();
			}
			
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.followings.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 9.3 拒绝关注请求
	 * 
	 * @return
	 */
	public function refuseAction()
	{
		$id = (int)$this->getRequest()->getParam('id', 0);
		$this->xmlRoot = 'message';
		
		if ($id && $id!=$this->userInfo['uid']) {
			$result = $this->user->follow()->reject($id);
			$codes = &$result['codes'];

			switch ($result['code']) {
				case $codes['HAS_NO_REQUEST']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.followings.has_no_request');
					break;
				case $codes['IS_FOLLOWER']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.followings.is_follower');
					break;
				case $codes['SUCCESS']:
					$this->data[$this->xmlRoot] = $this->lang->follow->request->rejected;
					break; 
				default:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->serverError();
					break;
			}

		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.followings.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 9.2同意关注请求
	 * 
	 * @return
	 */
	public function agreeAction()
	{
		$this->xmlRoot = 'agree';
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		if ($id && $id!=$this->userInfo['uid']) {
			$result = $this->user->follow()->agree($id);
			$codes = &$result['codes'];
			
			switch ($result['code']) {
				case $codes['ALREADY']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.followings.already_follow');
					break;
				case $codes['INVALID_REQUEST']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.followings.invalid_request');
					break;
				case $codes['INVALIDUSER']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.followings.user_not_found');
					break;
				case $codes['SUCCESS']:
					$message = $this->parseAchievements();
					$message = $this->langAll->global->follow->success.' '.$message;
					
					$this->data[$this->xmlRoot] = array(
						'message' => $message,
						'user' => $this->api->getTranslator('user')->translate(array(
							'data' => &$this->userInfo,
							)),
						);
					break;
				case $codes['FAILED']:
				default:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->serverError();
					break;
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.followings.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 9.1 关注某人
	 * 
	 * @return
	 */
	public function createAction()
	{
		$this->xmlRoot = 'user_concise';
		$uid = (int)$this->getRequest()->getParam('id', 0);
		
		$this->needSufficientKarma();

		if ($uid && $uid!=$this->userInfo['uid']) {
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUser();
			if ($userInfo['uid']) {
				$return = $this->user->follow()->request($userInfo['uid']);
				
				$codes = &$return['codes'];
				switch ($return['result']) {
					case $codes['DUPLICATED_REQUEST']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.followings.duplicate_request');
						break;
					case $codes['PENDING']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.followings.follow_need_validate');
						break;
					case $codes['INSUFFICIENT_KARMA']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.followings.insufficient_karma');
						break;
					case $codes['ALREADY']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.followings.already_follow');
						break;
					case $codes['BLOCKEDBY']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.followings.your_are_blocked');
						break;
					case $codes['BLOCKED']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.followings.you_blocked_him');
						break;
					case $codes['CANTSELF']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.followings.cant_follow_self');
						break;
					case $codes['INVALIDUSER']:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.followings.invalid_user');
						break;
					case $codes['SUCCESS']:
						$this->user->followings[] = $userInfo['uid'];
						
						$this->data[$this->xmlRoot] = $this->api->getTranslator('user_concise')->translate(array(
							'data' => &$userInfo,
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
				$this->error('error.followings.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.followings.invalid_user');
		}
		
		$this->output();
	}
}