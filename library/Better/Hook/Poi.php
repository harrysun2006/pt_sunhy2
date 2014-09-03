<?php

/**
 * Poi
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Poi extends Better_Hook_Base
{
	public function onPoiPollSubmitted(array $params)
	{
		
	}	
	
	public function onPoiCreated(array $params)
	{
		
	}
	
	public function onBlogPosted(array $params)
	{
		$blog = &$params['blog'];
		$poiId = $blog['poi_id'];
		
		if ($poiId) {
			$poi = Better_Poi_Info::getInstance($poiId);
			$poiInfo = $poi->getBasic();
			
			switch ($blog['type']) {
				case 'normal':
					$poi->update(array(
						'posts' => $poiInfo['posts']+1,
						));
					break;
				case 'tips':
					$poi->update(array(
						'tips' => $poiInfo['tips']+1,
						));
					break;
			}
			
			$poi->update(array('last_update'=>time()));
		}
	}
	
	public function onBlogDeleted(array $params)
	{
		$blog = &$params['blog'];
		$poiId = $blog['poi_id'];
		
		if ($poiId) {
			$poi = Better_Poi_Info::getInstance($poiId);
			$poiInfo = $poi->getBasic();
			
			switch ($blog['type']) {
				case 'normal':
					$poi->update(array(
						'posts' => $poiInfo['posts']>0 ? $poiInfo['posts']-1 : 0,
						));
					break;
				case 'checkin':
					$poi->update(array(
						'checkins' => $poiInfo['checkins']>0 ? $poiInfo['checkins']-1 : 0,
						));					
					break;
				case 'tips':
					$poi->update(array(
						'tips' => $poiInfo['tips']>0 ? $poiInfo['tips']-1 : 0,
						));
					break;
			}
			
			$poi->update(array('last_update'=>time()));			
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
		$checkins = $params['checkins'];

		$majorChanged = Better_Hook::$hookResults['UserCheckin']['major_changed'];
		
		$majorUid = Better_Poi_Major::getInstance($poiId)->calculate($uid);

		$poi = Better_Poi_Info::getInstance($poiId);
		$poiInfo = $poi->getBasic();
		
		$data = array(
			'checkins' => $poiInfo['checkins']+1,
			'last_update'=>time()
			);
		$majorChanged && $data['major'] = $uid;
		
		$first = Better_DAO_User_PlaceLog::getInstance($uid)->isFirstCheckin($poiId);
		if ($first) {
			$data['visitors'] = $poiInfo['visitors']+1;
			$data['users'] = $poiInfo['users']+1;
			
			$offset = array(2,3,5,8,14,24);
			if (in_array($data['visitors'], $offset) || in_array($data['users'], $offset)) {
				Better_Poi_Fulltext::getInstance()->updateItem($poiInfo['poi_id'], 1);
			}
		}

		$poi->update($data);
		$return = Better_User_Status::getInstance($uid)->friendBlogAroundMe($params);
		$fblog = array();
		if ($return['count'] > 0 && count($return['rows']) > 0) {
			$row = $return['rows'][0];
			$tmp = array(
				'userid' => (int)$row['fuid'],
				'username' => $row['username'],
				'nickname' => $row['nickname'],
				'before' => $params['checkin_time']-$row['dateline'],
				'distance' => (int)$row['distance'],
				'poi_id' => (int)$row['poi_id'],
				'poi_name' => $row['poi']['name'],
				'message' => $row['message'],
			);
			$fblog = array_merge($fblog, $tmp);
			unset($tmp);
		}
		Better_Hook::$hookResults['UserCheckin']['fblog'] = $fblog;
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