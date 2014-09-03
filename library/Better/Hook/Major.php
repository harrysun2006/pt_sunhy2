<?php

/**
 * 掌门处理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Major extends Better_Hook_Base
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
		$uid = $params['uid'];
		$poiId = $params['poi_id'];
		$score = (float)$params['score'];
		$day = $params['day'];
		$blacklist = $params['blacklist'];
		$message = '';
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		$majorChanged = Better_Config::getAppConfig()->major_swtich? true:false;
		$majorUserInfo = array();
		if (in_array($uid, $blacklist)) {
			//特定的UID不参与掌门运算
			return false;
		}
		
		//	用户有头像才进行掌门运算
		if ($userInfo['avatar']) {

			$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
			
			if(!$poiInfo['forbid_major']){	//	该poi没有禁止掌门运算
				$major = (int)$poiInfo['major'];
				$validCount = Better_DAO_User_PlaceLog::getInstance($uid)->getTowMonthCheckinCount($poiId, true);
				 
				if ($major!=0 && $major!=$uid) {			
					//	当前用户两个月内在该poi的签到次数
					$majorUser = Better_User::getInstance($major);
					$majorUserInfo = $majorUser->getUserInfo();
					
					if (!$majorUserInfo['avatar'] && $validCount>=1) {
						$majorChanged = true;
					} else if ($validCount>=1) {
						//	当前掌门的签到权重值
						$mWeight = $majorUser->major()->calMajorWeight($poiId, false, $day);
						
						//	当前用户的签到权重值
						$uWeight = $user->major()->calMajorWeight($poiId, false, $day);
						if ($uWeight>$mWeight) {
							$majorChanged = true;
						}
					}
					
				} else if ($major==0 && $validCount>=1) {
					$majorChanged = true;	
				}
				
				if ($majorChanged===true) {
					//	更新用户掌门数/掌门历史/poi掌门信息
					$user->major()->log($poiId);
					
					//	更新原掌门
					if (isset($majorUserInfo['uid']) && $majorUserInfo['uid']) {
						$majorUser->updateUser(array(
							'majors' => $majorUserInfo['majors']-1,
							));	
						//掌门被夺后发私信给原掌门
						$major_change_info = str_replace('{WHO}','@'.$userInfo['nickname'],Better_Language::load()->global->major_changed);						
						$major_change_info = str_replace('{POI}',$poiInfo['name'],$major_change_info);
						Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
							'content' => $major_change_info,
							'receiver' => $majorUserInfo['uid']
							));
					}
					
					$bid = Better_Hook::$hookResults['UserCheckin']['bid'];
					if ($bid) {
						Better_DAO_Blog::getInstance($uid)->updateByCond(array(
							'major' => '1',
						), array(
							'bid' => $bid,
							));
							
						//	写入新晋掌门表
						Better_DAO_Blog_Lastestmajors::getInstance($uid)->save($uid, $bid);
						
						Better_DAO_Poi_Major::getInstance()->save($poiId, $uid, $poiInfo['x'], $poiInfo['y'],$userInfo['recommend']);
						Better_Hook::factory('Rp')->invoke('TobeMajor', array(								
							'uid' => $uid
							));
					}
					
					Better_Hook::$hookNotify['UserCheckin']['major'] = Better_Language::load()->global->got_major;
				}
				
				Better_Hook::$hookResults['UserCheckin']['major_changed'] = $majorChanged;
				Better_Hook::$hookResults['UserCheckin']['major'] = $message;	
			}		
		} else {
			$text = '拥有头像才能争夺掌门宝座！';
			Better_Registry::set('no_avatar', $text);
		}
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