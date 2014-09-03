<?php

/**
 * PPNS处理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Ppns extends Better_Hook_Base
{
	/*==========	寻宝特有Hook开始	===========*/
	
	public function onGameInvite(array $params)
	{
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED==true) {
			$starterUid = (int)$params['starter_uid'];
			$coplayerUid = (int)$params['coplayer_uid'];
			$content = $params['content'];
			$sessId = $params['session_id'];
			
			Better_Ppns::getInstance()->simplePushToUid($coplayerUid, true);
		}
	}
	
	public function onGameResponse(array $params)
	{
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED==true) {
			$sessId = $params['session_id'];
			$starterUid = (int)$params['starter_uid'];
			$coplayerUid  = (int)$params['coplayer_uid'];
			$response = $params['response'];
			$sessUid = $params['uid'];
			
			Better_Ppns::getInstance()->simplePushToUid($starterUid, true);
		}
	}
	
	public function onGameChat(array $params)
	{
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED==true) {
			$sender = (int)$params['sender'];
			$receiver = (int)$params['receiver'];
			$content = $params['content'];
			$sessId = $prams['session_id'];
			
			Better_Ppns::getInstance()->simplePushToUid($receiver, true);			
		}
	}
	
	public function onGameResult(array $params)
	{
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED==true) {
			$starterUid = (int)$params['starter_uid'];
			$coplayerUid = (int)$params['coplayer_uid'];
			$sessId = $params['session_id'];
			$starterTreasure = &$params['starter_treasure'];
			$coplayerTreasure = &$params['coplayer_treasure'];
			
			Better_Ppns::getInstance()->simplePushToUid($starterUid, true);
			Better_Ppns::getInstance()->simplePushToUid($coplayerUid, true);
		}
	}
	
	public function onGameInviteTimeout(array $params)
	{
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED==true) {
			$starterUid = (int)$params['starter_uid'];
			$coplayerUid = (int)$params['coplayer_uid'];
			$sessId = $params['session_id'];
			
			Better_Ppns::getInstance()->simplePushToUid($starterUid, true);
		}
	}
	
	public function onGameTreasureTimeout(array $params)
	{
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED==true) {
			$starterUid = (int)$params['starter_uid'];
			$coplayerUid = (int)$params['coplayer_uid'];
			$sess = &$params['sess'];
			
			if (!$sess['starter_pickup']) {
				Better_Ppns::getInstance()->simplePushToUid($starterUid, true);
			}
			
			if (!$sess['coplayer_pickup']) {
				Better_Ppns::getInstance()->simplePushToUid($coplayerUid, true);
			}
		}
	}
	
	/*==========	寻宝特有Hook结束	===========*/
	
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
				
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED==true) {
			Better_Ppns::getInstance()->simplePushToUid($toFollowUid);
		}				
	}
	
	public function onDirectMessageSent(array $params)
	{
		$receiverUid = $params['receiver_uid'];
		
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED==true) {
			Better_Ppns::getInstance()->simplePushToUid($receiverUid);
		}				
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
		$friendUid = $params['friend_uid'];
		
		if (defined('BETTER_PPNS_ENABLED') && BETTER_PPNS_ENABLED==true) {
			Better_Ppns::getInstance()->simplePushToUid($friendUid);
		}				
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
	
	public function onInviteTodoSent($params)
	{
		self::onDirectMessageSent($params);
	}
}