<?php

/**
 * 队列处理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Queue extends Better_Hook_Base
{
	protected $followed = false;
	
	public function onPoiPollSubmitted(array $params)
	{
		
	}
		
	public function onPoiCreated(array $params)
	{
		
	}
	
	public function onBlogPosted(array $params)
	{
		if ($params['data']['no_queue']) {
			return;
		}
		$blog = &$params['blog'];
		$uid = $params['uid'];
		$poiId = $blog['poi_id'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();		
		
		if(Better_Hook::$hookResults['BlogPosted']!=Better_Hook::$RESULT_BLOG_NEED_CHECK && $blog['priv']!='private') {
			$data = array(
				'act_type' => '1',
				'queue_time' => time(),
				'uid' => $uid,
				'bid' => $blog['bid'],
				'poi_id' => $poiId
				);
			
			Better_Queue::push(array(
				'publictimeline'
				), $data);
		}
	}
	
	public function onBlogDeleted(array $params)
	{
		$blog = &$params['blog'];
		$userInfo = &$params['userInfo'];
			
		$user = Better_User::getInstance($userInfo['uid']);		
		
		Better_Queue::push(array(
			'publictimeline'
			), array(
				'act_type' => '2',
				'queue_time' => time(),
				'uid' => $userInfo['uid'],
				'bid' => $blog['bid'],
				'poi_id' => $blog['poi_id']
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
		$userInfo = $user->getUserInfo();
		
		$followingUser = Better_User::getInstance($followingUid);
		$followingUserInfo = $followingUser->getUserInfo();		
		
		Better_Queue::push(array(
			'publictimeline'
			), array(
				'uid' => $uid,
				'act_type' => '3',
				'bid' => '',
				'poi_id' => 0,
				'following_uid' => $followingUid,
				'queue_time' => time()
			));*/
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
		$uid = $oldUserInfo['uid'];
		
		if (isset($newUserInfo['banned'])) {
			if ($newUserInfo['banned']) {
				Better_Queue::push(array(
					'publictimeline'
					), array(
						'uid' => $uid,
						'act_type' => '5',
						'queue_time' => time()
						));
			} else {
				Better_Queue::push(array(
					'publictimeline'
					), array(
						'uid' => $uid,
						'act_type' => '8',
						'queue_time' => time()
						));
			}
		}

		/*if (isset($newUserInfo['priv_blog'])) {
			if ($newUserInfo['priv_blog']) {
				Better_Queue::push(array(
					'publictimeline'
					), array(
						'uid' => $uid,
						'act_type' => '6',
						'queue_time' => time()
						));
			} else {
				Better_Queue::push(array(
					'publictimeline'
					), array(
						'uid' => $uid,
						'act_type' => '9',
						'queue_time' => time()
						));
			}
		}*/
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
		/*$uid = $params['uid'];
		$followingUid = $params['following_uid'];
		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();		
		
		Better_Queue::push(array(
			'publictimeline'
			), array(
				'act_type' => '4',
				'uid' => $uid,
				'following_uid' => $followingUid,
				'queue_time' => time()
			));*/
	}
	
	public function onUserCheckin(array $params)
	{
	}
	
	public function onFriendRequest(array $params)
	{
	}
	
	public function onFriendWithSomebody(array $params)
	{
		$uid = (int)$params['uid'];
		$friendUid = (int)$params['friend_uid'];
		
		/*$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		$friendUser = Better_User::getInstance($friendUid);
		$friendUserInfo = $friendUser->getUserInfo();*/	
		
		Better_Queue::push(array(
			'publictimeline'
			), array(
				'uid' => $uid,
				'act_type' => '10',
				'bid' => '',
				'poi_id' => 0,
				'friend_uid' => $friendUid,
				'queue_time' => time()
			));
			
		Better_Queue::push(array(
			'publictimeline'
			), array(
				'uid' => $friendUid,
				'act_type' => '10',
				'bid' => '',
				'poi_id' => 0,
				'friend_uid' => $uid,
				'queue_time' => time()
			));
	}
	
	public function onUnblockSomebody(array $params)
	{
		
	}
	
	public function onUnfriendWithSomebody(array $params)
	{
		$uid = (int)$params['uid'];
		$friendUid = (int)$params['friend_uid'];
		
		Better_Log::getInstance()->logInfo(__METHOD__.'::'.__LINE__, 'debug', true);
		
		Better_Queue::push(array(
			'publictimeline'
			), array(
				'act_type' => '7',
				'uid' => $uid,
				'friend_uid' => $friendUid,
				'queue_time' => time()
			));		
			
		Better_Queue::push(array(
			'publictimeline'
			), array(
				'act_type' => '7',
				'uid' => $friendUid,
				'friend_uid' => $uid,
				'queue_time' => time()
			));					
	}
	
	public function onRejectFriendRequest(array $params)
	{
		
	}
	
	
	public function onSetHomeShow(array $params){
		$uid = $params['uid'];
		$friendUid = $params['friend_uid'];
		$show = $params['show'];
		
		if($show){//设置为显示某好友动态
			Better_Queue::push(array(
			'publictimeline'
			), array(
				'act_type' => '11',
				'uid' => $uid,
				'friend_uid' => $friendUid,
				'queue_time' => time()
			));		
		}else{//不显示
			Better_Queue::push(array(
			'publictimeline'
			), array(
				'act_type' => '12',
				'uid' => $uid,
				'friend_uid' => $friendUid,
				'queue_time' => time()
			));	
		}
	}
}