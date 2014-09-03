<?php

/**
 * 后台发送私信
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook.Admin
 * 
 */

class Better_Hook_Admin_DirectMessage extends Better_Hook_Base
{

	public function onBlogPosted(array $params)
	{
	}
	
	public function onBlogDeleted(array $params)
	{
		$blog = &$params['blog'];
		$userInfo = &$params['userInfo'];
		$filename = $params['filename'];
		$sessUid = Better_Registry::get('sess')->getUid();
		$uid = $userInfo['uid'];

		if (Better_Registry::get('sess')->admin_uid) {
			$data = $userInfo;
			$data['CONTENT'] = $blog['message'];
			$data['DATE'] = date('Y-m-d H:i:s', $blog['dateline']+8*3600);
			
			if($data['CONTENT'] && ($blog['type']=='normal'||$blog['type']=='checkin')){
				Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('blog_was_deleted', $data, $userInfo);
			}else if(!$blog['message'] && $blog['attach']){
				$data['filename'] = $filename;
				Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('attach_was_deleted', $data, $userInfo);
			}else if($data['CONTENT'] && $blog['type']=='tips'){
				Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('tip_was_deleted', $data, $userInfo);
			}
		}
	}
	
	public function onFollowSomebody(array $params)
	{
	}
	
	public function onBlockedSomebody(array $params)
	{
	}
	
	public function onBeforeQbsQuery(array $params)
	{
	}
	
	public function onAfterQbsQuery(array $params)
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
		
	}
	
	public function onFriendRequest(array $params)
	{
		
	}
	
	public function onFriendWithSomebody(array $params)
	{
		
	}
	
	public function onUnfriendWithSomebody(array $params)
	{
		
	}
	
	public function onRejectFriendRequest(array $params)
	{
		
	}
	
	public function onPoiCreated(array $params)
	{
		
	}
	
	public function onUnblockSomebody(array $params)
	{
		
	}
	
	public function onPoiPollSubmitted(array $params)
	{
		
	}
	
	/**
	 * @param unknown_type $params
	 */
	public function onResetUserName($params) {
		$userInfo = &$params['userInfo'];

		if (Better_Registry::get('sess')->admin_uid) {
			$data = $userInfo;
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('username_was_reset', $data, $userInfo);
		}
	}
	
	/**
	 * @param unknown_type $params
	 */
	public function onResetNickName($params) {
		$userInfo = &$params['userInfo'];

		if (Better_Registry::get('sess')->admin_uid) {
			$data = $userInfo;
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('nickname_was_reset', $data, $userInfo);
		}
	}

	
	/**
	 * @param unknown_type $params
	 */
	public function onResetUserPlace($params) {
		
	}

	/**
	 * @param unknown_type $params
	 */
	public function onResetUserSelfintro($params) {
		$userInfo = &$params['userInfo'];

		if (Better_Registry::get('sess')->admin_uid) {
			$data = $userInfo;
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('userselfintro_was_reset', $data, $userInfo);
		}
	}

	/**
	 * @param unknown_type $params
	 */
	public function onUserAvatarDeleted($params) {
		$userInfo = &$params['userInfo'];

		if (Better_Registry::get('sess')->admin_uid) {
			$data = $userInfo;
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('avatar_was_deleted', $data, $userInfo);
		}
	}
	
	
	public function onSmessageDeleted($params){
		$userInfo = $params['userInfo'];
		$message = $params['message'];
		
		if (Better_Registry::get('sess')->admin_uid) {
			$data['CONTENT'] = $message['content'];
			$data['DATE'] = date('Y-m-d H:i:s', $message['dateline']+8*3600);
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('smessage_was_deleted', $data, $userInfo);
		}
	}
		
	
	public function onRmessageDeleted($params){
		$userInfo = $params['userInfo'];
		$message = $params['message'];
		
		if (Better_Registry::get('sess')->admin_uid) {
			$data['CONTENT'] = $message['content'];
			$data['DATE'] = date('Y-m-d H:i:s', $message['dateline']+8*3600);
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('rmessage_was_deleted', $data, $userInfo);
		}
	}
	
	
	/**
	 * @param unknown_type $params
	 */
	public function onUserAttachDeleted($params) {
		$userInfo = &$params['userInfo'];
		$filename = $params['filename'];
		$dateline = $params['dateline'];
		
		if (Better_Registry::get('sess')->admin_uid) {
			$data = $userInfo;
			$data['filename'] = $filename;
			$data['DATE'] = date('Y-m-d H:i:s', $dateline+8*3600);
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('attach_was_deleted', $data, $userInfo);
		}
	}
	
	
	public function onUserLocked($params){
		$userInfo = &$params['userInfo'];
		
		if (Better_Registry::get('sess')->admin_uid) {
			$data = $userInfo;
			
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('user_was_locked', $data, $userInfo);
		}
		
	}
	
	public function onUserUnlocked($params){
		$userInfo = &$params['userInfo'];
		
		if (Better_Registry::get('sess')->admin_uid) {
			$data = $userInfo;
			
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('user_was_unlocked', $data, $userInfo);
		}
		
	}
	
	public function onUserMuted($params){
		$userInfo = &$params['userInfo'];
		
		if (Better_Registry::get('sess')->admin_uid) {
			$data = $userInfo;
			
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('user_was_muted', $data, $userInfo);
		}
		
	}
	
	
	public function onUserUnmuted($params){
		$userInfo = &$params['userInfo'];
		
		if (Better_Registry::get('sess')->admin_uid) {
			$data = $userInfo;
			
			Better_User_DirectMessage::getInstance(BETTER_SYS_UID)->sendTpl('user_was_unmuted', $data, $userInfo);
		}
		
	}
	
}