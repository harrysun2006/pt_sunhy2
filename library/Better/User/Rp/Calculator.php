<?php

/**
 * 计算RP值变化
 * 
 * @package Better.User.Karma
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Rp_Calculator extends Better_User_Rp_Base
{
	protected static $instance = array();
	const BASE = 10000;

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}
	
	/**
	 * 没有登录
	 * 
	 * @return integer
	 */
	public function onNotLogin()
	{
		
	}
	
	/**
	 * 邀请了某人注册
	 * 
	 * @return integer
	 */
	public function onInviteSomebody()
	{
		$rp = 0;
		$rp = Better_Config::getAppConfig()->rp->invitation;
		return $rp;
	}
	
	/**
	 * 删除
	 * 
	 * @return integer
	 */
	public function onDelete()
	{
		$rp = 0;
		$rp = Better_Config::getAppConfig()->rp->delblog;
		return $rp;		
	}
	
	/**
	 * 新贴士
	 * 
	 * @return integer
	 */
	
	
	/**
	 * 新围脖
	 * 
	 * @return integer
	 */
	public function onNewBlog()
	{
		return $this->_onCommon((array)'new_blog');
	}

	/**
	 * 签到
	 * 
	 * @return integer
	 */
	public function onCheckin()
	{
		return $this->_onCommon((array)'checkin');
	}
	
	
	
	/**
	 * 通用
	 * 
	 * @return integer
	 */
	
	private function _onCommon(array $types=array())
	{
		$karma = 0;
		$types = array('checkin', 'new_blog', 'new_tips');
		
		$count = Better_DAO_KarmaLog::getInstance($this->uid)->getNativedayCount($types)+1;

		if ($count<=6) {
			$userInfo = $this->user->getUserInfo();
			$nowKarma = $userInfo['karma'];
			
			$karma = $nowKarma>=0 ? (self::BASE-$nowKarma)/100 : 0;
		}
		
		return $karma;
	}
	
	/**
	 * 减少粉丝
	 * 
	 * @return integer
	 */
	public function onReduceFollower()
	{
			
	}
	
	/**
	 * 减少关注
	 * 
	 * @return integer
	 */
	public function onReduceFollowing()
	{
		
	}
	
	/**
	 * 新粉丝
	 * 
	 * @return integer
	 */
	public function onNewFollower()
	{
		$rp = 0;
		$count = Better_DAO_RpLog::getInstance($this->uid)->getNativedayCount('newfollower')+1;
		
		if ($count<=Better_Config::getAppConfig()->rp->newfans) {
			$rp = Better_Config::getAppConfig()->rp->newfan;
		}
		
		return $rp;
	}
	
	/**
	 * 取消好友关系
	 * 
	 * @return integer
	 */
	public function onUnfriendWithSomebody()
	{
		
	}
	
	/**
	 * 有新好友时
	 * 
	 * @return integer
	 */
	public function onFriendWithSomeBody(array $params=array())
	{
		$rp = 0;
		$count = Better_DAO_RpLog::getInstance($this->uid)->getNativedayCount('friendwithsomebody')+1;	
		Better_Log::getInstance()->logInfo($count,'rplog');	
		if ($count<=Better_Config::getAppConfig()->rp->newfriends) {
			$userInfo = $this->user->getUserInfo();
			$rp = Better_Config::getAppConfig()->rp->newfriend;			
		}		
		return $rp;
	}
	
	/**
	 * 登录时Karma变化
	 * 
	 * @return integer
	 */
	public function onLogin()
	{
		$result = 0;
		
		if (defined('IN_API') && !defined('BETTER_API_AUTH')) {
			$result = 0;
			
		} else {
			$count = Better_DAO_RpLog::getInstance($this->uid)->getNativedayCount(array('login','login_byapi','login_bycell','login_bycell_byapi'));		
			$text = $count;	
			if ($count<Better_Config::getAppConfig()->rp->logintimes) {
				$user = Better_User::getInstance($this->uid);
				$userInfo = $user->getUserInfo();				
				
				$result = Better_Config::getAppConfig()->rp->loginaction;
				if(defined('IN_API')){
					$result = $result+Better_Config::getAppConfig()->rp->login_inapi;
				}
				$text .=" had done";
			}
		}
		Better_Log::getInstance()->logInfo($text,'rp_login');
		return $result;
	}
	
	public function onLoginbycell()
	{
		$result = 0;
		
		if (defined('IN_API') && !defined('BETTER_API_AUTH')) {
			$result = 0;
		} else {
			$count = Better_DAO_RpLog::getInstance($this->uid)->getNativedayCount(array('login','login_byapi','login_bycell','login_bycell_byapi'));	
			$text = $count;	
			if ($count<Better_Config::getAppConfig()->rp->logintimes) {
				$user = Better_User::getInstance($this->uid);
				$userInfo = $user->getUserInfo();	
			
				$result = Better_Config::getAppConfig()->rp->loginaction+Better_Config::getAppConfig()->rp->loginbycell;
				if(defined('IN_API')){
					$result = $result+Better_Config::getAppConfig()->rp->login_inapi;
				}
				$text .=" had done";
			}
		}
		Better_Log::getInstance()->logInfo($text,'rp_login');
		return $result;
	}
	
	/**
	 * 发起好友请求
	 * 
	 * @return integer
	 */
	public function onFriendRequest()
	{
		return -10;
	}
	
	/**
	 * 计算加好友被拒绝时的rp值变化
	 * 
	 * @param $count		被拒绝的次数
	 * @return integer
	 */
	public function onFriendRequestRefused()
	{
		$count = Better_DAO_KarmaLog::getInstance($this->uid)->getOnedayFriendRequestRefuseCount()+1;
		
		$result = -10*(2+$count*($count-1)/2);
		
		return $result;
	}
	
	/**
	 * 计算加好友请求时阻止某人
	 * 
	 * @return integer
	 */
	public function onBlockedFromFriendRequest()
	{
		$count = Better_DAO_KarmaLog::getInstance($this->uid)->getOnedayCount('blocked_from_friend_request')+1;

		switch ($count) {
			case 1:
				$reduce = 50;
				break;
			case 2:
				$reduce = 100;
				break;
			case 3:
				$reduce = 150;
				break;
			case 4:
				$reduce = 200;
				break;
			case 5:
				$reduce = 300;
				break;
			case 6:
				$reduce = 400;
				break;
			case 7:
				$reduce = 500;
				break;
			default:
				$reduce = 0;
				break;
		}

		return -$reduce;
	}
	
	/**
	 * 被某人阻止后
	 * 
	 * 
	 */
	public function onBlockedBySomebody()
	{
		$reduce = 0;
		return -$reduce;
	}
	
	/**
	 * 格式化Karma
	 * 
	 * @param unknown_type $karma
	 */
	protected static function _format($karma)
	{
		return Better_Rp::format($karma);
	}
	
	
	public function onPrivate_Checkin()
	{
		$rp = 0;
		$rp = Better_Config::getAppConfig()->rp->privcheckin;
		return $rp;
	}
	public function onCommon_Checkin()
	{
		$rp = 0;
		$rp = Better_Config::getAppConfig()->rp->commoncheckin;
		return $rp;
	}
	public function onSync_Checkin()
	{
		$rp = 0;
		$rp = Better_Config::getAppConfig()->rp->checkinsync;
		return $rp;
	}
	public function onPrivate_Blog()
	{
		$rp = 0;
		$rp = Better_Config::getAppConfig()->rp->privblog;
		return $rp;
	}
	public function onCommon_Blog()
	{
		$rp = 0;
		$rp = Better_Config::getAppConfig()->rp->commonblog;
		return $rp;
	}
	public function onSync_Blog()
	{
		$rp = 0;
		$rp = Better_Config::getAppConfig()->rp->blogsync;
		return $rp;
	}
	public function onNewTips()
	{
		$rp = 0;
		$rp = Better_Config::getAppConfig()->rp->posttips;
		return $rp;
	}
	
	public function onTobeMajor()
	{
		$rp = 0;
		$rp = Better_Config::getAppConfig()->rp->majorship;
		return $rp;
	}
	
	
}