<?php

/**
 * 自动登录
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_AutoLogin extends Better_User_Base
{
	protected static $instance = array();
	public static $cookieKey = 'kai';

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}		
	
	/**
	 * 写入自动登录的Cookie
	 * 
	 * @return null
	 */
	public function putCookie()
	{
		$userInfo = $this->getUserInfo();
		
		if ($userInfo['uid']) {
			$hash = base64_encode($userInfo['uid'].'|'.md5($userInfo['password'].md5($userInfo['salt'])));
		} else {
			$hash = '';
		}
		
		header('P3P: CP=CAO PSA OUR');
		setcookie(self::$cookieKey, $hash, time()+Better_Session_Base::$stickTime, '/');
	}
	

	
	/**
	 * 自动登录
	 * 
	 * @param $hash
	 * @return bool
	 */
	public static function autoLogin()
	{
		$logined = false;

		if (isset($_COOKIE[self::$cookieKey]) && $_COOKIE[self::$cookieKey]!='') {
			list($uid, $pwdHash) = explode('|', base64_decode($_COOKIE[self::$cookieKey]));
			$loginbycellno = (isset($_COOKIE['loginbycellno']) && $_COOKIE['loginbycellno']!='')? $_COOKIE['loginbycellno'] : 0;
			if ($uid) {
				$userInfo = Better_User::getInstance($uid)->getUser();
				if ($pwdHash==md5($userInfo['password'].md5($userInfo['salt']))) {
					Better_Registry::get('sess')->set('uid', $uid);
					Better_Registry::get('sess')->uid = $uid;
					$logined = true;				
					Better_Hook::factory(array(
						'Karma', 'Badge', 'User','Rp'
					))->invoke('UserLogin', array(
						'uid' => $uid,
						'loginbycellno' => $loginbycellno,
						'autologin' => 1,
					));
				}
			}
		}

		return $logined;		
	}
	
	/**
	 * 清除自动登录cookie
	 * 
	 * @return
	 */
	public static function clear()
	{
		setcookie(self::$cookieKey, '', time()-Better_Session_Base::$stickTime, '/');
		unset($_COOKIE[self::$cookieKey]);
	}
}