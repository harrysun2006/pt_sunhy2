<?php

/**
 * 一些秘密处理
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Secret extends Better_Hook_Base
{

	public function onBindcell(array $params)
	{
		$data = &$params['data'];
		$uid = $params['uid'];
				
		if ($data['state']==Better_User_State::SIGNUP_VALIDATING) {
			$regParams = Better_DAO_Imei_Logs::getInstance()->getUidRegParams($uid);
			
			if ($regParams['imei']) {
				$num = rand(1,100)/100;
				$rate = Better_Imei::partnerRate($regParams['partner']);
				
				if ($rate==1 || $num<=$rate) {
						$toSave = array(
							'imei' => $regParams['imei'],
							'reg_last_active' => time(),
							'reg_partner' => $regParams['partner'],
							'reg_uid' => $uid,
							'reg_platform' => $regParams['platform'],
							'reg_version' => $regParams['version'],
							'reg_model' => $regParams['model'],
							'action' => 0
							);
						Better_Imei::saveMirror($toSave);				
				}
			}
		}
	}	
	
	public function onEmailBinded(array $params)
	{

		$uid = (int)$params['uid'];
		$beforeState = $params['before_state'];	

		if ($beforeState==Better_User_State::SIGNUP_VALIDATING) {
			$regParams = Better_DAO_Imei_Logs::getInstance()->getUidRegParams($uid);

			if ($regParams['imei']) {
				$num = rand(1, 100)/100;
				$rate = Better_Imei::partnerRate($regParams['partner']);
				
				if ($rate==1 || $num<=$rate) {
					$toSave = array(
						'imei' => $regParams['imei'],
						'reg_last_active' => time(),
						'reg_partner' => $regParams['partner'],
						'reg_uid' => $uid,
						'reg_platform' => $regParams['platform'],
						'reg_version' => $regParams['version'],
						'reg_model' => $regParams['model'],
						'action' => '0'
						);
					Better_Imei::saveMirror($toSave);
				}
			}
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
		$secret = $params['data']['secret'];

		if ($secret) {
			$imei = Better_Imei::decrypt($secret);
			
			if ($imei) {
				Better_Imei::save(array(
					'imei' => $imei,
					'action' => '0',
					'reg_uid' => $params['userInfo']['uid'],
					'reg_partner' => $params['data']['partner'],
					'reg_version' => $params['data']['version'],
					'reg_platform' => $params['data']['platform'],
					'reg_model' => $params['data']['model'],
					));
			}
		}
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
		if (defined('IN_API') && defined('BETTER_IMEI') && BETTER_IMEI) {
			$imei = Better_Imei::decrypt(BETTER_IMEI);
			$data = Better_Registry::get('POST');

			if ($imei) {
				$toSave = array(
					'imei' => $imei,
					'uid' => $params['uid'],
					'partner' => $params['partner'] ? $params['partner'] : $data['partner'],
					'version' => $data['version'],
					'platform' => $data['platform'],
					'model' => $data['model'],
					'action' => '1',
					);
				Better_Imei::save($toSave);
				Better_Imei::saveMirror($toSave);
			}
		}
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