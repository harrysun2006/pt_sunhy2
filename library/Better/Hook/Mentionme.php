<?php

/**
 * 提到我的
 * 
 * @author yangl
 * @package Better.Hook
 * 
 */

class Better_Hook_Mentionme extends Better_Hook_Base
{

	public function onBindcell(array $params)
	{
	}	
	
	public function onEmailBinded(array $params)
	{
	}
	
	public function onPoiPollSubmitted(array $params)
	{
		
	}
		
	public function onPoiCreated(array $params)
	{
		
	}
	
	public function onBlogPosted(array $params)
	{
		if(Better_Hook::$hookResults['BlogPosted'] != Better_Hook::$RESULT_BLOG_NEED_CHECK){
			$blog = $params['blog'];
			
			if($blog['upbid']){
				$upbid = $blog['upbid'];
				list($uid, $i) = explode('.', $upbid);
				if($uid!= $params['uid']){
					Better_DAO_Mentionme::getInstance($uid)->insert(array(
						'bid'=> $blog['bid'],
						'comment_id'=> 0,
						'uid' => $uid,
						'type' => 'rtme',
						'dateline'=> $blog['dateline']
					));
				}
			}
			
			$upuid = $uid;
			
			$atUids = Better_Blog::apiParseBlogAt($blog['message']);
			if($atUids && count($atUids)>0){
				foreach($atUids as $uid){
					if($uid!=$upuid && $uid!= $params['uid']){
						Better_DAO_Mentionme::getInstance($uid)->insert(array(
							'bid'=> $blog['bid'],
							'comment_id'=> 0,
							'uid' => $uid,
							'type' => '@me',
							'dateline'=> $blog['dateline']
						));
					}
				}
			}
		}
	}
	
	public function onBlogDeleted(array $params)
	{
		$blog = $params['blog'];
		$bid = $blog['bid'];
		Better_DAO_Mentionme::getInstance()->deleteByBid($bid);
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
	
	
	public function onReplyPosted(array $params){
		if(Better_Hook::$hookResults['ReplyPosted'] != Better_Hook::$RESULT_BLOG_NEED_CHECK){
			$blog_uid = $params['blog_uid'];
			$data = $params['data'];
			$comment_id = $params['id'];
			$comment_data = $params['comment_data'];
			$sessUid = $params['uid'];
			
			if($blog_uid != $sessUid){
				if($blog_uid && $comment_id){
					Better_DAO_Mentionme::getInstance($blog_uid)->insert(array(
						'bid'=> $data['bid'],
						'comment_id'=> $comment_id,
						'uid' => $blog_uid,
						'type' => 'commentme',
						'dateline'=> $comment_data['dateline']
					));
				}
			}
			$atUids = Better_Blog::apiParseBlogAt($comment_data['message']);
			if($atUids && count($atUids)>0){
				foreach($atUids as $uid){
					if($uid!=$blog_uid && $uid!=$sessUid){
						Better_DAO_Mentionme::getInstance($uid)->insert(array(
							'bid'=> $data['bid'],
							'comment_id'=> $comment_id,
							'uid' => $uid,
							'type' => '@me',
							'dateline'=> $comment_data['dateline']
						));
					}
				}
			}
		}
	}
	
	
	public function OnReplyDeleted(array $params){
		$id = $params['id'];
		$bid = $params['bid'];
		
		Better_DAO_Mentionme::getInstance()->deleteByRid($id, $bid);
	}
	
	
}