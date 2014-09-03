<?php

/**
 * 人品计算
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Rp extends Better_Hook_Base
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
		$uid = $params['uid'];
		$poiId = $blog['poi_id'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		$cangotrp = $user->rp()->cangetnativedaytotalrp();			
		if ((Better_Config::getAppConfig()->useractive->gotkarma || $user->isActive()) && $cangotrp && $blog['type']!='checkin') {	
			$rp = 0;	
			
			if($blog['type']=='normal'){
				$category = "postblog";
				if($blog['priv']=='private'){
					$rp = $rp + $user->rp()->calculate('Private_Blog');
					$category .="_private";
								
				} else {
					$rp = $rp + $user->rp()->calculate('Common_Blog');
					$category .="_common";
					
				}
				if(($blog['priv']=='public') && $blog['need_sync'] && ($tmpsyncsite=count(Better_User_Syncsites::getInstance($uid)->getSites()))){
					$tmpsyncsite =($tmpsyncsite>Better_Config::getAppConfig()->rp->maxsynctimes) ? Better_Config::getAppConfig()->rp->maxsynctimes : $tmpsyncsite;
					$rp = $rp + $tmpsyncsite*$user->rp()->calculate('Sync_Blog');
					$category .="_sync";					
				}
			} else if($blog['type']=='tips'){
				$category = "newtips";
				$rp = $user->rp()->calculate('NewTips');
			}
			
			if($cangotrp<$rp){
				$rp = $cangotrp;
			}
			if ($rp!=0 && $user->rp()->update(array(
					'rp' => $rp,
					'category' => $category,
					)))  {
						$message = str_replace('{RP}', $karma, $lang->rp->blog->title);
						Better_Hook::$hookNotify['BlogPosted']['rp'] = $message;
			}				
		}
		
	}
	
	public function onBlogDeleted(array $params)
	{
	
		$blog = &$params['blog'];
		$userInfo = &$params['userInfo'];
		$co_uid = $params['co_uid'];
		$user = Better_User::getInstance($userInfo['uid']);
		
		$rp = $user->rp()->calculate('Delete');
		if ($rp!=0) {
			$user->rp()->update(array(
				'rp' => $rp,
				'category' => 'delete',
				'co_uid' =>$co_uid ,
				));
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
		$cangotrp = $user->rp()->cangetnativedaytotalrp();
		if((Better_Config::getAppConfig()->useractive->gotkarma || $user->isActive())){
			$rp = $user->rp()->calculate('NewFollower');		
			if($cangotrp<$rp)
			{
				$rp = $cangotrp;
			}
			if ($rp!=0) {
				
				$user->rp()->update(array(
					'rp' => $rp,
					'category' => 'newfollower',
					'co_uid' => $followingUid,
					));
			}
		}*/
		
	}
	
	public function onBlockedSomebody(array $params)
	{
		
	}
	
	public function onUserCreated(array $params)
	{
		$userInfo = &$params['userInfo'];
		
		if (isset($userInfo['ref_uid']) && $userInfo['ref_uid']) {
			$refUser = Better_User::getInstance($userInfo['ref_uid']);
			$cangotrp = $refUser->rp()->cangetnativedaytotalrp();		
			$category = "invitesomebody";
			if($refUser->isActive()){
				$rp = $refUser->rp()->calculate('InviteSomebody');
				if($cangotrp<$rp){
					$rp = $cangotrp;
				}			
				if($refUser->isActive()){$rp!=0 && $refUser->rp()->update(array(
						'rp' => $rp,
						'category' => $category,
						));
				}	
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
		$loginbycellno = $params['loginbycellno'];		
		$loginbyapi = $params['loginbyapi'];	
		$category = "login";		
		$cangotrp = $user->rp()->cangetnativedaytotalrp();	
		$autologin = isset($params['autologin']) ? $params['autologin'] : 0 ;
		
		if($autologin){
			$now = time();
			Better_Log::getInstance()->logInfo($autologin."**".$uid."**".$now,'rpautologin');
		}		
		if((Better_Config::getAppConfig()->useractive->gotkarma || $user->isActive())){
			if(isset($userInfo['cell_no']) && strlen($userInfo['cell_no'])>0){
				$rp = $user->rp()->calculate('loginbycell');
				$category .="_bycell";
			} else {
				$rp = $user->rp()->calculate('login');
			}	
				
			if ($rp!=0 && $user->rp()->update(array(
					'rp' => $rp,
					'category' => $category,
					))) {
	
				$lang = Better_Registry::get('lang');
				$message = str_replace('{RP}', Better_Rp::format($rp), $lang->rp->login->title);
			}
			
			$message!='' && Better_Hook::$hookMessages['UserLogin']['rp'] = $message;
		}
	}
	
	public function onUserLogout(array $params)
	{
		$uid = $params['uid'];
	}	
	
	public function onUnfollowSomebody(array $params)
	{
		
		
	}
	
	public function onUserCheckin(array $params)
	{
		$checkinId = $params['checkin_id'];
		$uid = $params['uid'];
		$poiId = $params['poi_id'];
		$checkins = $params['checkins'];
		$checkinTime = $params['check_time'];
		$priv = $params['priv']; 
		$checkin_need_sync = $params['checkin_need_sync'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		$cangotrp = $user->rp()->cangetnativedaytotalrp();
		if ((Better_Config::getAppConfig()->useractive->gotkarma || $user->isActive()) && $cangotrp) {
			$rp = 0;
			$category = "checkin";
			if($priv=='private'){
				$rp = $rp + $user->rp()->calculate('Private_Checkin');
				$category .="_private";
			} else {
				$rp = $rp + $user->rp()->calculate('Common_Checkin');
				$category .="_common";
			}
			if(($priv=='public') && $checkin_need_sync && ($tmpsyncsite=count(Better_User_Syncsites::getInstance($uid)->getSites()))){
				$tmpsyncsite =($tmpsyncsite>Better_Config::getAppConfig()->rp->maxsynctimes) ? Better_Config::getAppConfig()->rp->maxsynctimes : $tmpsyncsite;
				$rp = $rp + $tmpsyncsite*$user->rp()->calculate('Sync_Checkin');
				$category .="_sync";
			}
			
			if($cangotrp<$rp){
				$rp = $cangotrp;
			}
			if ($rp!=0 && $user->rp()->update(array(
					'rp' => $rp,
					'category' => $category,
					))) {			
				
				$lang = Better_Registry::get('lang');
				Better_Hook::$hookResults['UserCheckin']['rp'] = str_replace('{RP}', Better_Rp::format($rp), $lang->rp->checkin->title);
				Better_Hook::$hookNotify['UserCheckin']['rp'] = str_replace('{RP}', Better_Rp::format($rp), $lang->rp->checkin->title);
			}
		}
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
		$userInfo = $user->getUserInfo();
		$friendUser = Better_User::getInstance($friendUid);
		$friendUserInfo = $friendUser->getUserInfo();
		$ucangotrp = $user->rp()->cangetnativedaytotalrp();
		$fcangotrp = $friendUser->rp()->cangetnativedaytotalrp();
		$uRp = $fRp = 0;
		if((Better_Config::getAppConfig()->useractive->gotkarma || $user->isActive())){
			$uRp = $user->rp()->calculate('FriendWithSomebody', array(
				'friend_uid' => $friendUid
				));
		}
		if((Better_Config::getAppConfig()->useractive->gotkarma || $friendUser->isActive())){
			$fRp = $friendUser->rp()->calculate('FriendWithSomebody', array(
				'friend_uid' => $uid,
				));
		}
		$uRp = ($ucangotrp<$uRp) ? $ucangotrp :$uRp;
		$fRp = ($fcangotrp<$fRp) ? $fcangotrp :$fRp;
		if ($uRp!=0) {
			$user->rp()->update(array(
				'rp' => $uRp,
				'category' => 'friendwithsomebody',
				'co_uid' => $friendUid,
				));
		}
		
		if ($fRp!=0) {
			$friendUser->rp()->update(array(
				'rp' => $fRp,
				'category' => 'friendwithsomebody',
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
		
	}
	
	public function onRejectFriendRequest(array $params)
	{
		
	}
	public function onTobeMajor(array $params)
	{		
		$uid = $params['uid'];		
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		$cangotrp = $user->rp()->cangetnativedaytotalrp();
		if ((Better_Config::getAppConfig()->useractive->gotkarma || $user->isActive()) && $cangotrp) {
			$category = "tobemajor";
			$rp = $user->rp()->calculate('TobeMajor');
			if($cangotrp<$rp){
				$rp = $cangotrp;
			}
			
			if ($rp!=0 && $user->rp()->update(array(
					'rp' => $rp,
					'category' => $category,
					))) {			
						
				$lang = Better_Registry::get('lang');
				Better_Hook::$hookResults['UserCheckin']['rp'] = str_replace('{RP}', Better_Rp::format($rp), $lang->rp->checkin->title);
				Better_Hook::$hookNotify['UserCheckin']['rp'] = str_replace('{RP}', Better_Rp::format($rp), $lang->rp->checkin->title);
			}
		}
	}
}