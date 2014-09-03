<?php

/**
 * “关注的人”预分发
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Publictimeline extends Better_Hook_Base
{
	public function onPoiPollSubmitted(array $params)
	{
		
	}
		
	public function onPoiCreated(array $params)
	{
		
	}
	
	/**
	 * 用户发布吼吼时，需要马上为他自己插入一条数据，其他人的则留到后端队列取慢慢处理
	 * 
	 * @see Better_Hook_Base::onBlogPosted()
	 */
	public function onBlogPosted(array $params)
	{
		if ($params['data']['no_publictimeline']) {
			return;
		}
		
		$uid = $params['uid'];
		$bid = $params['bid'];
		$blog = &$params['blog'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		Better_DAO_User_Publictimeline::getInstance($uid)->insert(array(
			'uid' => $uid,
			'bid' => $bid,
			'dateline' => time()
			));
	}
	
	/**
	 * 用户删除吼吼时，立刻从预分发表中删除数据
	 * 
	 * @see Better_Hook_Base::onBlogDeleted()
	 */
	public function onBlogDeleted(array $params)
	{
		$uid = $params['userInfo']['uid'];
		$bid = $params['blog']['bid'];
		
		Better_DAO_User_Publictimeline::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'bid' => $bid
			));
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