<?php

/**
 * 好友请求通知
 * 
 * @package Better.User.Notification
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Notification_FriendRequest extends Better_User_Notification_Base
{
	protected static $instance = array();
	protected $type = 'friend_request';

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	

}