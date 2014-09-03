<?php

/**
 * 前台session实现
 *
 * @package Better.Session
 * @author leip <leip@peptalk.cn>
 */

class Better_Session_Front extends Better_Session_Base 
{
	
	/**
	 * session初始化
	 * 根据获得的sid查询数据库，再根据获得的数据库结果分别处理guest会话和用户会话
	 *
	 * @see library/Better/Session/Better_Session_Base#init()
	 */
	public function init($force=true)
	{
		
		$this->namespace = Better_Config::getAppConfig()->session->frontNamespace;
		
		$force===true && parent::init();
		
		$this->uid = $this->get('uid');

		if ($this->uid>0 || (!defined('IN_API') && isset($_COOKIE[Better_User_AutoLogin::$cookieKey]) && Better_User_AutoLogin::autoLogin())) {
			$user = Better_User::getInstance($this->uid);
			$userInfo = $user->getUser();
			$timezone = $userInfo['timezone'];
		} else {
			$this->set('uid', '0');
			$user = Better_User::getInstance(0);
			$timezone = 8;
		}
		
		$str = '';
	    	 	  $str .=Better_Registry::get('sess')->get('step2nickname');
	    	 	  $str .="xxx".Better_Config::getAppConfig()->autoregthird->switch;
	    	 	  $str .="xxx".Better_Registry::get('sess')->get('thirdpass');
	    	 	  $str .="xxx".$_SESSION['authpass'];

	    define('BETTER_USER_TIMEZONE', $timezone);

		Better_DAO_Base::setCacheIdentifier($this->uid);
		Better_Registry::set('user', $user);

	}
	
	public function destroy()
	{
		Better_User_AutoLogin::clear();
		parent::destroy();
	}

}

?>