<?php

/**
 * 用户Karma基类
 * 
 * @package Better.User.Karma
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Karma_Base
{
	protected $uid = 0;
	protected $user = null;
	
	protected function __construct($uid)
	{
		$this->uid = (int)$uid;
		$this->user = Better_User::getInstance($this->uid);
	}
}