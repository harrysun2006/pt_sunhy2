<?php

/**
 * QBS备份表
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Qbsbackup extends Better_Hook_Base
{
	public function onPoiPollSubmitted(array $params)
	{
		
	}	
	
	public function onPoiCreated(array $params)
	{
		
	}

	/**
	 * 围脖提交以后的事件
	 * 
	 * @param $params
	 * @return null
	 */
	public function onBlogPosted(array $params)
	{
		$bid = $params['bid'];
		$blog = &$params['blog'];
		
		Better_DAO_BlogLocation::getInstance()->replace(array(
			'bid' => $bid,
			'x' => $blog['x'],
			'y' => $blog['y'],
			'address' => $blog['address'],
			'lbs_report' => time(),
			'city' => $blog['city']
			));
	}
	
	public function onBlogDeleted(array $params)
	{
		Better_DAO_BlogLocation::getInstance($params['blog']['uid'])->delete($params['blog']['bid']);
	}		
	
	public function onUserChanged(array $params)
	{
		$oldUserInfo = &$params['oldUserInfo'];
		$newUserInfo = &$params['newUserInfo'];
		$uid = $oldUserInfo['uid'];
				
		if (!empty($newUserInfo['x']) && !empty($newUserInfo['y'])) {
			Better_DAO_User_Location::getInstance($uid)->replace(array(
				'uid' => $uid,
				'x' => $newUserInfo['x'],
				'y' => $newUserInfo['y'],
				'address' => !empty($newUserInfo['address']) ? $newUserInfo['address'] : 'UNKNOWN',
				'lbs_report' => time(),
				'city' => $newUserInfo['city'],
				));	
	
			//	记录我到过的地方
			/*
			Better_DAO_User_PlaceLog::getInstance($uid)->insert(array(
				'uid' => $uid,
				'address' => !empty($newUserInfo['address']) ? $newUserInfo['address'] : 'UNKNOWN',
				'x' => $newUserInfo['x'],
				'y' => $newUserInfo['y'],
				'city' => $newUserInfo['city'],
				'dateline' => BETTER_NOW,
				));	*/
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
		
		Better_DAO_User_Location::getInstance()->deleteByCond(array(
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