<?php

/**
 * 用户
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_User extends Better_Hook_Base
{
	public function onEmailBinded(array $params)
	{
		$uid = $params['uid'];
		$beforeState = $params['before_state'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();

		$refUid = (int)$userInfo['ref_uid'];
		if ($refUid && $beforeState==Better_User_State::SIGNUP_VALIDATING) {
			$refUser = Better_User::getInstance($refUid);
			$refUserInfo = $refUser->getUser();
			$refUser->updateUser(array(
				'invites' => $refUserInfo['invites']+1
				));
		}		
	}
	
	public function onPoiPollSubmitted(array $params)
	{
		
	}
		
	public function onPoiCreated(array $params)
	{
		
	}	
	
	public function onBlogPosted(array $params)
	{
		$user = Better_User::getInstance($params['blog']['uid']);
		$userInfo = $user->getUser();
		
		$data = array(
			'posts' => $userInfo['posts']+1,
			'last_active' => time(),
		);
			
		if (($params['blog']['type']=='normal' || ($params['blog']['message']!='' && $params['blog']['type']=='checkin')) && $params['blog']['priv']=='public' && Better_Hook::$hookResults['BlogPosted'] != Better_Hook::$RESULT_BLOG_NEED_CHECK) {
			$data['last_bid'] = $params['bid'];

			//	comment ok?
			$filter_words1 = file(Better_Config::getAppConfig()->filter->words2.'-1.txt');
			$filter_words2 = file(Better_Config::getAppConfig()->filter->words2.'-2.txt');

			$filter_words = (array)array_merge($filter_words1, $filter_words2);
			$params['blog']['message'] = Better_Filter::make_semiangle($params['blog']['message']);
			foreach($filter_words as $word){
				$word = trim($word);
				if($word){
					$params['blog']['message'] = str_ireplace($word, '***', $params['blog']['message']);		
				}
			}
			
			$data['status'] = serialize(array_merge($params['blog'], array(
									'lon' => $params['data']['lon'],
									'lat' => $params['data']['lat'],
									)));
		}

		if($params['blog']['type']=='normal'){
			$data['now_posts'] = +1;
		} else if($params['blog']['type']=='tips'){
			$data['now_tips'] = +1;
		}
		$user->updateUser($data, true);
	}
	
	public function onBlogDeleted(array $params)
	{
		$userInfo = &$params['userInfo'];
		$bid = $params['blog']['bid'];
		$uid = $userInfo['uid'];
		
		//	更新用户的最后消息id
		$user = Better_User::getInstance($userInfo['uid']);
		$info = $user->getUser();
			
		$resets = array(
			'last_active' => time(),
			);
	
		if ($bid==$info['last_bid']) {
			$data = Better_Blog::getByUids(array($uid), 1, 1);
			$resets['last_bid'] = isset($data['rows'][0]['bid']) ? $data['rows'][0]['bid'] : 0;
			$resets['status'] = isset($data['rows'][0]) ? serialize($data['rows'][0]) : serialize(array());
		}
		
		switch ($params['blog']['type']) {
			case 'normal':
				$resets['now_posts'] = -1;
				break;
			case 'tips':
				$resets['now_tips'] = -1;
				break;
			case 'checkin':
				$resets['checkins'] = -1;
				break;
		}
		$user->updateUser($resets);		
		Better_User_Favorites::callbackInBlogDelete($bid);
	}

	public function onAttachmentUploaded(array $params)
	{
		$uid = $params['uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		$user->updateUser(array(
			'files' => $userInfo['files']+1
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
		/*$uid = $params['uid'];
		$followingUid = $params['following_uid'];
		
		$user = Better_User::getInstance($uid);
		$followingUser = Better_User::getInstance($followingUid);
		
		$userInfo = $user->getUserInfo();
		$followingUserInfo = $followingUser->getUserInfo();
	
		Better_User::getInstance($uid)->updateUser(array(
			'last_active' => time(),
			'followings' => $userInfo['followings']+1
			));
			
		Better_User::getInstance($followingUid)->updateUser(array(
			'followers' => $followingUserInfo['followers']+1
			));
			
		Better_DAO_FollowRequest::getInstance($uid)->deleteByCond(array(
			'uid' => $followingUid,
			'request_uid' => $uid,
			));	
			
		Better_DAO_DmessageReceive::getInstance($uid)->updateByCond(array(
			'act_result' => 1
			), array(
				'uid' => $uid,
				'from_uid' => $followingUid,
				'type' => 'follow_request'
			));	
						
		Better_DAO_DmessageReceive::getInstance($followingUid)->updateByCond(array(
			'act_result' => 1
			), array(
				'uid' => $followingUid,
				'from_uid' => $uid,
				'type' => 'follow_request'
			));	*/
						
	}
	
	public function onBlockedSomebody(array $params)
	{
		$uid = $params['uid'];
		$blocked_uid = $params['blocked_uid'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		$bUser = Better_User::getInstance($blocked_uid);
		$bUserInfo = $bUser->getUser();
		
		$user->updateUser(array(
			'last_active' => time(),
		));

		//取消关注关系
		//$user->follow()->delete($blocked_uid);
		//$bUser->follow()->delete($uid);
		
		//	如果有关注请求，也先删除请求
		/*if ($user->follow()->hasRequest($blocked_uid)) {
			$user->follow()->reject($blocked_uid);
		}
		
		if ($bUser->follow()->hasRequest($uid)) {
			$bUser->follow()->reject($uid);
		}*/
		
		//	删除彼此的好友关系
		if ($user->friends()->isFriend($blocked_uid)) {
			$user->friends()->delete($blocked_uid);
			$bUser->friends()->delete($uid);
		} else {
			$bUser->friends()->hasRequest($uid) && $user->friends()->reject($blocked_uid);
			$user->friends()->hasRequest($blocked_uid) && $bUser->friends()->reject($uid);
		}
		
		
		//	删除彼此的好友请求
		Better_DAO_FriendsRequestToMe::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'request_uid' => $blocked_uid,
			));
		Better_DAO_FriendsRequestToMe::getInstance($blocked_uid)->deleteByCond(array(
			'uid' => $blocked_uid,
			'request_uid' => $uid,
			));
										
		Better_DAO_FriendsRequest::getInstance($blocked_uid)->deleteByCond(array(
			'uid' => $blocked_uid,
			'request_to_uid' => $uid,
			));
		Better_DAO_FriendsRequest::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'request_to_uid' => $blocked_uid,
			));			
			
		//	删除彼此的私信
		Better_DAO_DmessageReceive::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'from_uid' => $blocked_uid,
			'type' => 'friend_request'
			));
		Better_DAO_DmessageReceive::getInstance($uid)->deleteByCond(array(
			'uid' => $blocked_uid,
			'from_uid' => $uid,
			'type' => 'friend_request'
			));
			
		Better_DAO_DmessageReceive::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'from_uid' => $blocked_uid,
			'type' => 'follow_request'
			));
		Better_DAO_DmessageReceive::getInstance($uid)->deleteByCond(array(
			'uid' => $blocked_uid,
			'from_uid' => $uid,
			'type' => 'follow_request'
			));			
	}
	
	public function onUserCreated(array $params)
	{
		$uid = $params['userInfo']['uid'];
		Better_User::getInstance($uid)->follow()->request(BETTER_SYS_UID);
		
	}
	
	public function onUserChanged(array $params)
	{
	}	
	
	public function onFollowRequest(array $params)
	{
	}
	
	public function onDirectMessageSent(array $params)
	{
		$uid = $params['uid'];
		$receiverUid = $params['receiver_uid'];
		
		$user = Better_User::getInstance($uid);
		$receiverUser = Better_User::getInstance($receiverUid);
		
		$userInfo = $user->getUserInfo();
		$receiverUserInfo = $receiverUser->getUserInfo();
		
		$user->updateUser(array(
			'sent_msgs' => $userInfo['sent_msgs']+1,
		));
		$receiverUser->updateUser(array(
			'received_msgs' => $receiverUserInfo['received_msgs']+1,
			'new_msgs' => $receiverUserInfo['new_msgs']+1,
		));
								
	}
	
	public function onBlogReplyPosted(array $params)
	{
		$uid = $params['uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();

		$data = array(
			'posts' => $userInfo['posts']+1,
			'last_active' => time(),
		);

		switch ($params['blog']['type']) {
			case 'normal':
				$data['now_posts'] = +1;
				break;
			case 'tips':
				$data['now_tips'] = +1;
				break;
			case 'checkin':
				$data['checkins'] = +1;
				break;
		}

		$user->updateUser($data, true);
	}	
	
	public function onAddedFavorite(array $params)
	{
		$uid = $params['uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		Better_User::getInstance($uid)->updateUser(array(
			'favorites' => $userInfo['favorites']+1,
		));
	}
	
	public function onUserDeleted(array $params)
	{	
	
	}	
	
	public function onUserLogin(array $params)
	{
		$uid = $params['uid'];
		$user = Better_User::getInstance($uid);
		
		$user->updateUser(array(
			'lastlogin' => time(),
			'last_active' => time(),
			'lastlogin_partner' => $params['partner'],
		));
	}
	
	public function onUserLogout(array $params)
	{
	}		
	
	public function onUnfollowSomebody(array $params)
	{
		/*$uid = $params['uid'];
		$following_uid = $params['following_uid'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		$fUser = Better_User::getInstance($following_uid);
		$fUserInfo = $fUser->getUserInfo();

		$user->updateUser(array(
			'followings' => $userInfo['followings'] -1
			));
		$fUser->updateUser(array(
			'followers' => $fUserInfo['followers'] -1
			));*/
	}
	
	public function onUserCheckin(array $params)
	{
		$uid = $params['uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		$poiInfo = Better_Poi_Info::getInstance($params['poi_id']);
		list($x, $y) = Better_Functions::LL2XY($poiInfo->lon, $poiInfo->lat);
		
		$data = array(
			'posts' => $userInfo['posts']+1,
			'checkins' => $userInfo['checkins']+1,
			'lbs_report' => time(),
			);
			
		if ($params['checkins']>0) {
			$data['places'] = $userInfo['places']+1;
		}
		
		if ($params['priv']=='public') {
			$data['last_checkin_poi'] = $poiInfo->poi_id;
			$data['city'] = $poiInfo->city;
			$data['address'] = $poiInfo->address;
			$data['x'] = $x;
			$data['y'] = $y;
			$data['last_checkin_from'] = defined('IN_API') ? 'api' : 'web';
		}
		
		$user->updateUser($data);
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
		$userInfo = $user->getUserInfo();
		$friendUserInfo = $friendUser->getUserInfo();
		
		//$user->notification()->friendRequest()->clear($friendUid);
		$friendUser->notification()->friendRequest()->clear($uid);
		
		$user->ping()->pingOn($friendUid);
		
		//$followingUids = $user->follow()->getFollowings();
		//	成为好友后自动双向关注
		/*$getkarma = 0;
		if (!in_array($friendUid, $followingUids)) {
			$user->follow()->forceAdd($friendUid,$getkarma);
		} else {
			Better_Hook::factory(array(
				'Queue'
				))->invoke('FollowSomebody', array(
					'uid' => $uid,
					'following_uid' => $friendUid,
					));
		}*/
		
		/*$followingUids = $friendUser->follow()->getFollowings();
		if (!in_array($uid, $followingUids)) {
			$friendUser->follow()->forceAdd($uid,$getkarma);	
		} else {
			Better_Hook::factory(array(
				'Queue'
				))->invoke('FollowSomebody', array(
					'uid' => $friendUid,
					'following_uid' => $uid
					));
		}*/
		
		$user->updateUser(array(
			'friends' => $userInfo['friends']+1,
			));
		$friendUser->updateUser(array(
			'friends' => $friendUserInfo['friends']+1,
			));
			
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
	}	
	
	public function onUnblockSomebody(array $params)
	{
		$uid = $params['uid'];
		
		Better_User::getInstance($uid)->updateUser(array(
			'last_active' => time()
			));
	}
	
	public function onUnfriendWithSomebody(array $params)
	{
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		
		$user = Better_User::getInstance($uid);
		$friendUser = Better_User::getInstance($friendUid);
		
		$user->ping()->pingOff($friendUid);
		$friendUser->ping()->pingOff($uid);		
		
		$userInfo = $user->getUserInfo();
		$friendUserInfo = $friendUser->getUserInfo();
		
		$user->updateUser(array(
			'friends' => $userInfo['friends']-1,
			));
		$friendUser->updateUser(array(
			'friends' => $friendUserInfo['friends']-1,
			));
	}
	
	public function onRejectFriendRequest(array $params)
	{
		$uid = $params['uid'];
		$requestUid = $params['request_uid'];
		
		$user = Better_User::getInstance($uid);
		$requestUser = Better_User::getInstance($requestUid);
		$userInfo = $user->getUserInfo();
		$requestUserInfo = $requestUser->getUserInfo();
		
		//$user->notification()->friendRequest()->clear($requestUid);
		//$requestUser->notification()->friendRequest()->clear($uid);
	
	}
	
	public function onInviteTodoSent($params)
	{
		self::onDirectMessageSent($params);
	}
	
}
