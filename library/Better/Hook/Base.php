<?php

/**
 * 钩子抽象类
 * 预定义一系列事件，将事件的处理逻辑放在特定的方法体
 * 起初这个类定义了一些抽象方法让子类必须实现，但后来由于时间越来越多，且有些事件并不具有普遍性，所以后来某些事件就没有在基类声明抽象方法了
 * 
 * @author leip <leip@peptalk.cn>
 * @package Better.Hook
 */
abstract class Better_Hook_Base
{
	public function __get($key)
	{
		$return = '';
		
		if (isset(Better_Hook::$tmp[$key])) {
			$return = Better_Hook::$tmp[$key];
		}
		
		return $return;
	}
	
	public function __set($key, $val)
	{
		Better_Hook::$tmp[$key] = $val;
	}
	
	public function __call($method, $args)
	{
		Better_Log::getInstance()->logAlert('Call to undefined hook method : ['.get_class($this).'::'.$method.']', 'hook');
	}
	
	/**
	 * 新的同步站点
	 * 
	 * @param array $params
	 */
	public function onNewSyncSites(array $params)
	{
		
	}

	/**
	 * 消息提交以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onBlogPosted(array $params);
	
	/**
	 * 消息删除以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onBlogDeleted(array $params);
	
	/**
	 * 关注某人以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onFollowSomebody(array $params);

	/**
	 * 阻止某人以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onBlockedSomebody(array $params);
	
	/**
	 * 取消阻止某人后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onUnblockSomebody(array $params);
	
	/**
	 * 用户注册以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onUserCreated(array $params);
	
	/**
	 * 用户资料变化以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onUserChanged(array $params);
	
	/**
	 * 发起Qbs查询前的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onBeforeQbsQuery(array $params);
	
	/**
	 * Qbs查询结束后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onAfterQbsQuery(array $params);
	
	/**
	 * 文件上传以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onAttachmentUploaded(array $params);
	
	/**
	 * 加关注请求的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onFollowRequest(array $params);
	
	/**
	 * 发送私信以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onDirectMessageSent(array $params);
	
	/**
	 * 回复某个消息后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onBlogReplyPosted(array $params);
	
	/**
	 * 添加收藏以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onAddedFavorite(array $params);
	
	/**
	 * 用户删除以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onUserDeleted(array $params);
	
	/**
	 * 用户登录后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onUserLogin(array $params);
	
	/**
	 * 用户注销以后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onUserLogout(array $params);
	
	/**
	 * 取消关注某人后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onUnfollowSomebody(array $params);
	
	/**
	 * 用户Checkin后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onUserCheckin(array $params);
	
	/**
	 * 加好友请求后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onFriendRequest(array $params);
	
	/**
	 * 成为好友以后的事件
	 * 
	 * @param $params;
	 * @return unknown_type
	 */
	abstract public function onFriendWithSomebody(array $params);
	
	/**
	 * 和某人取消好友关系的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onUnfriendWithSomebody(array $params);
	
	/**
	 * 拒绝好友请求后的事件
	 * 
	 * @param $params
	 * @return unknown_type
	 */
	abstract public function onRejectFriendRequest(array $params);
	
	/**
	 * POI新建以后的事件
	 */
	abstract public function onPoiCreated(array $params);
	
	/**
	 * POI投票以后的事件
	 * @param array $params
	 */
	abstract public function onPoiPollSubmitted(array $params);
	
	/**
	 * 暂时先写成空实现，不申明为abstract方法，避免改动一大堆类
	 * 用户被删除头像后的事件
	 */
	public function onUserAvatarDeleted($params){}
	
	
	/**
	 * 用户被重置位置后的事件
	 */
	public function onResetUserPlace($params){}
	
	
	/**
	 * 用户被重置用户名后的事件
	 */
	public function onResetUserName($params){}
	

	/**
	 * 用户被重置姓名后的事件
	 */
	public function onResetNickName($params){}
	
	/**
	 * 用户被重置自我介绍后的事件
	 */
	public function onResetUserSelfintro($params){}
	
	//用户被删私信
	public function onSmessageDeleted($params){}
	
	public function onRmessageDeleted($params){}
	
	//用户被删图片
	public function onUserAttachDeleted($params){}
	
	public function onUserLocked($params){}
	
	public function onUserUnlocked($params){}
	
	public function onUserMuted($params){}
	
	public function onUserUnmuted($params){}
	
	public function onReplyPosted($params){}
	
	public function onInviteTodoSent($params){}
	
}
