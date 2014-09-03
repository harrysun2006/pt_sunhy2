<?php

/**
 * 清理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Clean extends Better_Hook_Base
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
		/*$uid = (int)$params['uid'];
		$priv = $params['priv'];
		$bid = Better_Registry::get('checkin_bid');
		
		$gotBadge = trim(Better_Hook::$hookNotify['UserCheckin']['badge'])!='' ? true : false;
		$gotMajor = Better_Hook::$hookResults['UserCheckin']['major_changed'] ? true : false;
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		define('BETTER_PASSBY_SYNCSITE', true);
		$checkinParams = Better_Registry::get('blog_last_params');
		
		if ($gotMajor) {
			
			Better_DAO_Blog::getInstance($uid)->updateByCond(array(
				'major' => 0,
				'badge_id' => 0
				), array(
				'bid' => $bid,
				'uid' => $uid
				));
			
			$s = array(
				'bid' => $uid.'.'.$userInfo['posts'],
				'type' => 'checkin',
				'poi_id' => $checkinParams['poi_id'],
				'message' => '',
				'attach' => '',
				'priv' => $checkinParams['priv'] ,
				'source' => $checkinParams['source'],
				'x' => $checkinParams['x'],
				'y' => $checkinParams['y'],
				'city' => $checkinParams['city'],
				'address' => $checkinParams['address'],
				'range' => $checkinParams['range'],
				'ip' => $checkinParams['ip'],
				'dateline' => time(),
				'synced' => 1,
				'uid' => $uid
				);
				
			try {
				$nbid = Better_DAO_Blog::getInstance($uid)->insert($s);
				
				if ($nbid) {
					Better_DAO_Blog::getInstance($uid)->updateByCond(array(
						'major' => 1
					), array(
						'uid' => $uid,
						'bid' => $nbid
						));
						
					Better_Hook::factory(
						array(
							'User',
						)
					)->invoke('BlogPosted', array(
						'blog' => $s,
						'data' => $s,
						'bid' => $nbid,				
						'uid' => $uid,
						));
				}
	
			} catch (Exception $e) {
				Better_Log::getInstance()->logAlert('POST_NEW_BLOG_FAILED:['.$e->getMessage().']', 'blog');
				
				$user->updateUser(array(
					'posts' => $userInfo['posts']+1
					));
				
				$nbid && self::delete($nbid);
				$nbid = 0;
			}				
		} else if ($gotBadge) {
			$badges = Better_Hook::$hookResults['UserCheckin']['badge'];
			$thisBadge = array_pop($badges);

			if ($thisBadge['badge_name']) {
				Better_DAO_Blog::getInstance($uid)->updateByCond(array(
					'major' => 0,
					'badge_id' => 0
					), array(
					'bid' => $bid,
					'uid' => $uid
					));
				
				$s = array(
					'bid' => $uid.'.'.$userInfo['posts'],
					'type' => 'checkin',
					'poi_id' => $checkinParams['poi_id'],
					'message' => '',
					'attach' => '',
					'priv' => $checkinParams['priv'] ,
					'source' => $checkinParams['source'],
					'x' => $checkinParams['x'],
					'y' => $checkinParams['y'],
					'city' => $checkinParams['city'],
					'address' => $checkinParams['address'],
					'range' => $checkinParams['range'],
					'ip' => $checkinParams['ip'],
					'dateline' => time(),
					'synced' => 1,
					'uid' => $uid,
					);
					
				try {
					$nbid = Better_DAO_Blog::getInstance($uid)->insert($s);
					
					if ($nbid) {
						$badgeId = $thisBadge['id'];
						Better_DAO_Blog::getInstance($uid)->updateByCond(array(
							'badge_id' => $badgeId
						), array(
							'uid' => $uid,
							'bid' => $nbid
							));
												
						Better_Hook::factory(
							array(
								'User',
							)
						)->invoke('BlogPosted', array(
							'blog' => $s,
							'data' => $s,
							'bid' => $nbid,				
							'uid' => $uid,
							));
					} else {
						Better_Log::getInstance()->logInfo("FAILED:[".$nbid."]", 'clean');
					}
		
				} catch (Exception $e) {
					Better_Log::getInstance()->logAlert('POST_NEW_BLOG_FAILED:['.$e->getMessage().']', 'blog');
					
					$user->updateUser(array(
						'posts' => $userInfo['posts']+1
						));
					
					$nbid && self::delete($nbid);
					$nbid = 0;
				}						
			}
		}*/
	}
	
	public function onFriendRequest(array $params)
	{
		
	}
	
	public function onFriendWithSomebody(array $params)
	{
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		
		Better_DAO_FriendsRequestToMe::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'request_uid' => $friendUid,
			));
		Better_DAO_FriendsRequestToMe::getInstance($friendUid)->deleteByCond(array(
			'uid' => $friendUid,
			'request_uid' => $uid,
			));
										
		Better_DAO_FriendsRequest::getInstance($friendUid)->deleteByCond(array(
			'uid' => $friendUid,
			'request_to_uid' => $uid,
			));
		Better_DAO_FriendsRequest::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'request_to_uid' => $friendUid,
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
		
	}
}