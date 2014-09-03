<?php

/**
 * 发消息
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Blog extends Better_Hook_Base
{
	/**
	 * 
	 * 用户获得勋章时自动发一条吼吼
	 * 
	 * @param array $params
	 */
	public function onGetBadge(array $params)
	{
		$badgeId = (int)$params['badge_id'];
		$uid = (int)$params['uid'];
		$poiId = (int)$params['poi_id'];
		
		$blog = Better_Registry::get('blog_last_params');
		$badge = Better_Badge::getBadge($badgeId)->getParams();
			
		$source = $blog['source'];
		$source || Better_Config::getAppConfig()->blog->default_source;		
		$data = array(
			'upbid' => 0,
			'poi_id' => $poiId,
			'type' => 'normal',
			'priv' => 'public',
			'source' => $source,
			'x' => $blog['x'],
			'y' => $blog['y'],
			'lon' => $blog['lon'],
			'lat' => $blog['lat'],
			'city' => $blog['city'],
			'address' => $blog['address'],
			);		
	}
	
	/**
	 * 用户对贴士投票后，需要更新Blog表的投票结果数据
	 * @see Better_Hook_Base::onPoiPollSubmitted()
	 */
	public function onPoiPollSubmitted(array $params)
	{
		$bid = $params['bid'];
		$uid = $params['uid'];
		list($buid, $foobar) = explode('.', $bid);
		
		$result = 0;
		if ($params['option']=='up') {
			$result = 1;
		} else if ($params['option']=='down') {
			$result = -1;
		}
		
		if ($result!=0) {
			Better_DAO_Blog::getInstance($buid)->increase('poll_result', array(
				'bid' => $bid,
				'uid' => $buid
			), $result);
			
			Better_DAO_Blog::getInstance($buid)->increase('polls', array(
				'bid' => $bid,
				'uid' => $buid
			), 1);
			
			if ($result==1) {
				Better_DAO_Blog::getInstance($buid)->increase('up', array(
					'bid' => $bid,
					'uid' => $buid
					), 1);
			} else if ($result==-1) {
				Better_DAO_Blog::getInstance($buid)->increase('down', array(
					'bid' => $bid,
					'uid' => $buid
					), 1);				
			}
		}
	}
		
	public function onPoiCreated(array $params)
	{
		
	}
		
	public function onBlogPosted(array $params)
	{
	}
	
	public function onBlogDeleted(array $params)
	{
		$blog = &$params['blog'];
		$bid = $blog['bid'];
		
		Better_DAO_Blog::resetUpbid($bid);
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
		Better_Blog::addFavorited($params['uid'], $params['bid']);
	}
	
	public function onUserDeleted(array $params)
	{
		$uid = $params['uid'];
		$bids = array();
		$qbs = Better_Service_Qbs::getInstance();
		
		$data = Better_DAO_Blog::getInstance($uid)->getAll(array(
			'uid' => $uid,
			));
		foreach($data as $row) {
			if ($row['attach']) {
				Better_Attachment::getInstance($row['attach'])->delFile($row['attach']);
			}
			
			$qbs->deleteBlogXY($row['bid'], $row['dateline']);
			Better_DAO_BlogLocation::getInstance($uid)->delete($row['bid']);
			Better_DAO_Blogreply::getInstance($uid)->deleteByCond(array(
				'realbid' => $row['bid'],
			));
		}
		
		Better_DAO_Blog::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			));
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
		$data = array(
			'type' => 'checkin',
			'poi_id' => $params['poi_id'],
			'lon' => $params['lon'],
			'lat' => $params['lat'],
			'message' => $params['message'],
			'attach' => $params['photo'],
			'priv' => $params['priv'] ? $params['priv'] : 'public',
			'source' => isset($params['source']) ? $params['source'] : 'kai',
			'need_sync' =>isset($params['checkin_need_sync']) ? $params['checkin_need_sync'] : 1
			);
			
		if (Better_Hook::$hookResults['UserCheckin']['major_changed']) {
			$data['major'] = 1;
		}
		
		$uid = $params['uid'];

		//	自动发一条check微博
		$data_checkin = $data;
		if ($params['is_tips']) {
			$data_checkin['message'] = '';
		}
		$bid = Better_User::getInstance($uid)->blog()->add($data_checkin);		
		
		if ($params['is_tips']) {
			$in_reply_to_status_id = 0;
			$_bid = Better_Blog::post($uid, array(
							'message' => $data['message'],
							'upbid' => $in_reply_to_status_id,
							'attach' => $data['attach'],
							'source' => $data['source'],
							'poi_id' => $data['poi_id'],
							'type' => 'tips',
							'need_sync' => $data['need_sync'],
							'passby_spam' => true,
							));	
			
		}

		Better_Hook::$hookResults['UserCheckin']['bid'] = $bid;
		Better_Registry::set('checkin_bid', $bid);
	}
	
	public function onFriendRequest(array $params)
	{
		
	}
	
	public function onFriendWithSomebody(array $params)
	{
		$flag = Better_Config::getAppConfig()->friend_shout;
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
		
		if ($flag) {
			$uid = $params['uid'];
			$friendUid = $params['friend_uid'];		
			
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUserInfo();
			
			$counter = (int)Better_Cache::remote()->get('fb_counter_'.$uid.'_'.$friendUid);
			$last = $user->cache()->get('fb_with_'.$friendUid);

			if (!$last && $counter<=1) {
				$user->cache()->set('fb_with_'.$friendUid, 'yes', 60);
				switch (BETTER_CONTROLLER_MODULE) {
					case 'mobile':
						$uSource = 'wap';
						break;
					case 'api':
						$uSource = Better_Functions::partner2source($userInfo['lastlogin_partner']);
						break;
					default:
						$uSource = 'web';
						break;
				}
				
				$friendUser = Better_User::getInstance($friendUid);
				$friendUserInfo = $friendUser->getUserInfo();
				$fuSource = Better_Functions::partner2source($friendUserInfo['lastlogin_partner']);
				
				$content = str_replace('{LINK}', '@'.$friendUserInfo['nickname'], $user->getUserLang()->global->friend_shout);
				
				$user->blog()->add(array(
					'message' => $content,
					'priv' => 'public',
					'upbid' => 0,
					'poi_id' => 0,
					'need_sync' => 0,
					'passby_spam' => 1,
					'nokarma' => 1,
					'passby_filter' => 1,
					'source'=>''
					));
					
				$content = str_replace('{LINK}', '@'.$userInfo['nickname'], $friendUser->getUserLang()->global->friend_shout);
				$friendUser->blog()->add(array(
					'message' => $content,
					'priv' => 'public',
					'upbid' => 0,
					'poi_id' => 0,
					'need_sync' => 0,
					'passby_spam' => 1,
					'nokarma' => 1,
					'passby_filter' => 1,
					'source'=>''
					));			
			}	
		}	
		
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
	}
	
	public function onFriendWithSomebodys(array $params)
	{
		$flag = Better_Config::getAppConfig()->friend_shout;
		Better_Log::getInstance()->logTime(__METHOD__.':'.__LINE__);
		if ($flag) {
			$uid = $params['uid'];
			$friendUid = $params['friend_uid'];		
			Better_Log::getInstance()->logInfo(serialize($friendUid),'friendsrequest');
			$user = Better_User::getInstance($uid);
			$userInfo = $user->getUserInfo();
			if (true) {
				
				switch (BETTER_CONTROLLER_MODULE) {
					case 'mobile':
						$uSource = 'wap';
						break;
					case 'api':
						$uSource = Better_Functions::partner2source($userInfo['lastlogin_partner']);
						break;
					default:
						$uSource = 'web';
						break;
				}
				
				$friendsNickname='';
				foreach($friendUid as $rows){					
					$friend_uid = $rows;
					
					$counter = (int)Better_Cache::remote()->get('fb_counter_'.$uid.'_'.$friend_uid);
					$last = $user->cache()->get('fb_with_'.$friend_uid);
					$user->cache()->set('fb_with_'.$friend_uid, 'yes', 60);					
					$friendUser = Better_User::getInstance($friend_uid);
					$friendUserInfo = $friendUser->getUserInfo();
					$fuSource = Better_Functions::partner2source($friendUserInfo['lastlogin_partner']);
					$content = str_replace('{LINK}', '@'.$userInfo['nickname'], $friendUser->getUserLang()->global->friend_shout);
					
					$friendUser->blog()->add(array(
						'message' => $content,
						'priv' => 'public',
						'upbid' => 0,
						'poi_id' => 0,
						'need_sync' => 0,
						'passby_spam' => 1,
						'nokarma' => 1,
						'passby_filter' => 1
						));	
					
					$addstr = " ";
					$friendsNickname .= $addstr.'@'.$friendUserInfo['nickname'];
					
				}	
				
				$content = str_replace('{LINK}', $friendsNickname, $user->getUserLang()->global->friend_shout);				
				$user->blog()->add(array(
					'message' => $content,
					'priv' => 'public',
					'upbid' => 0,
					'poi_id' => 0,
					'need_sync' => 0,
					'passby_spam' => 1,
					'nokarma' => 1,
					'passby_filter' => 1
					));
					
						
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
}