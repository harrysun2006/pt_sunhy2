<?php

/**
 * 虚拟勋章处理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Fakebadge extends Better_Hook_Base
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
		$uid = $params['uid'];
		$poiId = $params['poi_id'];
		$score = (float)$params['score'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		
		$badges = array();
		$lastBadgeId = 0;
		$lastBadgeName = '';

		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();

		if ($user->isActive() && $score<=0) {
			$gotBadges = $user->badge()->getMyBadges();
			$gotBids = array_keys($gotBadges);
			
			$gender = $userInfo['gender'];
			$gs = array('all');
			$gender!='secret' && $gs[] = $gender;
	
			$availableBadges = Better_Badge::getAllBadges(array(
				'category' => 'checkin',
				'gender' => $gs,
				'dirty' => 1
				), $gotBids);

			foreach ($availableBadges as $badge) {
				try {
					$flag = $badge->touch(array(
						'poi_id' => $poiId,
						'uid' => $uid,
						'gender' => $gender,
						'x' => $x,
						'y' => $y
						));
					if ($flag===true) {
						$data = $badge->getParams();
						$badges[$data['id']] = $data;
						$user->badge()->got($badge->id, $poiId);
						$lastBadgeId = $badge->id;
						$lastBadgeName = $badge->badge_name;
					}
				} catch (Exception $e) {
					Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge');
					continue;
				}						
			}
		}
		
		if ($lastBadgeId && Better_Hook::$hookResults['UserCheckin']['bid']) {
			Better_DAO_Blog::getInstance($uid)->updateByCond(array(
				'badge_id' => $lastBadgeId
			), array(
				'bid' => Better_Hook::$hookResults['UserCheckin']['bid'],
				'uid' => $uid
			));			
			
			Better_Hook::$hookNotify['UserCheckin']['badge'] = Better_Language::load()->global->got_badge.'"'.$lastBadgeName.'"';
		}
		Better_Hook::$hookResults['UserCheckin']['badge'] = $badges;
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