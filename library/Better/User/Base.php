<?php

/**
 * 用户附属功能基类
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Base
{
	protected $uid = 0;
	protected $userInfo = array();
	protected $user = null;
	
	protected  function __construct($uid)
	{
		$this->uid = $uid;
		$this->user = Better_User::getInstance($uid);
	}

	public function getUserInfo()
	{
		$this->userInfo = $this->user->getUser();
		return $this->userInfo;
	}
}