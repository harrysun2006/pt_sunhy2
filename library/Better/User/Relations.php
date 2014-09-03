<?php

/**
 * 用户关系相关操作
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Relations extends Better_User_Base
{
	protected static $instance = array();

	public $blocked = array();
	public $blockedby = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
}