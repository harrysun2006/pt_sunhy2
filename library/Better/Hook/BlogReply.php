<?php

/**
 * 发回复
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_BlogReply extends Better_Hook_Base
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
		/*$blog = &$params['blog'];
		$userInfo = &$params['user'];
		
		Better_DAO_Blogreply::getInstance($blog['uid'])->deleteByCond(array(
			'realbid' => $blog['bid'],
			));*/
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
		/*$uid = $params['uid'];
		Better_DAO_Blogreply::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			));*/
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