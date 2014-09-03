<?php

/**
 * 发送Email
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Email extends Better_Hook_Base
{
	public function onPoiPollSubmitted(array $params)
	{
		
	}
		
	public function onPoiCreated(array $params)
	{
		
	}
	
	public function onBlogPosted(array $params)
	{
	}
	
	public function onBlogDeleted(array $params)
	{
	}
	
	public function onFollowSomebody(array $params)
	{
		$uid = $params['uid'];
		$following_uid = $params['uid'];
	}	
	
	public function onBlockedSomebody(array $params)
	{
		$uid = $params['uid'];
		$blocked_uid = $params['blocked_uid'];
	}	
	
	public function onUserCreated(array $params)
	{
		$userInfo = &$params['userInfo'];		
		if($_POST['login_type']=='local'){
			Better_Email_Signup::send($userInfo);
		} else {			
			$uid = $userInfo['uid'];
			Better_User_Bind_Email::getInstance($uid)->bind($userInfo['email']);
		}		
	}
	
	public function onUserChanged(array $params)
	{
		$oldUserInfo = &$params['oldUserInfo'];
		$newUserInfo = &$params['newUserInfo'];
		$newEmail = &$params['new_email'];
		$uid = $oldUserInfo['uid'];

		if ($newEmail) {
			Better_Email_Bind::send(array(
				'email' => $newEmail,
				'uid' => $oldUserInfo['uid'],
				'nickname'=> $newUserInfo['nickname'],
				'userChange'=> 1
				));			
		}
	}
	
	public function onBeforeQbsQuery(array $params)
	{
	}
	
	public function onAfterQbsQuery(array $params)
	{
	}	
	
	public function onAttachmentUploaded(array $params)
	{
	}
	
	public function onFollowRequest(array $params)
	{
		
	}
	
	public function onDirectMessageSent(array $params)
	{
	}
	
	public function onBlogReplyPosted(array $params)
	{
	}	
	
	public function onAddedFavorite(array $params)
	{
	}
	
	public function onUserDeleted(array $params)
	{
	}

	public function onUserLogin(array $params)
	{
	}
	
	public function onUserLogout(array $params)
	{
	}
	
	public function onUnfollowSomebody(array $params)
	{
		
	}
	
	public function onUserCheckin(array $params)
	{
		
	}
	
	public function onFriendRequest(array $params)
	{
		
	}
	
	public function onFriendWithSomebody(array $params)
	{
		
	}	
	
	public function onUnblockSomebody(array $params)
	{
		
	}
	
	public function onUnfriendWithSomebody(array $params)
	{
		
	}
	
	public function onRejectFriendRequest(array $params)
	{
		
	}
	
	public function onBindcell(array $params){
		$data = $params['data'];
		$data['uid'] = $params['uid'];
				
		Better_Email_Bindcell::send($data);
	}
}