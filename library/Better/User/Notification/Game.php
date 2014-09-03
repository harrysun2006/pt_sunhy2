<?php
/**
 * 游戏通知
 * 
 * @package Better.User.Notification
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_User_Notification_Game extends Better_User_Notification_Base
{
	protected static $instance = array();
	protected $type = 'game';

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
}