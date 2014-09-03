<?php

/**
 * 市场部活动
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Market extends Better_Hook_Base
{
	
	/**
	 * 用户帐号激活
	 * 
	 */
	public function onEmailBinded(array $params)
	{
		
	}

	/**
	 * Karma值变化时
	 * 
	 */
	public function onKarmaChange(array $params)
	{
	
	}

	/**
	 * 兑换宝物
	 * 
	 * @param array $params
	 */
	public function onExchangeTreasure(array $params)
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
		$oldUserInfo = &$params['oldUserInfo'];
		$newUserInfo = &$params['newUserInfo'];
		
		if (isset($newUserInfo['avatar']) && $newUserInfo['avatar'] && $newUserInfo['avatar']==$oldUserInfo['avatar']) {
			Better_DAO_Market_Blog::getInstance($oldUserInfo['uid'])->updateByCond(array(
				'kai_checked' => 0,
				'partner_checked' => 0,
				'last_checked' => 0,
			), array(
				'uid' => $oldUserInfo['uid']
			));
		}
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
		$score = $params['score'];
		$message = $params['message'];
		$priv = $params['priv'];
		$uid = $params['uid'];
		$user = Better_User::getInstance($uid);
		$config = Better_Config::getAppConfig();
		
		if (trim($message) && $score<=0) {
			$bid = Better_Hook::$hookResults['UserCheckin']['bid'];
			
			//	该死的大声展勋章，算了...
			$gotBadges = $user->badge()->getMyBadges();
			$gotBids = array_keys($gotBadges);		
			$thisBadge = 93;
	
			if ($poiId && !in_array($thisBadge, $gotBids) && $priv=='public' && $config->getitloudermeeting->switch) {
				$now = time();
				$overtime = $config->poi->getitlouder->bj->overtime;
				if ($now<=$overtime && $poiId==$config->poi->getitlouder->bj->id) {
					$user->badge()->got($thisBadge, $poiId);
					
					Better_DAO_Blog::getInstance($uid)->updateByCond(array(
						'badge_id' => $thisBadge
					), array(
						'bid' => $bid,
						'uid' => $uid
					));				
				}
			}		
			
			$thisBadge = 94;
			if ($poiId && !in_array($thisBadge, $gotBids) && $priv=='public' && $config->getitloudermeeting->switch) {
				$now = time();
				$overtime = $config->poi->getitlouder->sh->overtime;
				$starttime = $config->poi->getitlouder->bj->overtime;
				if ($now>$starttime && $now<=$overtime && $poiId==$config->poi->getitlouder->sh->id) {
					$user->badge()->got($thisBadge, $poiId);
					
					Better_DAO_Blog::getInstance($uid)->updateByCond(array(
						'badge_id' => $thisBadge
					), array(
						'bid' => $bid,
						'uid' => $uid
					));					
				}
			}				
		}
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