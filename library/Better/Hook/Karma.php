<?php

/**
 * Karma计算
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Karma extends Better_Hook_Base
{
	public function onPoiPollSubmitted(array $params)
	{
		
	}
		
	public function onPoiCreated(array $params)
	{
	}
		
	public function onBlogPosted(array $params)
	{
		if (!defined('BETTER_PASSBY_KARMA')) {
			$blog = &$params['blog'];
			$uid = $params['uid'];
			$poiId = $blog['poi_id'];
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUserInfo();
						
			if ($user->isActive() && $poiId>0) {
				$message = '';
				
				if ($blog['type']=='normal' && $blog['upbid']=='0') {
					$karma = $user->karma()->calculate('NewBlog');
					if ($karma!=0 && $user->karma()->update(array(
							'karma' => $karma,
							'category' => 'new_blog',
							))) {
						$message = str_replace('{KARMA}', $karma, $lang->karma->blog->title);
						Better_Hook::$hookNotify['BlogPosted']['karma'] = $message;
					}	
				} else if ($blog['type']=='tips') {
					$karma = $user->karma()->calculate('NewTips');
					if ($karma!=0 && $user->karma()->update(array(
							'karma' => $karma,
							'category' => 'new_tips',
							))) {
						$message = str_replace('{KARMA}', $karma, $lang->karma->tips->title);
						Better_Hook::$hookNotify['BlogPosted']['karma'] = $message;
					}
				}
				
				$message !='' && Better_Hook::$hookMessages['BlogPosted']['karma'] = $message;
			}
		}
	}
	
	public function onBlogDeleted(array $params)
	{
		if (!defined('BETTER_PASSBY_KARMA')) {
			$blog = &$params['blog'];
			$userInfo = &$params['userInfo'];
			
			$user = Better_User::getInstance($userInfo['uid']);
			
			$karma = $user->karma()->calculate('Delete');
			if ($karma!=0) {
				$user->karma()->update(array(
					'karma' => $karma,
					'category' => 'delete',
					'co_uid' => 0,
					));
			}
		}
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
		$userInfo = $user->getUserInfo();
		
		$followingUser = Better_User::getInstance($followingUid);
		$followingUserInfo = $followingUser->getUserInfo();
		
		$karma = $followingUser->karma()->calculate('NewFollower');
		if ($karma!=0) {
			
			$followingUser->karma()->update(array(
				'karma' => $karma,
				'category' => 'new_follower',
				'co_uid' => $uid,
				));
		}*/
		
		
	}
	
	public function onBlockedSomebody(array $params)
	{
		$uid = $params['uid'];
		$blockedUid = $params['blocked_uid'];
		$from = $params['from'];
		$msgid = $params['msgid'];
		$blockedUser = Better_User::getInstance($blockedUid);
		$blockedUserInfo = $blockedUser->getUserInfo();

		if ($from==='friend_request' || $from==='direct_message' || $from==='follow_request') {
			//	加好友请求里面阻止了好友
			$over24hours = 1;
			switch ($from){
				case 'friend_request':
					$request_info = Better_User_Notification::getInstance($uid)->getFriendsrequstInfo($blockedUid);
					if((strtotime(date("Y-m-d H:i:s",time()))-$request_info['dateline'])>24*3600){
						$over24hours = 0;
					}
					break;
				case 'direct_message':					
					$direct_info = Better_User_Notification::getInstance($uid)->getDirectmesssageInfo($msgid);
					if((strtotime(date("Y-m-d H:i:s",time()))-$direct_info['dateline'])>24*3600){
						$over24hours = 0;
					}
					break;
				case 'follow_request':
					/*$request_info = Better_User_Notification::getInstance($uid)->getFollowrequstInfo($blockedUid);
					if((strtotime(date("Y-m-d H:i:s",time()))-$request_info['dateline'])>24*3600){
						$over24hours = 0;
					}*/
					break;
			}	
			
			if($over24hours){				
				$karma = $blockedUser->karma()->calculate('BlockedFromFriendRequest');
	
				if ($karma!=0) {
					define('BETTER_KARMA_BLOCKED_PLUSED', true);
					$blockedUser->karma()->update(array(
						'karma' => $karma,
						'category' => 'blocked_from_friend_request',
						'co_uid' => $uid,
						));
				}
			}		
		} else {
			$karma = $blockedUser->karma()->calculate('BlockedBySomebody');
			
			if ($karma!=0) {
				$blockedUser->karma()->update(array(
					'karma' => $karma,
					'category' => 'blocked_by_somebody',
					'co_uid' => $uid,
					));
			}
		}
	}
	
	public function onUserCreated(array $params)
	{
		$userInfo = &$params['userInfo'];
		
		if (isset($userInfo['ref_uid']) && $userInfo['ref_uid']) {
			$refUser = Better_User::getInstance($userInfo['ref_uid']);
			$karma = $refUser->karma()->calculate('InviteSomebody');
			if ($karma!=0) {
				$refUser->karma()->update(array(
					'karma' => $karma,
					'category' => 'invite_somebody',
					'co_uid' => $userInfo['uid'],
					));
			}
		}
	}
	
	public function onUserChanged(array $params)
	{
		$oldUserInfo = &$params['oldUserInfo'];
		$newUserInfo = &$params['newUserInfo'];
	}
	
	public function onAttachmentUploaded(array $params)
	{
		$fileId = $params['file_id'];
		$uid = $params['uid'];
	}
	
	public function onFollowRequest(array $params)
	{
		/*$uid = $params['uid'];
		$followingUid = $params['following_uid'];	*/	
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
		$message = '';
		$uid = $params['uid'];

		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();		
		$karma = $user->karma()->calculate('login');
		if ($karma!=0 && $user->karma()->update(array(
				'karma' => $karma,
				'category' => 'login',
				))) {

			$lang = Better_Registry::get('lang');
			$message = str_replace('{KARMA}', Better_Karma::format($karma), $lang->karma->login->title);
		}
			
		$message!='' && Better_Hook::$hookMessages['UserLogin']['karma'] = $message;
	}
	
	public function onUserLogout(array $params)
	{
		$uid = $params['uid'];
	}	
	
	public function onUnfollowSomebody(array $params)
	{
		
		/*$uid = $params['uid'];
		$followingUid = $params['following_uid'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		$karma = $user->karma()->calculate('ReduceFollowing');
		if ($karma!=0) {
			$user->updateUser(array(
				'karma' => $userInfo['karma'] + $karma
				));
			$user->karma()->log(array(
				'karma' => $karma,
				'category' => 'reduce_following',
				'co_uid' => $followingUid
				));
		}
		
		$followingUser = Better_User::getInstance($followingUid);
		$followingUserInfo = $followingUser->getUserInfo();
		
		$karma = $followingUser->karma()->calculate('ReduceFollower');
		if ($karma!=0) {
			
			$followingUser->updateUser(array(
				'karma' => $followingUserInfo['karma'] + $karma,
				));

			$followingUser->karma()->log(array(
				'karma' => $karma,
				'category' => 'reduce_follower',
				'co_uid' => $uid,
				));
		}	*/	
	}
	
	public function onUserCheckin(array $params)
	{
		$checkinId = $params['checkin_id'];
		$uid = $params['uid'];
		$poiId = $params['poi_id'];
		$checkins = $params['checkins'];
		$checkinTime = $params['check_time'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		if ($user->isActive()) {
			$karma = $user->karma()->calculate('Checkin');
			
			if ($karma!=0 && $user->karma()->update(array(
					'karma' => $karma,
					'category' => 'checkin',
					))) {			
						
				$lang = Better_Registry::get('lang');
				Better_Hook::$hookResults['UserCheckin']['karma'] = str_replace('{KARMA}', Better_Karma::format($karma), $lang->karma->checkin->title);
				Better_Hook::$hookNotify['UserCheckin']['karma'] = str_replace('{KARMA}', Better_Karma::format($karma), $lang->karma->checkin->title);
			}
		}
	}
	
	public function onFriendRequest(array $params)
	{
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		//	加好友请求先扣Karma值
		$karma = $user->karma()->calculate('FriendRequest');
		
		if ($karma!=0) {
			$user->karma()->update(array(
				'karma' => $karma,
				'category' => 'friend_request',
				'co_uid' => $friendUid,
				));
		}
	}
	
	public function onFriendWithSomebody(array $params)
	{
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];		
		
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		$friendUser = Better_User::getInstance($friendUid);
		$friendUserInfo = $friendUser->getUserInfo();
		
		$uKarma = $user->karma()->calculate('FriendWithSomebody', array(
			'friend_uid' => $friendUid
			));
		$fKarma = $friendUser->karma()->calculate('FriendWithSomebody', array(
			'friend_uid' => $uid,
			));

		if ($uKarma!=0) {
			$user->karma()->update(array(
				'karma' => $uKarma,
				'category' => 'friend_with_somebody',
				'co_uid' => $friendUid,
				));
		}
		
		if ($fKarma!=0) {
			$friendUser->karma()->update(array(
				'karma' => $fKarma,
				'category' => 'friend_with_somebody',
				'co_uid' => $uid,
				));
		}
		
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
	}
	
	public function onUnblockSomebody(array $params)
	{
		$uid = $params['uid'];
		$unBlockedUid = $params['unblocked_uid'];
	}
	
	public function onUnfriendWithSomebody(array $params)
	{
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		$friendUser = Better_User::getInstance($friendUid);
		$friendUserInfo = $friendUser->getUserInfo();
		
		$karma = $user->karma()->calculate('UnfriendWithSomebody');
		if ($karma!=0) {
			$user->karma()->update(array(
				'karma' => $karma,
				'category' => 'cancel_friend',
				'co_uid' => $friendUid,
				));
		}

		$fKarma = $friendUser->karma()->calculate('UnfriendWithSomebody');
		
		if ($fKarma!=0) {
			$friendUser->karma()->update(array(
				'karma' => $fKarma,
				'category' => 'cancel_friend',
				'co_uid' => $uid,
				));
		}
	}
	
	public function onRejectFriendRequest(array $params)
	{
		if (!defined('BETTER_KARMA_BLOCKED_PLUSED')) {
			$uid = $params['uid'];
			$requestUid = $params['request_uid'];
			$requestUser = Better_User::getInstance($requestUid);
			$requestUserInfo = $requestUser->getUserInfo();
	
			$karma = $requestUser->karma()->calculate('FriendRequestRefused');
			
			if ($karma!=0) {
				$requestUser->karma()->update(array(
					'karma' => $karma,
					'category' => 'friend_request_refused',
					'co_uid' => $uid,
					));
			}
		}
	}
}