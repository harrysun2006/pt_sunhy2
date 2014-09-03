<?php

/**
 * 缓存处理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Cache extends Better_Hook_Base
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
		$message = trim($params['blog']['message']);
		$uid = $params['uid'];
		$bid = $params['bid'];
		$badge_tm = $params['badge_tm'];
		if ($uid) {
			$user = Better_User::getInstance($uid);
			if($badge_tm){
				$cache = array(
					'message' => $message,
					'dateline' => time(),
					'attmd5' => $params['data']['attmd5'], // 记录thumb图片md5值,可以连续2次提交相同的文字 + 不同的图片
					'real_upbid' => $params['data']['real_upbid'],
					);
				$user->cache()->set('last_message', $cache);
			}
			if ($params['blog']['upbid']) {
				list($upUid, $foobar) = explode('.', $params['blog']['upbid']);
				$cacher = Better_User::getInstance($upUid)->cache();
				$rns = (int)$cacher->get('rt_blogs_count');
				$cacher->set('rt_blogs_count', $rns+1);
				
				Better_Log::getInstance()->logInfo($upUid.'|'.$rns, 'debug', true);
			}
		}		
	}
	
	public function onBlogDeleted(array $params)
	{
		$blog = &$params['blog'];
		$bid = $blog['bid'];
		
		Better_Cache_Module_Blog::delete($bid);

		$cacher = Better_Cache::remote();
		$cacheKey = md5('kai_blog_bid_'.$bid);
		$cacher->set($cacheKey);
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
		
		$user = Better_User::getInstance($uid);
		$followingUser = Better_User::getInstance($followingUid);
		
		$user->push('followings', $followingUid);
		$followingUser->push('followers', $uid);
		
		$user->cache()->set('followings', $user->followings);
		$followingUser->cache()->set('followers', $followingUser->followers);*/	
		
	}
	
	public function onBlockedSomebody(array $params)
	{
		$uid = $params['uid'];
		$blockedUid = $params['blocked_uid'];		
		
		$user = Better_User::getInstance($uid);
		$blockedUser = Better_User::getInstance($blockedUid);
		
		$user->push('blocks', $blockedUid);
		$blockedUser->push('blockedby', $uid);
		
		$user->cache()->set('blocks', $user->blocks);
		$blockedUser->cache()->set('blockedby', $blockedUser->blockedby);			
	}
	
	public function onUserCreated(array $params)
	{

	}
	
	public function onUserChanged(array $params)
	{
		$oldUserInfo = &$params['oldUserInfo'];
		$newUserInfo = &$params['newUserInfo'];
		$uid = $oldUserInfo['uid'];
		
		if (($newUserInfo['username'] && $newUserInfo['username']!=$oldUserInfo['username']) || ($newUserInfo['nickname'] && $newUserInfo['nickname']!=$oldUserInfo['nickname'])) {
			$cacher = Better_Cache::remote();
			$cacheKey = 'kai_unmap_'.md5($oldUserInfo['nickname']);
			$cacher->set($cacheKey, null);
			
			if ($newUserInfo['username'] && $newUserInfo['nickname']) {
				$cacheKey = 'kai_unmap_'.md5($newUserInfo['nickname']);
				$cacher->set($cacheKey, $newUserInfo['username']);
			} else if ($newUserInfo['username']) {
				$cacher->set($cacheKey, $newUserInfo['username']);
			} else if ($newUserInfo['nickname']) {
				$cacheKey = 'kai_unmap_'.md5($newUserInfo['nickname']);
				$cacher->set($cacheKey, $oldUserInfo['username']);
			}
			
			Better_Queue_Module_Clearcache::getInstance()->push(array(
				'uid' => $uid,
				'module' => 0
				));
		}
		
		if ($newUserInfo['avatar'] && $newUserInfo['avatar']!=$oldUser['avatar']) {
			Better_Queue_Module_Clearcache::getInstance()->push(array(
				'uid' => $uid,
				'module' => 0
				));			
		}
	}
	
	public function onAttachmentUploaded(array $params)
	{
	}
	
	public function onUserAttachDeleted($params) 
	{
		$bid = $params['bid'];
		
		if ($bid) {
			$cacher = Better_Cache::remote();
			$cacheKey = md5('kai_blog_bid_'.$bid);
			
			$cacher->remove($cacheKey);
		}
	}
	
	public function onFollowRequest(array $params)
	{
	}
	
	public function onDirectMessageSent(array $params)
	{
		$uid = (int)$params['uid'];
		$receiverUid = (int)$params['receiver_uid'];
		$type = $params['type'];
		
		$cacher = Better_User::getInstance($receiverUid)->cache();
		$cacheKey = '';
		switch ($type) {
			case 'direct_message':
				$cacheKey = 'direct_message_count';
				break;
			case 'follow_request':
				$cacheKey = 'follow_request_count';
				break;
			case 'friend_request':
				$cacheKey = 'friend_request_count';
				break;
			case 'invitation_todo':
				$cacheKey = 'invitation_todo_count';
				break;
		}
		
		if ($cacheKey) {
			$data = (int)$cacher->get($cacheKey);
			$cacher->set($cacheKey, $data+1);
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
		$uid = (int)$params['uid'];
		
	 	if (defined('IN_API') && $uid) {
	 		$data = &$_REQUEST;
	 		$user = Better_User::getInstance($uid);
	 		$user->cache()->set('apns_count', 0);
	 		
	 		if (isset($data['platform']) && isset($data['model']) && isset($data['version'])) {
				$platform = str_replace(' ', '', $data['platform']);
				$model = str_replace(' ', '', $data['model']);
				$ver = str_replace(' ', '', $data['version']);
				
	 			$user->cache()->set('client', array(
					'platform' => $platform,
					'model' => $model,
					'ver' => $ver,
					'language' => Better_Registry::get('language')
					));
	 		}
		}		
	}
	
	public function onUserLogout(array $params)
	{
	}	
	
	public function onUnfollowSomebody(array $params)
	{
		/*$uid = $params['uid'];
		$followingUid = $params['following_uid'];
		
		$user = Better_User::getInstance($uid);
		$followingUser = Better_User::getInstance($followingUid);
		
		$user->clean('followings', $followingUid);
		$followingUser->clean('followers', $uid);
		
		$user->cache()->set('followings', $user->followings);
		$followingUser->cache()->set('followers', $followingUser->followers);*/
	}
	
	public function onUserCheckin(array $params)
	{
	
	}
	
	public function onFriendRequest(array $params)
	{
	}
	
	public function onFriendWithSomebody(array $params)
	{
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];		
		
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
		
		$user = Better_User::getInstance($uid);
		$friendUser = Better_User::getInstance($friendUid);
		
		$user->push('friends', $friendUid);
		$friendUser->push('friends', $uid);

		$user->cache()->set('friends', (array)$user->friends);
		$friendUser->cache()->set('friends',  (array)$friendUser->friends);

		$user->cache()->set('friend_request_count', 0);
		
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
	}
	
	public function onUnblockSomebody(array $params)
	{
		$uid = $params['uid'];
		$unblockedUid = $params['unblocked_uid'];
		
		$user = Better_User::getInstance($uid);
		$unblockedUser = Better_User::getInstance($unblockedUid);
		
		$user->clean('blocks', $unblockedUid);
		$unblockedUser->clean('blockedby', $uid);
		
		$user->cache()->set('blocks', $user->blocks);
		$unblockedUser->cache()->set('blockedby', $unblockedUser->blockedby);					
	}
	
	public function onUnfriendWithSomebody(array $params)
	{
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		
		$user = Better_User::getInstance($uid);
		$friendUser = Better_User::getInstance($friendUid)
		;
		$user->clean('friends', $friendUid);
		$friendUser->clean('friends', $uid);
		
		$user->cache()->set('friends', (array)$user->friends);
		$friendUser->cache()->set('friends', (array)$friendUser->friends);				
	}
	
	public function onRejectFriendRequest(array $params)
	{
		
	}
	
	public function onInviteTodoSent($params)
	{
		self::onDirectMessageSent($params);
	}
	
}