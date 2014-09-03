<?php

/**
 * 互联网大会处理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Meeting extends Better_Hook_Base
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
	}
	
	public function onBlockedSomebody(array $params)
	{
	}
	
	public function onUserCreated(array $params)
	{
		if (defined('IN_MEETING')) {
			$uid = (int)$params['userInfo'];
			$meetingUid = (int)Better_Config::getAppConfig()->user->meeting_user_id;
			
			Better_User::getInstance($uid)->friends()->request($meetingUid);
			Better_User::getInstance($meetingUid)->friends()->request($uid);
		}
	}
	
	public function onUserChanged(array $params)
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
}