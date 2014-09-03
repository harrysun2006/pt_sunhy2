<?php

/**
 * Ping处理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Ping extends Better_Hook_Base
{
	
	public function onGameInvite(array $params)
	{
		$starterUid = $params['starter_uid'];
		$coplayerUid = $params['coplayer_uid'];
		$content = $params['content'];	
		
		Better_User::getInstance($starterUid)->ping()->addQueueForSomebody($coplayerUid, $content, 'game');			
	}

	/**
	 * Karma值变化时
	 * 
	 */
	public function onKarmaChange(array $params)
	{

	}

	/**
	 * 捡起宝物
	 * 
	 * 
	 */
	public function onPickupTreasure(array $params)
	{

	}
	
	/**
	 * 新的同步站点
	 * 
	 * @param $params
	 */
	public function onNewSyncSites(array $params)
	{
	
	}
	
	public function onPoiPollSubmitted(array $params)
	{
		
	}
		
	public function onPoiCreated(array $params)
	{
		
	}
	
	public function onBlogPosted(array $params)
	{
		$flag = Better_Registry::get('blog_apns_sent');
		
		if (!$flag) {
			$bid = $params['bid'];
			$uid = $params['uid'];
			$poiId = $params['blog']['poi_id'];
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUser();
	
			$message = trim(Better_Registry::get('blog_last_filter_message'));
			
			if ($params['blog']['type']=='normal' && $params['blog']['priv']=='public' && (Better_Hook::$hookResults['BlogPosted'] != Better_Hook::$RESULT_BLOG_NEED_CHECK || $message!='')) {
				
				$message=='' && $message = $params['blog']['message'];
				
				$user->ping()->addQueue(array(
					'sender' => $userInfo['nickname'],
					'content' => $userInfo['nickname'].' : '.$message,
					'type' => 'friends_shout'
					));					
					
			} else if ($params['blog']['type']=='checkin' && $params['blog']['priv']=='public') {
				$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
				$message = $params['blog']['message'] ? ' : '.$params['blog']['message'] : '';
				
				$user->ping()->addQueue(array(
					'sender' => $userInfo['nickname'],
					'content' => $userInfo['nickname'].' @ '.$poiInfo['name'].$message,
					'type' => 'friends_checkin'
					));
					
			}
			
			$flag = true;
			Better_Registry::set('blog_apns_sent', $flag);
		}
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

	}
	
	public function onUserChanged(array $params)
	{
	}
	
	public function onAttachmentUploaded(array $params)
	{
	}
	
	public function onFollowRequest(array $params)
	{
		/*
		$uid = $params['uid'];
		$followingUid = $params['following_uid'];		
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		$followingUser = Better_User::getInstance($followingUid);
		$followingUserInfo = $followingUser->getUser();
		
		$content = str_replace('{NICKNAME}', $userInfo['nickname'], $followingUser->getUserLang()->notification->follow_request);

		Better_User::getInstance($user)->ping()->addQueueForSomebody($followingUid, $content, 'request');			
                */			
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
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		$friendUser = Better_User::getInstance($friendUid);
		$friendUserInfo = $friendUser->getUser();
		
		$content = str_replace('{NICKNAME}', $userInfo['nickname'], $friendUser->getUserLang()->notification->friend_request);

		Better_User::getInstance($user)->ping()->addQueueForSomebody($friendUid, $content, 'request');									
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