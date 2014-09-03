<?php

/**
 * 计算Karma值变化
 * 
 * @package Better.User.Karma
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Karma_Calculator extends Better_User_Karma_Base
{
	protected static $instance = array();
	const BASE = 1000;

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
		$karma = 0;
		$userInfo = $this->user->getUserInfo();
		
		$nowKarma = $userInfo['karma'];
		$karma = $nowKarma>=0 ? -1*$nowKarma/10 : 0;

		return self::_format($karma);
	}
	
	/**
	 * 邀请了某人注册
	 * 
	 * @return integer
	 */
	public function onInviteSomebody()
	{
		$karma = 0;
		
		$userInfo = $this->user->getUserInfo();
		$nowKarma = $userInfo['karma'];
		
		$karma = $nowKarma>=0 ? (self::BASE-$nowKarma)/10 : self::BASE/10;
		
		return $karma;
	}
	
	/**
	 * 删除
	 * 
	 * @return integer
	 */
	public function onDelete()
	{
		$karma = 0;
		
		$userInfo = $this->user->getUserInfo();
		$nowKarma = $userInfo['karma'];
		
		$karma = $nowKarma>=0 ? -$nowKarma/100 : 0;
		
		return $karma;				
	}
	
	/**
	 * 新贴士
	 * 
	 * @return integer
	 */
	public function onNewTips()
	{
		return $this->_onCommon((array)'new_tips');
	}
	
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
		$karma = 0;
		
		$count = Better_DAO_KarmaLog::getInstance($this->uid)->getNativedayCount('reduce_follower')+1;
		
		if ($count<=6) {
			$userInfo = $this->user->getUserInfo();
			$nowKarma = $userInfo['karma'];
			
			$karma = $nowKarma>=0 ? -$nowKarma/100 : 0;
		}
		
		return $karma;		
	}
	
	/**
	 * 减少关注
	 * 
	 * @return integer
	 */
	public function onReduceFollowing()
	{
		$karma = 0;
		
		$count = Better_DAO_KarmaLog::getInstance($this->uid)->getNativedayCount('reduce_following')+1;
		
		if ($count<=6) {
			$userInfo = $this->user->getUserInfo();
			$nowKarma = $userInfo['karma'];

			$karma = $nowKarma>=0 ? -$nowKarma/100 : 0;
		}
		
		return $karma;				
	}
	
	/**
	 * 新粉丝
	 * 
	 * @return integer
	 */
	public function onNewFollower()
	{
		$karma = 0;
		
		$count = Better_DAO_KarmaLog::getInstance($this->uid)->getNativedayCount('new_follower')+1;
		
		if ($count<=6) {
			$userInfo = $this->user->getUserInfo();
			$nowKarma = $userInfo['karma'];
			
			$karma = $nowKarma>=0 ? (self::BASE-$nowKarma)/100 : self::BASE/100;
		}
		
		return $karma;
	}
	
	/**
	 * 取消好友关系
	 * 
	 * @return integer
	 */
	public function onUnfriendWithSomebody()
	{
		$karma = 0;
		
		$userInfo = $this->user->getUserInfo();
		$nowKarma = $userInfo['karma'];

		$karma = $nowKarma>=0 ? -3*$nowKarma/100 : -0;
		
		return $karma;
	}
	
	/**
	 * 有新好友时
	 * 
	 * @return integer
	 */
	public function onFriendWithSomeBody(array $params=array())
	{
		$karma = 0;
		$count = Better_DAO_KarmaLog::getInstance($this->uid)->getNativedayCount('friend_with_somebody')+1;
		
		$friendUid = isset($params['friend_uid']) ? (int)$params['friend_uid'] : 0;
		if ($friendUid && $this->user->friends()->hasRequest($friendUid)) {
			$karma -= $this->onFriendRequest();
		}
					
		if ($count<=2) {
			$userInfo = $this->user->getUserInfo();
			$nowKarma = $userInfo['karma'] + $karma;
			
			$karma += $nowKarma>=0 ? 3*(self::BASE-$nowKarma)/100 : 3*self::BASE/100;
		}
		
		return $karma;
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
			$count = Better_DAO_KarmaLog::getInstance($this->uid)->getNativedayCount('login');
	
			if (($this->user->isLoginActive()) && $count<1) {
				$user = Better_User::getInstance($this->uid);
				$userInfo = $user->getUserInfo();
				
				if (date('Ymd')>date('Ymd', $userInfo['regtime'])) {
					$karma = $userInfo['karma'];
					
					$lastLogin = Better_DAO_KarmaLog::getInstance($this->uid)->getLastCheckin();
					
					if ($lastLogin>0) {
						$now = time();
						$offset = (defined('BETTER_USER_TIMEZONE') ? BETTER_USER_TIMEZONE : 8)*3600;
						
						$dayStart = $now - date('H', $now+$offset)*3600 - date('i', $now+$offset)*60 - date('s', $now+$offset);
						$lastLoginStart = $lastLogin - date('H', $lastLogin+$offset)*3600 - date('i', $lastLogin+$offset)*60 - date('s', $lastLogin+$offset);
						$days = intval(($dayStart - $lastLoginStart)/(3600*24));
						
						$days>91 && $days = 91;
						$ratio = ($days-1)/5 + 2;
						
						$result = ($days>0 || ($days==0 && date('d',time()+$offset)!=date('d', $lastLogin+$offset))) ? (($karma>=0 ? (self::BASE-$karma) : (self::BASE))*$ratio)/100 : 0;
					} else {
						$days = -1;
						$result = $userInfo['lastlogin'] ? $karma>=0 ? 2*(self::BASE - $karma)/100 : 2*self::BASE/100 : 0;
					}
				}
			}
		}

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
		return Better_Karma::format($karma);
	}
}