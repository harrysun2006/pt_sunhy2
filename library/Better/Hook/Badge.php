<?php

/**
 * 勋章处理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Badge extends Better_Hook_Base
{
	
	/**
	 * 用户帐号激活
	 * 
	 */
	public function onEmailBinded(array $params)
	{
		$uid = $params['uid'];
		$beforeState = $params['before_state'];
		
		if ($uid) {
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUserInfo();
			
			if ($userInfo['ref_uid'] && $beforeState==Better_User_State::SIGNUP_VALIDATING) {
				$refUid = $userInfo['ref_uid'];
				$refUser = Better_User::getInstance($refUid);
				$refUserInfo = $refUser->getUserInfo();
				
				$gotBadges = $refUser->badge()->getMyBadges();
				$gotBids = array_keys($gotBadges);
				
				$availableBadges = Better_Badge::getAllBadges(array(
					'category' => 'invite',
					), $gotBids);
					
				foreach ($availableBadges as $badge) {
					try {
						$flag = $badge->touch(array(
							'uid' => $refUid,
							));
						
						if ($flag===true) {
							$data = $badge->getParams();
							$refUser->badge()->got($badge->id, $refUserInfo['last_checkin_poi']);
						}
			
					} catch (Exception $e) {
						Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge');
						continue;
					}					
				}
			}
		}
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
		$treasureId = $params['treasure_id'];
		$uid = $params['uid'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		
		$gotBadges = $user->badge()->getMyBadges();
		$gotBids = array_keys($gotBadges);
		
		//	这个勋章（幸运儿：只要触发了兑宝操作即可）没有任何逻辑，只能hardcode了...
		$hardId = Better_Config::getAppConfig()->treasure->hard_id;

		if (!in_array($hardId, $gotBids)) {
			$badge = Better_Badge::getBadge($hardId);
			$user->badge()->got($hardId, $userInfo['last_checkin_poi']);
			Better_Hook::$hookResults['ExchangeTreasure']['badge'] = (array)$badge;
			Better_Hook::$hookNotify['ExchangeTreasure']['badge'] = Better_Language::load()->global->got_badge.' '.$badge->badge_name;
		}			
		
	}
	
	/**
	 * 捡起宝物
	 * 
	 * 
	 */
	public function onPickupTreasure(array $params)
	{
		$treasureId = $params['treasure_id'];
		$poiId = $params['poi_id'];
		$uid = $params['uid'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		
		$gotBadges = $user->badge()->getMyBadges();
		$gotBids = array_keys($gotBadges);
		
		$availableBadges = Better_Badge::getAllBadges(array(
			'category' => 'treasure',
			), $gotBids);

		$badges = array();
		$lastBadgeId = 0;
		
		foreach ($availableBadges as $badge) {
			try {
				$flag = $badge->touch(array(
					'uid' => $uid,
					));
				
				if ($flag===true) {
					$data = $badge->getParams();
					$badges[$data['id']] = $data;
					$user->badge()->got($badge->id, $poiId);
					$lastBadgeId = $badge->id;
				}
			} catch (Exception $e) {
				Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge');
				continue;
			}				
		}

		Better_Hook::$hookResults['PickupTreasure']['badge'] = $badges;

		if ($lastBadgeId) {
			Better_Hook::$hookNotify['PickupTreasure']['badge'] = Better_Language::load()->global->got_badge.' '.$badge->badge_name;
		} 	
		
	}
	
	/**
	 * 新的同步站点
	 * 基本上只为了“新闻联播”那个勋章
	 * 
	 * @param $params
	 */
	public function onNewSyncSites(array $params)
	{
		$uid = $params['uid'];
		$protocol = $params['protocol'];	
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		
		$gotBadges = $user->badge()->getMyBadges();
		$gotBids = array_keys($gotBadges);
		
		$availableBadges = Better_Badge::getAllBadges(array(
			'category' => 'sync',
			), $gotBids);

		$badges = array();
		$lastBadgeId = 0;

		foreach ($availableBadges as $badge) {
			try {
				$flag = $badge->touch(array(
					'uid' => $uid,
					));
				
				if ($flag===true) {
					$data = $badge->getParams();
					$badges[$data['poi_id']] = $data;
					$user->badge()->got($badge->id);
					$lastBadgeId = $badge->id;
				}
			} catch (Exception $e) {
				Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge');
				continue;
			}									
		}

		Better_Hook::$hookResults['NewSyncSites']['badge'] = $badges;

		if ($lastBadgeId) {
			Better_Hook::$hookNotify['NewSyncSites']['badge'] = Better_Language::load()->global->got_badge.' '.$badge->badge_name;
		} 	
		
	}
	
	public function onPoiPollSubmitted(array $params)
	{
		
	}
		
	/**
	 * 用户新建poi时
	 * @see Better_Hook_Base::onPoiCreated()
	 */
	public function onPoiCreated(array $params)
	{
		$uid = $params['uid'];
		$poiId = $params['poi_id'];
		$poiInfo = &$params['poi_info'];
		$doing = $params['doing'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		if ($poiId>0) {
			
			$gotBadges = $user->badge()->getMyBadges();
			$gotBids = array_keys($gotBadges);
			
			$gender = $userInfo['gender'];
			$gs = array('all');
			$gender!='secret' && $gs[] = $gender;
	
			$availableBadges = Better_Badge::getAllBadges(array(
				'category' => 'poi',
				'gender' => $gs
				), $gotBids);
	
			$badges = array();
			$lastBadgeId = 0;
			$lastBadgeName = '';
			
			foreach ($availableBadges as $badge) {
				try {
					$flag = $badge->touch(array(
						'poi_id' => $poiId,
						'uid' => $uid,
						'gender' => $gender,
						'poi_info' => $poiInfo,
						'doing' => $doing
						));
					if ($flag===true) {
						$data = $badge->getParams();
						$badges[$data['poi_id']] = $data;
						$user->badge()->got($badge->id, $poiId);
						$lastBadgeId = $badge->id;
						$lastBadgeName = $badge->badge_name;
					}
				} catch (Exception $e) {
					Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge_exception');
					continue;
				}						
			}
			
			if ($lastBadgeId) {
				Better_Hook::$hookNotify['PoiCreated']['badge'] = Better_Language::load()->global->got_badge.'"'.$lastBadgeName.'"';
			}
	
			Better_Hook::$hookResults['PoiCreated']['badge'] = $badges;
		}		
	}
	
	public function onBlogPosted(array $params)
	{
		$bid = $params['bid'];
		$uid = $params['uid'];
		$poiId = $params['blog']['poi_id'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		$abUid = Better_Config::getAppConfig()->aibang_user_id;
					
		if ($uid!=$abUid) {
			$gotBadges = $user->badge()->getMyBadges();
			$gotBids = array_keys($gotBadges);			
			$gender = $userInfo['gender'];
			$gs = array('all');
			$gender!='secret' && $gs[] = $gender;	
			$availableBadges = Better_Badge::getAllBadges(array(
				'category' => 'blog',
				'gender' => $gs
				), $gotBids);
						
			$badges = array();
			$lastBadgeId = 0;
			$lastBadgeName = '';
						
			foreach ($availableBadges as $badge) {
				try {
					$flag = $badge->touch(array(
						'poi_id' => $poiId,
						'uid' => $uid,
						'gender' => $gender,
						'blog' => &$params['blog']
						));

					if ($flag===true) {
						$data = $badge->getParams();
						$badges[$data['id']] = $data;
						$user->badge()->got($badge->id, $poiId);
						$lastBadgeId = $badge->id;
						$lastBadgeName = $badge->badge_name;
					}
				} catch (Exception $e) {
					Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge_exception');
					continue;
				}						
			}
			
			Better_Hook::$hookNotify['BlogPosted']['badge'] = Better_Language::load()->global->got_badge.'"'.$lastBadgeName.'"';
			
			/*if ($lastBadgeId) {
				Better_Hook::$hookNotify['BlogPosted']['badge'] = Better_Language::load()->global->got_badge.'"'.$lastBadgeName.'"';
				Better_DAO_Blog::getInstance($uid)->updateByCond(array(
					'badge_id' => $lastBadgeId
				), array(
					'bid' => $bid,
					'uid' => $uid
				));
			}*/			
			//Better_Hook::$hookResults['BlogPosted']['badge'] = $badges;
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
		/*$uid = $params['uid'];
		$followingUid = $params['following_uid'];
		$badges = array();
		
		$followingUser = Better_User::getInstance($followingUid);
		$followingUserInfo = $followingUser->getUser();
		
		if (!$followingUser->isBanned()) {
			$gotBadges = $followingUser->badge()->getMyBadges();
			$gotBids = array_keys($gotBadges);
			
			$gender = $followingUserInfo['gender'];
			$gs = array('all');
			$gender!='secret' && $gs[] = $gender;
			
			$availableBadges = Better_Badge::getAllBadges(array(
				'category' => 'follower',
				'gender' => $gs
				), $gotBids);
				
			foreach ($availableBadges as $badge) {
				try {
					$flag = $badge->touch(array(
						'gender' => $gender,
						'uid' => $followingUid,
						));
	
					if ($flag===true) {
						$data = $badge->getParams();
						
						$badges[$data['id']] = $data;
						$followingUser->badge()->got($badge->id);
					}
				} catch (Exception $e) {
					Better_Log::getInstance()->logInfo($e->getTraceAsString(), 'badge');
					continue;
				}
			}
		}*/		
	}
	
	public function onBlockedSomebody(array $params)
	{
	}
	
	public function onUserCreated(array $params)
	{
		$ref = (int)$params['ref_uid'];
		
		if ($ref>0) {

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
		$uid = $params['uid'];
		
		$badges = array();
		$lastBadgeId = 0;
		$lastBadgeName = '';

		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();


		$gotBadges = $user->badge()->getMyBadges();
		$gotBids = array_keys($gotBadges);
		
		$gender = $userInfo['gender'];
		$gs = array('all');
		$gender!='secret' && $gs[] = $gender;

		$availableBadges = Better_Badge::getAllBadges(array(
			'category' => 'login',
			'gender' => $gs
			), $gotBids);

		foreach ($availableBadges as $badge) {
			try {
				$flag = $badge->touch(array(
					'uid' => $uid,
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
		
		Better_Hook::$hookNotify['UserLogin']['badge'] = Better_Language::load()->global->got_badge.'"'.$lastBadgeName.'"';
		
		/*if ($lastBadgeId && Better_Hook::$hookResults['UserLogin']['bid']) {
			Better_DAO_Blog::getInstance($uid)->updateByCond(array(
				'badge_id' => $lastBadgeId
			), array(
				'bid' => Better_Hook::$hookResults['UserLogin']['bid'],
				'uid' => $uid
			));			
			
			Better_Hook::$hookNotify['UserLogin']['badge'] = Better_Language::load()->global->got_badge.'"'.$lastBadgeName.'"';
		}*/
		Better_Hook::$hookResults['UserLogin']['badge'] = $badges;				
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
		$distance = (float)$params['distance'];
		$day = $params['day']; //暂时用这个判断是不是活动了
		
		$badges = array();
		$lastBadgeId = 0;
		$lastBadgeName = '';

		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();

		if ($score>0 || $day ) {
			$gotBadges = $user->badge()->getMyBadges();
			$gotBids = array_keys($gotBadges);
			
			$gender = $userInfo['gender'];
			$gs = array('all');
			$gender!='secret' && $gs[] = $gender;
	
			$availableBadges = Better_Badge::getAllBadges(array(
				'category' => 'checkin',
				'gender' => $gs
				), $gotBids);		
			$availableBadgesblog = Better_Badge::getAllBadges(array(
				'category' => 'blog',
				'gender' => $gs
				), $gotBids);
			try{
				$newavailableBadges = $availableBadges+$availableBadgesblog; 
			}  catch (Exception $e) {
				$newavailableBadges =$availableBadges;
			}
			//Better_Log::getInstance()->logInfo(serialize($availableBadges)."\n".serialize($availableBadgesblog)."\n".serialize($newavailableBadges),'newcheckinbadege');
			$badgeshowdesc = 1;
			foreach ($newavailableBadges as $badge) {
				try {
					$flag = $badge->touch(array(
						'poi_id' => $poiId,
						'uid' => $uid,
						'gender' => $gender,
						'x' => $x,
						'y' => $y,
						'distance' => $distance
						));
					if ($flag===true) {
						$data = $badge->getParams();
						$badges[$data['id']] = $data;
						$user->badge()->got($badge->id, $poiId);
						if($badgeshowdesc){
							$lastBadgeId = $badge->id;
							$lastBadgeName = $badge->badge_name;
							$badgeshowdesc = 0;
						}
					}
				} catch (Exception $e) {
					Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge');
					continue;
				}						
			}
		}
		
		Better_Hook::$hookNotify['UserCheckin']['badge'] = Better_Language::load()->global->got_badge.'"'.$lastBadgeName.'"';
		
		/*if ($lastBadgeId && Better_Hook::$hookResults['UserCheckin']['bid']) {
			Better_DAO_Blog::getInstance($uid)->updateByCond(array(
				'badge_id' => $lastBadgeId
			), array(
				'bid' => Better_Hook::$hookResults['UserCheckin']['bid'],
				'uid' => $uid
			));			
			
			Better_Hook::$hookNotify['UserCheckin']['badge'] = Better_Language::load()->global->got_badge.'"'.$lastBadgeName.'"';
		}*/
		Better_Hook::$hookResults['UserCheckin']['badge'] = $badges;
	}
	
	public function onFriendRequest(array $params)
	{
		
	}
	
	public function onFriendWithSomebody(array $params)
	{
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
		
		$badges = array();
		$lastBadgeId = 0;
		$lastBadgeName = '';
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		if (!$user->isBanned()) {
			$gotBadges = $user->badge()->getMyBadges();
			$gotBids = array_keys($gotBadges);
			
			$gender = $userInfo['gender'];
			$gs = array('all');
			$gender!='secret' && $gs[] = $gender;
			
			$availableBadges = Better_Badge::getAllBadges(array(
				'category' => 'friend',
				'gender' => $gs
				), $gotBids);
				
			foreach ($availableBadges as $badge) {
				try {
					$flag = $badge->touch(array(
						'gender' => $gender,
						'uid' => $uid,
						));
					if ($flag===true) {
						$data = $badge->getParams();
						
						$badges[$data['id']] = $data;
						$user->badge()->got($badge->id);
						$lastBadgeId = $badge->id;
						$lastBadgeName = $badge->badge_name;
					}
				} catch (Exception $e) {
					Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge');
					continue;
				}							
			}
		}
		
		if ($lastBadgeId && Better_Hook::$hookResults['FriendWithSomebody']['bid']) {			
			Better_Hook::$hookNotify['FriendWithSomebody']['badge'] = Better_Language::load()->global->got_badge.'"'.$lastBadgeName.'"';
		}
		Better_Hook::$hookResults['FrienUserCheckindWithSomebody']['badge'] = $badges;		
		
		$friendUser = Better_User::getInstance($friendUid);
		$friendUserInfo = $friendUser->getUser();
		
		
		$gotBadges = $friendUser->badge()->getMyBadges();
		$gotBids = array_keys($gotBadges);
		
		$gender = $friendUserInfo['gender'];
		$gs = array('all');
		$gender!='secret' && $gs[] = $gender;
		
		$availableBadges = Better_Badge::getAllBadges(array(
			'category' => 'friend',
			'gender' => $gs
			), $gotBids);
			
		foreach ($availableBadges as $badge) {
			try {
				$flag = $badge->touch(array(
					'gender' => $gender,
					'uid' => $friendUid,
					));
				if ($flag===true) {
					$data = $badge->getParams();
					
					$badges[$data['id']] = $data;
					$friendUser->badge()->got($badge->id);
					$lastBadgeId = $badge->id;
					$lastBadgeName = $badge->badge_name;
				}
			} catch (Exception $e) {
				Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge');
				continue;
			}							
		}
				
		
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
		
	}
	public function onRpChange(array $params)
	{
		
		$uid = $params['uid'];
		$orig = $params['orig'];
		$new = $params['new'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		$gotBadges = $user->badge()->getMyBadges();
		$gotBids = array_keys($gotBadges);
		
		$availableBadges = Better_Badge::getAllBadges(array(
			'category' => 'rp',
			), $gotBids);

		$badges = array();
		$lastBadgeId = 0;		
		foreach ($availableBadges as $badge) {
			try {
				$flag = $badge->touch(array(
					'uid' => $uid,
					));
				
				if ($flag===true) {
					$data = $badge->getParams();
					$user->badge()->got($badge->id, $userInfo['last_checkin_poi']);
					$lastBadgeId = $badge->id;
				}
	
				Better_Hook::$hookResults['RpChange']['badge'] = $badges;
			} catch (Exception $e) {
				Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge');
				continue;
			}
		}	
	}
	
	public function onPoiUpdated(array $params)
	{
		$uid = $params['uid'];
		$poiId = $params['poi_id'];
		$poiInfo = &$params['poi_info'];
		$doing = $params['doing'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		if ($poiId>0) {
			
			$gotBadges = $user->badge()->getMyBadges();
			$gotBids = array_keys($gotBadges);
			
			$gender = $userInfo['gender'];
			$gs = array('all');
			$gender!='secret' && $gs[] = $gender;
	
			$availableBadges = Better_Badge::getAllBadges(array(
				'category' => 'poi',
				'gender' => $gs
				), $gotBids);
	
			$badges = array();
			$lastBadgeId = 0;
			$lastBadgeName = '';			
			foreach ($availableBadges as $badge) {
				try {
					$flag = $badge->touch(array(
						'poi_id' => $poiId,
						'uid' => $uid,
						'gender' => $gender,
						'poi_info' => $poiInfo,
						'doing' => $doing
						));
					if ($flag===true) {
						$data = $badge->getParams();
						$badges[$data['poi_id']] = $data;
						$user->badge()->got($badge->id, $poiId);
						$lastBadgeId = $badge->id;
						$lastBadgeName = $badge->badge_name;
					}
				} catch (Exception $e) {
					Better_Log::getInstance()->logAlert($e->getTraceAsString(), 'badge_exception');
					continue;
				}						
			}
			
			if ($lastBadgeId) {
				Better_Hook::$hookNotify['PoiCreated']['badge'] = Better_Language::load()->global->got_badge.'"'.$lastBadgeName.'"';
			}
	
			Better_Hook::$hookResults['PoiCreated']['badge'] = $badges;
		}		
	}
}