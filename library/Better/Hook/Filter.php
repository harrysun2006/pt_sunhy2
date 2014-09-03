<?php

/**
 * 字符过滤
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 * 
 */

class Better_Hook_Filter extends Better_Hook_Base
{
	public function onPoiPollSubmitted(array $params)
	{
		
	}
		
	public function onPoiCreated(array $params)
	{
		
	}	
	
	public function onBlogPosted(array $params)
	{
		$bid = $params['bid'];
		$data = &$params['data'];
		$blog = &$params['blog'];
		$passbyFilter = (isset($data['passby_filter']) && $data['passby_filter']) ? true : false;
		$config = Better_Config::getAppConfig();
					
		if ($config->filter->enable && !$passbyFilter) {
			$userInfo = Better_User::getInstance($blog['uid'])->getUser();
			$username = $userInfo['username'];
			$message = $blog['message'];
			
			$result = Better_Filter::getInstance()->filter($blog['message'], 'blog', $blog['uid'], $username, $bid, $userInfo);
			
			if ($config->blog->check_all || ($config->blog->check_suspicious && in_array(3, $result))) {
				Better_Hook::$hookResults['BlogPosted'] = Better_Hook::$RESULT_BLOG_NEED_CHECK;
				defined('BETTER_LAST_BLOG_WAS_FILTED') || define('BETTER_LAST_BLOG_WAS_FILTED', true);
				Better_Blog::setNeedCheck($bid);
			}
			
			if(in_array(2, $result)){
				$message = Better_Filter::make_semiangle($message);
				$filter_words1 = file(Better_Config::getAppConfig()->filter->words2.'-1.txt');
				$filter_words2 = file(Better_Config::getAppConfig()->filter->words2.'-2.txt');
				$filter_words = array_merge($filter_words1, $filter_words2);
				foreach($filter_words as $word){
					$word = trim($word);
					if($word){
						$message = str_ireplace($word, '***', $message);		
					}
				}
				Better_Registry::set('blog_last_filter_message', $message);
							
				Better_DAO_Blog::getInstance($blog['uid'])->updateByCond(array(
					'message' => $message
					), array('bid' => $bid));
				Better_Registry::set('blog_last_filter_message', $message);
			}
		
			if (!$passbyFilter && $blog['attach']) {
				$data_array = array();
		       	$data_array['uid'] = $blog['uid'];
				$data_array['username'] = $username;
		       	$data_array['refid'] = $bid;
		       	$data_array['type'] = 'attach';
		       	$data_array['imgurl'] = $blog['attach'];
		       	$data_array['changetime'] = $blog['dateline'];
		       	
		       	Better_DAO_Newimg::getInstance()->insert($data_array);
			}
		}
	}
	
	public function onBlogDeleted(array $params)
	{
		$bid = $params['blog']['bid'];
		Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>0), array('bid'=>$bid));
		if ($params['blog']['attach']) {
			Better_DAO_Newimg::getInstance()->deleteByCond(array(
													'refid' =>$bid,
													'type' => 'attach'));
		}
	}
	
	public function onUserCreated(array $params)
	{
		if (Better_Config::getAppConfig()->filter->enable) {
			$userInfo = &$params['userInfo'];
			$uid = $userInfo['uid'];
			
			Better_Filter::getInstance()->filter(self::genUserWords($userInfo), 'userinfo', $uid, $userInfo['username'], $uid);
		}
	}
	
	public function onUserChanged(array $params)
	{
		if (Better_Config::getAppConfig()->filter->enable) {
			$oldUserInfo = &$params['oldUserInfo'];
			$newUserInfo = &$params['newUserInfo'];
			$uid = $oldUserInfo['uid'];
	
			$result = Better_Filter::getInstance()->filter(self::genUserWords(array_merge($oldUserInfo, $newUserInfo)), 'userinfo', $uid, $oldUserInfo['username'], $uid);
			
			Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>0), array('uid'=>$uid, 'type'=>'userinfo', 'flag'=>1));
			if(is_array($result) && count($result)>0){
				$id = Better_Filter::getInstance()->getLastId();
				Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>1), $id);					
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
	
	public function onAttachmentUploaded(array $params)
	{
	}
	
	public function onFollowRequest(array $params)
	{
	}
	
	public function onDirectMessageSent(array $params)
	{
		if (Better_Config::getAppConfig()->filter->enable) {
			$uid = $params['uid'];
			$receiverUid = $params['receiver_uid'];
			$content = $params['content'];
			$msg_id = $receiverUid.'.'.$params['msg_id'];
			
			if($uid!=10000){
				$userInfo = Better_User::getInstance($uid)->getUser();
				$receiverUserInfo = Better_User::getInstance($receiverUid)->getUser();
		
				$result = Better_Filter::getInstance()->filter($content, 'direct_message', $uid, $userInfo['username'], $msg_id);				
			
				if (in_array(3, $result)) {					
					$filter_words1 = file(Better_Config::getAppConfig()->filter->words3.'-1.txt');
					$filter_words2 = file(Better_Config::getAppConfig()->filter->words3.'-2.txt');
					$filter_words = array_merge($filter_words1, $filter_words2);
					foreach($filter_words as $word){
						$word = trim($word);
						if($word){
							$content = str_ireplace($word, '***', $content);		
						}
					}
				}
				
				
				
				
				//替换
				if(in_array(2, $result)){
					$filter_words1 = file(Better_Config::getAppConfig()->filter->words2.'-1.txt');
					$filter_words2 = file(Better_Config::getAppConfig()->filter->words2.'-2.txt');
					$filter_words = array_merge($filter_words1, $filter_words2);
					foreach($filter_words as $word){
						$word = trim($word);
						if($word){
							$content = str_ireplace($word, '***', $content);		
						}
					}	
				}	
						
				Better_DAO_DmessageReceive::getInstance($receiverUid)->updateByCond(array(
					'content' => $content
					), array('msg_id' => $params['msg_id']));
				Better_DAO_DmessageSend::getInstance($uid)->updateByCond(array(
					'content' => $content
					), array('msg_id' => $params['msg_id']));				
								
			}
		}
	}
	
	public function onBlogReplyPosted(array $params)
	{
		/*$blog = &$params['blog'];
		$userInfo = Better_User::getInstance($blog['uid'])->getUser();
		
		Better_Filter::getInstance()->filter($blog['message'], 'blog_reply', $blog['uid'], $userInfo['username'], $blog['bid']);*/
	}	
	
	public function onAddedFavorite(array $params)
	{
	}	
	
	public function onUserDeleted(array $params)
	{	
	}	
	
	public static function genUserWords(array $userInfo)
	{
		$word = '';
		
		$word .= '用户名:'.$userInfo['username'].'<br>';
		$word .= 'NAME:'.$userInfo['nickname'].'<br>';
		$word .= '自我介绍:'.$userInfo['self_intro'];
		
		return $word;
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
	
	
	
	/**
	 * 发表回复hook
	 * @param array $params
	 */
	public function onReplyPosted(array $params)
	{
		$id = $params['id'];
		$data = &$params['data'];
		$uid = &$params['uid'];
		$blog_uid = &$params['blog_uid'];
		$message = &$params['message'];
		$passbyFilter = (isset($data['passby_filter']) && $data['passby_filter']) ? true : false;
		$config = Better_Config::getAppConfig();
					
		if ($config->filter->enable && !$passbyFilter) {
			$userInfo = Better_User::getInstance($uid)->getUser();
			$username = $userInfo['username'];
			
			$result = Better_Filter::getInstance()->filter($message, 'reply', $uid, $username, $data['bid'].'-'.$id);
			
			if ($config->blog->check_all || ($config->blog->check_suspicious && in_array(3, $result))) {
				Better_Hook::$hookResults['ReplyPosted'] = Better_Hook::$RESULT_BLOG_NEED_CHECK;
				//defined('BETTER_LAST_BLOG_WAS_FILTED') || define('BETTER_LAST_BLOG_WAS_FILTED', true);
				Better_DAO_Blogreply::setNeedCheck($id, $blog_uid);
			}
			
			if(in_array(2, $result)){
				$message = Better_Filter::make_semiangle($message);
				$filter_words1 = file(Better_Config::getAppConfig()->filter->words2.'-1.txt');
				$filter_words2 = file(Better_Config::getAppConfig()->filter->words2.'-2.txt');
				$filter_words = array_merge($filter_words1, $filter_words2);
				foreach($filter_words as $word){
					$word = trim($word);
					if($word){
						$message = str_ireplace($word, '***', $message);		
					}
				}
				Better_Registry::set('reply_last_filter_message', $message);
							
				Better_DAO_Blogreply::getInstance($blog_uid)->update(array(
					'message' => $message
					), $id);
				Better_Registry::set('reply_last_filter_message', $message);
			}
			
		}
	}
	
	
	/**
	 * 删除评论
	 */
	public function onReplyDeleted(array $params){
		$id = $params['id'];
		$bid = $params['bid'];
		Better_DAO_Admin_Filter::getInstance()->update(array('flag'=>0), array('bid'=>$bid.'-'.$id, 'type'=>'reply'));
	}
}