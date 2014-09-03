<?php

/**
 * 抄送到第三方
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Syncsites extends Better_Hook_Base
{
	/**
	 * 获得了掌门的事件
	 */
	public function onGetMajor(array $params)
	{
		/*$uid = $params['uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		$poiId = $params['poi_id'];
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();

		//	同步获得掌门的信息到第三方
		$msg = Better_Registry::get('lang')->global->sync_got_major;
		$msg = str_replace('{POI}', $poiInfo['name'], $msg);
				
		Better_Service_ThirdBinding::getInstance($userInfo['uid'])->bind($msg, 0, '', $poiId);				*/				
	}
	
	/**
	 * 获得了勋章的事件
	 * 
	 */
	public function onGetBadge(array $params)
	{
		$uid = (int)$params['uid'];
		$badgeId = (int)$params['badge_id'];
		$poiId = $params['poi_id'];
		$badges = $params['badges'];
		
		if ($uid && $badgeId) {
			$badgeInfo = Better_Badge::getBadge($badgeId)->getParams();
			if($badges){
				$msg = '我获得开开(K.ai) ';
				foreach($badges as $row){
					$msg .="〖{$row['badge_name']}〗 ";
				}
				$msg .= '勋章!';
			}else{
				if ($badgeInfo['badge_name']) {
					$langKey = Better_Registry::get('language');
					if (preg_match('/en/i', $langKey)) {
						$syncTips = $badgeInfo['en_sync_tips'];
					} else {
						$syncTips = $badgeInfo['sync_tips'];
					}
					
					if (!$syncTips) {
						$msg = Better_Registry::get('lang')->global->sync_got_badge;
						$msg = str_replace('{BADGE}', $badgeInfo['badge_name'], $msg);
						//$msg .= " ".Better_User::getInstance($uid)->getUserLang()->global->badge_sync_suffix;
					} else {
						$msg = $syncTips;
					}
					
				}
			}
			
			$attach = Better_Badge::getBadgeSavePath($badgeInfo['id']);
			Better_Service_ThirdBinding::getInstance($uid)->bind($msg, 0, $attach, $poiId, '', true);	
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
		$need_sync = $params['need_sync'];
		if($need_sync){
			$blog = &$params['blog'];
			$user = Better_User::getInstance($params['uid']);
			$userInfo = $user->getUserInfo();
	
			if (Better_Hook::$hookResults['BlogPosted'] != Better_Hook::$RESULT_BLOG_NEED_CHECK) {
				if ($blog['priv']!='private') {
					$poiId = (int)$blog['poi_id'];
					$attach = $params['data']['attach'];
					$userInfo = Better_User::getInstance($blog['uid'])->getUser();	
					if ($attach) {
						$tmp = Better_Attachment::getInstance($attach)->parseAttachment();
						$attach = $tmp['save_path'];
					} else {
						$attach = '';
					}
					
					Better_Service_ThirdBinding::getInstance($userInfo['uid'])->bind($blog['message'], $params['bid'], $attach, $poiId, $blog['type']);	
				}
			}
		}
	}
	
	public function onBlogDeleted(array $params)
	{
		$blog = &$params['blog'];
		$bid = $blog['bid'];
		$third_info = $blog['third_info'];
		if ($third_info) {
			$third_info = rtrim($third_info, ',');
			$array_thrid = explode(',', $third_info);
			foreach ($array_thrid as $v) {
				list($third_protocol, $third_id) = explode('|', $v);
				Better_Service_ThirdBinding::getInstance($blog['uid'])->unbind($third_id, $third_protocol, $blog['uid']);		
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
		$uid = $params['uid'];
		
		Better_DAO_SyncQueue::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			));
		Better_DAO_ThirdBinding::getInstance($uid)->deleteByCond(array(
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