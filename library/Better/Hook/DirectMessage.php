<?php

/**
 * 发送私信
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_DirectMessage extends Better_Hook_Base
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
		$blog = &$params['blog'];
		$userInfo = &$params['user'];
		$uid = Better_Registry::get('sess')->getUid();
		
		if ($uid!=$userInfo['uid'] && Better_Registry::get('sess')->admin_uid) {
			
		}
		
	}
	
	public function onFollowSomebody(array $params)
	{
		/*$uid = $params['uid'];
		$following_uid = $params['uid'];*/
		
	}
	
	public function onBlockedSomebody(array $params)
	{
		$uid = $params['uid'];
		$blocked_uid = $params['blocked_uid'];
		
		Better_DAO_DmessageReceive::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'from_uid' => $blocked_uid,
			));
		Better_DAO_DmessageSend::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'to_uid' => $blocked_uid,
			));
	}
	
	public function onBeforeQbsQuery(array $params)
	{
	}
	
	public function onAfterQbsQuery(array $params)
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
		$uid = $params['uid'];
		$toFollowUid = $params['to_follow'];
		$duplicated = $params['duplicated'];

		if (!$duplicated) {
			$user = Better_User::getInstance($uid);
			$user->notification()->FollowRequest()->send(array(
				'receiver' => $toFollowUid,
				'content' => '',
				));
		}
		
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
		$uid = $params['uid'];
		
		$tmp = Better_User_DirectMessage::getInstance($uid)->getReceiveds(1,9999);
		foreach($tmp['msgs'] as $row) {
			$sender = $row['from_uid'];
			$senderUser = Better_User::getInstance($sender);
			$senderUserInfo = $senderUser->getUser();
			$senderUser->updateUser(array(
				'sent_msgs' => $senderUserInfo['sent_msgs']-1,
				));
			
			Better_DAO_DmessageSend::getInstance($sender)->deleteByCond(array(
				'to_uid' => $uid,
				));
		}
		
		$tmp = Better_User_DirectMessage::getInstance($uid)->getSents(1,9999);
		foreach($tmp['msgs'] as $row) {
			$receiver = $row['to_uid'];
			$receiverUser = Better_User::getInstance($receiver);
			$receiverUserInfo = $receiverUser->getUser();
			
			$data = array(
				'received_msg' => $receiverUserInfo['received_msg']-1,
				);
			if (!$row['received']) {
				$data['new_msgs'] = $receiverUserInfo['new_msgs']-1;
			}
			$receiverUser->updateUser($data);
			
			Better_DAO_DmessageReceive::getInstance($receiver)->deleteByCond(array(
				'from_uid' => $uid,
				));
		}
		
		Better_DAO_DmessageReceive::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			));
		Better_DAO_DmessageSend::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			));
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
	
	public function onKarmaChange(array $params)
	{
		$uid = $params['uid'];
		$old_karma = $params['orig'];
		$new_karma = $params['new'];
		
		if($old_karma < 900 && $new_karma >= 900){
			Better_User::getInstance(BETTER_SYS_UID)->notification()->directMessage()->send(array(
				'receiver' => $uid,
				'content' => Better_Registry::get('language')->dmessage->karma900_msg
			));
		}
	}
}