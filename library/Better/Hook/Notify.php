<?php

/**
 * 发通知
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Notify extends Better_Hook_Base
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
	
	public function onBeforeQbsQuery(array $params)
	{
	}
	
	public function onAfterQbsQuery(array $params)
	{
	}
	
	public function onFollowSomebody(array $params)
	{
		/*$uid = (int)$params['uid'];
		$followingUid = (int)$params['following_uid'];
		
		Better_DAO_DmessageReceive::getInstance($followingUid)->updateByCond(array(
			'act_result' => '1',
			'readed' => '1'
			), array(
				'uid' => $followingUid,
				'from_uid' => $uid,
				'type' => 'follow_request'
			));*/
	}
	
	public function onBlockedSomebody(array $params)
	{
	}
	
	public function onUserCreated(array $params)
	{
	}
	
	public function onUserChanged(array $params)
	{
	}
	
	public function onAttachmentUploaded(array $params)
	{
	}
	
	public function onFollowRequest(array $params)
	{
		$toFollowUid = $params['to_follow'];
		$uid = $params['uid'];
		$duplicated = $params['duplicated'];
		
		if (!$duplicated) {
			/*Better_User::getInstance($toFollowUid)->notify()->store(array(
				'type' => 'follow_request',
				'content' => '',
				'data' => $uid
				));*/
		}
	}
	
	public function onDirectMessageSent(array $params)
	{
		$receiverUid = $params['receiver_uid'];
		$uid = $params['uid'];
		$msg_id = $params['msg_id'];
		
		Better_User::getInstance($receiverUid)->notify()->store(array(
			'type' => 'direct_message',
			'content' => '',
			'data' => $msg_id
			));
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
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];

		Better_User::getInstance($uid)->notification()->all()->send(array(
			'type' => 'friend_request',
			'content' => '',
			'receiver' => $friendUid
			));		
	}
	
	public function onFriendWithSomebody(array $params)
	{
		//add no_notice
		if ($params['no_notice']) {return null;}
		
		$uid = (int)$params['uid'];
		$friendUid = (int)$params['friend_uid'];
		$isRequest = (bool)$params['is_request'];
		if(Better_Config::getAppConfig()->friend_withsomebody->notice->switch){
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUserInfo();		
			$content = $user->getUserLang()->global->friend_withsomebody;
			$content = str_replace("{NICKNAME}",$userInfo['nickname'],$content);
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->send($content, $friendUid);						
		}
		
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);

		Better_DAO_DmessageReceive::getInstance($uid)->updateByCond(array(
			'act_result' => '1',
			'readed' => '1'
			), array(
				'uid' => $uid,
				'from_uid' => $friendUid,
				'type' => 'friend_request'
			));		
			
		Better_DAO_DmessageReceive::getInstance($friendUid)->updateByCond(array(
			'act_result' => '1',
			'readed' => '1'
			), array(
				'uid' => $friendUid,
				'from_uid' => $uid,
				'type' => 'friend_request'
			));			
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
	}	
	
	public function onUnblockSomebody(array $params)
	{
		
	}
	
	public function onUnfriendWithSomebody(array $params)
	{
		
	}
	
	public function onRejectFriendRequest(array $params)
	{
		$uid = (int)$params['uid'];
		$requestUid = (int)$params['request_uid'];
		
		Better_DAO_DmessageReceive::getInstance($uid)->updateByCond(array(
			'act_result' => '2',
			'readed' => '1'
			), array(
				'from_uid' => $requestUid,
				'uid' => $uid,
				'type' => 'friend_request'
			));				
	}
	
	public function onInviteTodoSent($params)
	{
		$receiverUid = $params['receiver_uid'];
		$uid = $params['uid'];
		$msg_id = $params['msg_id'];		
		Better_User::getInstance($receiverUid)->notify()->store(array(
			'type' => 'invitation_todo',
			'content' => '',
			'data' => $msg_id
			));		
	}
	
}
