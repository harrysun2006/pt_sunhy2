<?php

/**
 * 日志
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Tracelog extends Better_Hook_Base
{
	public function onPoiPollSubmitted(array $params)
	{
		$data = array();
		$data['uid'] = $params['uid'];
		$data['itemid'] = $params['bid'];
		$data['action'] = 'poll_poi';
		$data['data'] = '对贴士进行了投票: '.$params['option'];;
		
		Better_Tracelog::getInstance()->tracelog($data);
	}	
	
	public function onPoiCreated(array $params)
	{
		$data = array();
		$data['uid'] = $params['uid'];
		$data['itemid'] = $params['poi_id'];
		$data['action'] = 'create_poi';
		$data['data'] = '创建新POI: '.$params['poi_info']['name'];
		
		Better_Tracelog::getInstance()->tracelog($data);
	}
	
	public function onBlogPosted(array $params)
	{
		$uid = $params['blog']['uid'];
		$type = $params['blog']['type'];		
		
		$data = array();
		$data['uid'] = $uid;
		$data['itemid'] = $params['blog']['bid'];
		$data['data'] = $params['blog']['message'];
		if($type=='normal'){
			$data['action'] = 'post_shout';
		}else if($type=='tips'){
			$data['action'] = 'post_tips';
		}else if($type=='checkin'){
			$data['action'] = 'check_in';
			$data['data'] = '签到';
		}
		
		Better_Tracelog::getInstance()->tracelog($data);
	}
	
	public function onBlogDeleted(array $params)
	{
		$uid = $params['blog']['uid'];
		$bid = $params['blog']['bid'];
		
		//记录用户操作
		$data['uid'] = $uid;
		$data['itemid'] = $bid;
		$data['action'] = 'del_blog';
		$data['data'] = $params['blog']['message']? $params['blog']['message'] : "签到";
			
		Better_Tracelog::getInstance()->tracelog($data);		
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
		$following_uid = $params['following_uid'];
		
		if ($uid!=BETTER_SYS_UID) {
			$userInfo = Better_User::getInstance($uid)->getUser();
			$followingUserInfo = Better_User::getInstance($following_uid)->getUser();
			
			$data['action'] = 'follow';
			$data['uid'] = $uid;
			$data['itemid'] = $following_uid;
			$data['data'] = '关注了 '.$followingUserInfo['nickname'];
			
			Better_Tracelog::getInstance()->tracelog($data);
		}*/
		
	}
	
	public function onBlockedSomebody(array $params)
	{
		$uid = $params['uid'];
		$blocked_uid = $params['blocked_uid'];
		
		$userInfo = Better_User::getInstance($uid)->getUser();
		$blockedUserInfo = Better_User::getInstance($blocked_uid)->getUser();
		
		$data['action'] = 'block';
		$data['uid'] = $uid;
		$data['itemid'] = $blocked_uid;
		$data['data'] = '阻止了 '.$blockedUserInfo['nickname'];

		Better_Tracelog::getInstance()->tracelog($data);		
	}
	
	public function onUserCreated(array $params)
	{
		$userInfo = &$params['userInfo'];
		
		$data['action'] = 'signup';
		$data['uid'] = $userInfo['uid'];
		$data['itemid'] = $userInfo['uid'];
		$data['data'] = '注册了开开';
		
		Better_Tracelog::getInstance()->tracelog($data);
	}
	
	public function onUserChanged(array $params)
	{
		$newUserInfo = &$params['newUserInfo'];
		$oldUserInfo = &$params['oldUserInfo'];
		$uid = $oldUserInfo['uid'];
		
		$data = array();
		$data['uid'] = $uid;
		$data['itemid'] = $uid;
		
		if (isset($newUserInfo['avatar']) && $newUserInfo['avatar']!=$oldUserInfo['avatar']) {
			$data['action'] = 'update_avatar';
			$data['data'] = '更新了头像';
			
			Better_Tracelog::getInstance()->tracelog($data);
		}
		
		$detect = array(
			'username' => '用户名',
			'nickname' => '姓名',
			'self_intro' => '自我介绍',
		);
		$message = '';
		foreach ($detect as $key=>$value) {
			if (isset($newUserInfo[$key]) && $newUserInfo[$key]!=$oldUserInfo[$key]) {
				$message .= $value.': '.$newUserInfo[$key].',';
			}
		}
		
		if ($message!='') {
			$data['action'] = 'update';
			$data['data'] = $message;
			Better_Tracelog::getInstance()->tracelog($data);
		}

	}	
	
	public function onAttachmentUploaded(array $params)
	{
		$file_id = $params['file_id'];
		$uid = $params['uid'];
		$data['action'] = 'upload';
		
		$attach =  Better_Attachment::getInstance($file_id)->parseAttachment();
		
		$data['uid'] = $uid;
		$data['itemid'] = $file_id;
		$data['data'] = '上传了附件: '.'<img src="'.$attach['tiny'].'" />';

		Better_Tracelog::getInstance()->tracelog($data);	
	}
	
	public function onFollowRequest(array $params)
	{
		$uid = $params['uid'];
		$to_follow = $params['to_follow'];
		
		$userInfo = Better_User::getInstance($uid)->getUser();
		$toFollowUserInfo = Better_User::getInstance($to_follow)->getUser();
		
		$data = array();
		$data['action'] = 'follow_request';
		$data['uid'] = $uid;
		$data['itemid'] = $to_follow;
		$data['data'] = '请求关注 '.$toFollowUserInfo['nickname'];
		
		Better_Tracelog::getInstance()->tracelog($data);
	}
	
	public function onDirectMessageSent(array $params)
	{
		$uid = $params['uid'];
		$receiverUid = $params['receiver_uid'];
		$content = $params['content'];
		$msg_id = $params['msg_id'];
		
		$userInfo = Better_User::getInstance($uid)->getUser();
		$receiverUserInfo = Better_User::getInstance($receiverUid)->getUser();
		
		$data['action'] = 'direct_message';
		$data['uid'] = $uid;
		$data['itemid'] = $msg_id;
		$data['data'] = '发送私信给 '.$receiverUserInfo['nickname'];
		
		Better_Tracelog::getInstance()->tracelog($data);
	}
	
	public function onBlogReplyPosted(array $params)
	{

		$uid = $params['uid'];
		
		$upbid = $params['blog']['upbid'];
		$blog = Better_Blog::getBlog($upbid);
		$content = $blog['blog']['message'];
		
		$data = array();
		$data['uid'] = $uid;
		$data['itemid'] = $params['blog']['bid'];
		$data['message'] = '回复微博';
		$data['data'] = '回复内容: '.$params['blog']['message'].'<hr />原文: '.$content;
		
		Better_Tracelog::getInstance()->tracelog($data,'blogReply','new',$uid);
	}	
	
	public function onAddedFavorite(array $params)
	{
		$blog = Better_Blog::getBlog($params['bid']);
	
		$data = array();
		$data['uid'] = $params['uid'];
		$data['itemid'] = $params['bid'];
		$data['action'] = 'favorite';
		$data['data'] = '收藏了：'.$blog['blog']['message'];

		Better_Tracelog::getInstance()->tracelog($data);
	}
	
	public function onDeleteFavorite(array $params)
	{
		$blog = Better_Blog::getBlog($params['bid']);
	
		$data = array();
		$data['uid'] = $params['uid'];
		$data['itemid'] = $params['bid'];
		$data['action'] = 'unfavorite';
		$data['data'] = '取消收藏了：'.$blog['blog']['message'];

		Better_Tracelog::getInstance()->tracelog($data);
	}
	
	public function onUserDeleted(array $params)
	{
		
	}	
	
	public function onUserLogin(array $params)
	{
		$data = array();
		$data['uid'] = $params['uid'];
		$data['itemid']='';
		$data['action'] = 'login';
		$data['data'] = '登陆开开';
		
		Better_Tracelog::getInstance()->tracelog($data);
	}
	
	public function onUserLogout(array $params)
	{
		$data = array();
		$data['uid'] = $params['uid'];
		$data['itemid']='';
		$data['action'] = 'logout';
		$data['data'] = '退出开开';
		
		Better_Tracelog::getInstance()->tracelog($data);
	}		
	
	public function onUnfollowSomebody(array $params)
	{
		/*$uid = $params['uid'];
		$following_uid = $params['following_uid'];
		$followingUserInfo = Better_User::getInstance($following_uid)->getUser();
		
		$data = array();
		$data['uid'] = $uid;
		$data['itemid'] = $following_uid;
		$data['action'] = 'unfollow';
		$data['data'] = '取消关注:'.$followingUserInfo['nickname'];
		
		Better_Tracelog::getInstance()->tracelog($data);*/
	}
	
	public function onUserCheckin(array $params)
	{
		
	}
	
	public function onFriendRequest(array $params)
	{
		$friend = Better_User::getInstance($params['friend_uid'])->getUser();
		$data = array();
		$data['uid'] = $params['uid'];
		$data['itemid'] = $params['friend_uid'];
		$data['action'] = 'friend_request';
		$data['data'] = '请求加'.$friend['nickname'].'为好友';
		
		Better_Tracelog::getInstance()->tracelog($data);
	}
	
	public function onFriendWithSomebody(array $params)
	{
		$friend = Better_User::getInstance($params['friend_uid'])->getUser();
		$data = array();
		$data['uid'] = $params['uid'];
		$data['itemid'] = $params['friend_uid'];
		$data['action'] = 'friend_with';
		$data['data'] = '同意加'.$friend['nickname'].'为好友';
		
		Better_Tracelog::getInstance()->tracelog($data);
	}	
	
	public function onUnblockSomebody(array $params)
	{
		$uid = $params['uid'];
		$unblocked_uid = $params['unblocked_uid'];
		
		$userInfo = Better_User::getInstance($uid)->getUser();
		$unblockedUserInfo = Better_User::getInstance($unblocked_uid)->getUser();
		
		$data['action'] = 'unblock';
		$data['uid'] = $uid;
		$data['itemid'] = $unblocked_uid;
		$data['data'] = '取消阻止了 '.$unblockedUserInfo['nickname'];

		Better_Tracelog::getInstance()->tracelog($data);	
	}
	
	public function onUnfriendWithSomebody(array $params)
	{
		$friend = Better_User::getInstance($params['friend_uid'])->getUser();
		$data = array();
		$data['uid'] = $params['uid'];
		$data['itemid'] = $params['friend_uid'];
		$data['action'] = 'unfriend_with';
		$data['data'] = '删除好友:'.$friend['nickname'];
		
		Better_Tracelog::getInstance()->tracelog($data);
			
	}
	
	public function onRejectFriendRequest(array $params)
	{
		$friend = Better_User::getInstance($params['request_uid'])->getUser();
		$data = array();
		$data['uid'] = $params['uid'];
		$data['itemid'] = $params['request_uid'];
		$data['action'] = 'reject_friend';
		$data['data'] = '拒绝加'.$friend['nickname'].'为好友';
		
		Better_Tracelog::getInstance()->tracelog($data);
	}
	
	
}