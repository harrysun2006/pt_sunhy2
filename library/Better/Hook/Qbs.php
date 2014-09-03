<?php

/**
 * QBS
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Qbs extends Better_Hook_Base
{
	public function onPoiPollSubmitted(array $params)
	{
		
	}	
	
	public function onPoiCreated(array $params)
	{
		
	}	
	
	/**
	 * 围脖提交以后的事件，更新qbs
	 * 
	 * @param $params
	 * @return null
	 */
	public function onBlogPosted(array $params)
	{
		$bid = $params['bid'];
		$data = &$params['data'];
		$blog = &$params['blog'];
		
		$qbs = Better_Service_Qbs::getInstance();
		$qbsResult = $qbs->updateBlogXY($data['lon'], $data['lat'], time(), $bid, $blog['address'], $blog['city']);

		if (is_array($qbsResult) && isset($qbsResult[1])) {
			Better_Log::getInstance()->logAlert('QBS Error: ['.($qbsResult[0] ? "true" : "false").']['.$qbsResult[1].']', 'qbs');
		}

	}

	public function onBlogDeleted(array $params)
	{
		//	删除qbs中的记录
		try {/*
			$qbs = Better_Service_Qbs::getInstance();
			$qbsResult = $qbs->deleteBlogXY($blog['bid'], $blog['dateline']);
			if (is_array($qbsResult) && isset($qbsResult[1])) {
				Better_Log::getInstance()->logAlert('QBS Error (in blog delete): ['.($qbsResult[0] ? "true" : "false").']['.$qbsResult[1].']', 'qbs');
			}*/
		} catch(Better_Exception $e) {
			Better_Log::getInstance()->logAlert('QBS Exception (in blog delete): ['.$e->getMessage().']', 'qbs');
		}		
	}	
	
	public function onUserChanged(array $params)
	{
		$oldUserInfo = &$params['oldUserInfo'];
		$newUserInfo = &$params['newUserInfo'];
		$uid = $oldUserInfo['uid'];
				
		if (isset($newUserInfo['lon']) && isset($newUserInfo['lat'])) {
			try {
				$qbs = Better_Service_Qbs::getInstance();
				$qbs->updateUserXY($newUserInfo['lon'], $newUserInfo['lat'], time(), $uid, $newUserInfo['address'], $newUserInfo['city']);
			} catch(Better_Exception $e) {
				Better_Log::getInstance()->logAlert('QBS_Exception:['.$e->getMessage().']', 'qbs');
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
		$userInfo = Better_User::getInstance($uid)->getUser();
		
		$qbs = Better_Service_Qbs::getInstance();
		$qbsResult = $qbs->deleteUserXY($uid, $userInfo['lbs_report']);
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