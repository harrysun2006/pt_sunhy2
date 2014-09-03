<?php

/**
 * 生成推送到其他微博客的对象
 *
 * @package Better.Service
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Service_PushToOtherSites
{
	/**
	 * 目前支持的协议（网站）
	 *
	 * @var array
	 */
	public static $supportedProtocols = array(
		'4sq.com', 
		'zuosa.com',
		'renren.com',
		'digu.com',
		'fanfou.com',
		'twitter.com',
		'51.com',
		'9911.com',
		'follow5.com',
		'kaixin001.com',
		'kaixin.com',
		'sina.com',
		'douban.com',
		'tongxue.com',
		'plurk.com',
		'sohu.com',
		'facebook.com',
		'139.com',
		'msn.com',
		'163.com',
		'bedo.cn',
		'qq.com',
		'qqsns.com',
		);
    public static $openProtocols = array('sina.com','renren.com','kaixin001.com','douban.com','4sq.com','twitter.com','sohu.com','9911.com','digu.com','zuosa.com','msn.com');
   
     public static $openingProtocols = array('sina.com','qq.com','msn.com','renren.com','kaixin001.com','douban.com','facebook.com','twitter.com','4sq.com','sohu.com','163.com','139.com','zuosa.com','follow5.com');
    public static $shortProtocols = array('新浪','腾讯','MSN','人人','开心','豆瓣','脸谱','推特','四方','搜狐','网易','说客','做啥','F5');
	/**
	 * 输出具体的对象实例
	 *
	 * @param string $protocol
	 * @param string $username
	 * @param string $password
	 * @return object
	 */
	public static function factory($protocol, $username, $password, $oauth_token='', $oauth_token_secret='')
	{
		$protocol = strtolower($protocol);
		if (in_array($protocol, self::$supportedProtocols)) {
			
			$protocolClass = 'Better_Service_PushToOtherSites_Sites_'.ucfirst(str_replace('.', '', $protocol));
			if (class_exists($protocolClass)) {				
				if ($oauth_token || $oauth_token_secret) {
					return new $protocolClass($username, $password, $oauth_token, $oauth_token_secret);	
				} else {
					return new $protocolClass($username, $password);
				}
				
			} else {
				
				throw new Zend_Exception('Protocol <b>'.$protocol.'</b> not supported yet');
			}
		} else {
			throw new Zend_Exception('Protocol <b>'.$protocol.'</b> not supported yet');
		}
	}
	
	/**
	 * 根据不同协议生成抄送的文字
	 * 
	 * @return string
	 */
	public static function format(array $row, array $userInfo, array $poiInfo)
	{
		if ($row['content']) {
			$isbedo = $row['protocol'] == 'bedo.cn' ? 'BBEEDDOO' : ' ';
			
			if ( preg_match('/掌门/', $row['content']) || preg_match('/mayor/', $row['content'])  ) {
				$link = $isbedo . BETTER_BASE_URL . '/poi/' . $poiInfo['poi_id'];
			} else {
				$link = $isbedo . BETTER_BASE_URL . '/' . $userInfo['username'];
			}
			
			$link = '';
			
			$text = $row['content'];
			
			switch ($row['protocol']) {
				case 'bedo.cn':				
					$len = 1000;				
					break;				
				case '9911.com':
					$len = self::__getLength($link);				
					break;
				case 'msn.com':
					$len = 128 - mb_strlen($link, 'UTF-8');
					break;
				case 'facebook.com':
					$len = 420 - mb_strlen($link, 'UTF-8'); 					
				default:
					$len = 140 - mb_strlen($link, 'UTF-8');
					break;
			}			
			$text = mb_substr($text, 0, $len, 'UTF-8');				
			$result = $text . $link;

		} else {
			$result = $row['message'];
			
			switch ($row['type']) {
				case 'tips':
					$result = self::_formatTips($row, $userInfo, $poiInfo);
					break;
				case 'checkin':
					$result = self::_formatCheckin($row, $userInfo, $poiInfo);
					break;
				case 'todo':
					$result = self::_formatTodo($row, $userInfo, $poiInfo);
					break;
				case 'normal':
				default:
					$result = self::_formatShout($row, $userInfo, $poiInfo);
					break;
			}
		}
		
		return $result;
	}

	/*
	 * 贴士的格式
	 */
	protected static function _formatTips(array $row, array $userInfo, array $poiInfo)
	{
		$result = '';
		$message = trim($row['message']);
		$text = '我在 ' . $poiInfo['name'] . '：' . $message;
		
		if ($row['protocol'] == 'bedo.cn') {
			$poi_link = 'BBEEDDOO' . BETTER_BASE_URL.'/poi/'.$poiInfo['poi_id'];
		} elseif ( in_array($row['protocol'], array('kaixin001.com', 'renren.com')) ) {
			$text = $message;
			$poi_link = '';
		} else {
			$poi_link = ' ' . BETTER_BASE_URL . '/poi/' . $poiInfo['poi_id'];
		}
		
		$poi_link = self::__addBid($poi_link, $row['bid'], $row['protocol']);
		
		switch ($row['protocol']) {
			case 'bedo.cn':				
				$len = 1000;				
				break;			
			case '9911.com':				
				$len = self::__getLength($poi_link);				
				break;	
			case 'sina.com':
				$cnt = floor(substr_count($text, ' ') / 2);				
				$len = 140 - mb_strlen($poi_link, 'UTF-8') / 2 + $cnt; //新浪真的很挫 				
				break;					
			default:
				$len = 140 - mb_strlen($poi_link, 'UTF-8');
				break;
		}
		$text = mb_substr($text, 0, $len, 'UTF-8');	
	
		$result = $text . $poi_link;
		return $result;
	}	
	
	/*
	 * 签到的格式
	 */
	protected static function _formatCheckin(array $row, array $userInfo, array $poiInfo)
	{
		$message = trim($row['message'])!='' ? ' : '.trim($row['message']) : '';
		$result = '';
	
		$lang = Better_Language::loadIt($userInfo['language'] ? $userInfo['language'] : 'zh-cn');
		$imAt = $lang->global->imat;
		$imAt = $imAt ? $imAt : '我在';
		
		$text = $imAt . ' ' . $poiInfo['city'] . ' '.$poiInfo['name'] . $message;
		
		if ($row['protocol'] == 'bedo.cn') {
			$poi_link = 'BBEEDDOO' . BETTER_BASE_URL.'/poi/'.$poiInfo['poi_id'];
		} elseif ( in_array($row['protocol'], array('kaixin001.com', 'renren.com')) ) {
			$text = trim($row['message']);
			$poi_link = '';
		} else {
			$poi_link = ' ' . BETTER_BASE_URL.'/poi/'.$poiInfo['poi_id'];
		}
		
		$poi_link = self::__addBid($poi_link, $row['bid'], $row['protocol']);
		
		switch ($row['protocol']) {
			case 'bedo.cn':				
				$len = 1000;				
				break;			
			case '9911.com':				
				$len = self::__getLength($poi_link);				
				break;	
			case 'sina.com':
				$cnt = floor(substr_count($text, ' ') / 2);				
				$len = 140 - mb_strlen($poi_link, 'UTF-8') / 2 + $cnt; //新浪真的很挫 				
				break;	
			default:
				$len = 140 - mb_strlen($poi_link, 'UTF-8');
				break;
		}
		
		$text = mb_substr($text, 0, $len, 'UTF-8');	
	
		$result = $text . $poi_link;
		return $result;
	}
	
	protected static function _formatShout(array $row, array $userInfo, array $poiInfo)
	{
		$result = $row['message'];
		
		if ($row['protocol'] == 'bedo.cn') {
			$my_link = 'BBEEDDOO' . BETTER_BASE_URL . '/' . $userInfo['username'];
		} elseif( in_array($row['protocol'], array('kaixin001.com','renren.com')) ) {
			$my_link = '';
		} else {
			$my_link = ' ' . BETTER_BASE_URL . '/' . $userInfo['username'];
		}
		
		$my_link = '';
		$message = $row['message'];
				
		switch ($row['protocol']) {		
			case 'bedo.cn':				
				$len = 1000;				
				break;								
			case '9911.com': 
				$len = self::__getLength($my_link);
				break;
			case 'msn.com':
				$len = 128 - mb_strlen($my_link, 'UTF-8');
				break;
			default:
				$len = 140 - mb_strlen($my_link, 'UTF-8');
				break;
		}		
		$message = mb_substr($message, 0, $len, 'UTF-8');				
		$result = $message . $my_link;		
		return $result;
	}	
	
	public static function formatBadge(array $params)
	{
		$badge = &$params['badge'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		$user_link = ' ' . BETTER_BASE_URL.'/' . $userInfo['username'];
		$result = $params['message'];
				
		switch ($params['protocol']) {
			case '9911.com': 				
				$len = self::__getLength($user_link);
				break;
			default:
				$len = 140 - mb_strlen($user_link, 'UTF-8');
				break;
		}
		
		$result = mb_substr($result, 0, $len, 'UTF-8');				
		$result .= $user_link;
		
		//$result .= $user->getUserLang()->badge_sync_suffix;
			
		return $result;
	}
	
	public static function formatMajor(array $params)
	{
		$poiId = (int)$params['poi_id'];
		$result = $params['message'];
		$poi_link = ' ' . BETTER_BASE_URL.'/poi/'.$poiId;
				
		switch ($params['protocol']) {
			case '9911.com': 								
				$len = self::__getLength($poi_link);
				break;				
			default:
				$len = 140 - mb_strlen($poi_link, 'UTF-8');
				break;
		}		
		$result = mb_substr($result, 0, $len, 'UTF-8');				
		$result .= $poi_link;
				
		return $result;
	}
	
	public static function __getLength($link)
	{
		$link_9911 = " http://9911.ms/FsDD";
		$dl = mb_strlen($link, 'UTF-8') - mb_strlen($link_9911, 'UTF-8');

		if ($dl < 0) {
			$dl = 0;
		}
							
		$len = 140 - mb_strlen($link_9911, 'UTF-8') - $dl - 1;

		return $len;
	}
	
	/**
	 * 
	 * @param $poi_link
	 * @param $bid
	 * @return unknown_type
	 */
	public static function __addBid($poi_link, $bid, $protocol)
	{
		
		if ($bid && $poi_link) {
			$poi_link .= "?bid=$bid";
		}

		return $poi_link;
	}
	
	
	/*
	 * Todo的格式
	 */
	protected static function _formatTodo(array $row, array $userInfo, array $poiInfo)
	{
		$message = trim($row['message'])!='' ? ' : '.trim($row['message']) : '';
		$result = '';
	
		//$lang = Better_Language::loadIt($userInfo['language'] ? $userInfo['language'] : 'zh-cn');
		
		$imAt =  '我想去 ';
		
		$text = $imAt . ' ' . $poiInfo['city'] . ' '.$poiInfo['name'] . $message;
		
		if ($row['protocol'] == 'bedo.cn') {
			$poi_link = 'BBEEDDOO' . BETTER_BASE_URL.'/poi/'.$poiInfo['poi_id'];
		} elseif ( in_array($row['protocol'], array('kaixin001.com', 'renren.com')) ) {
			$text = trim($row['message']);
			$poi_link = '';
		} else {
			$poi_link = ' ' . BETTER_BASE_URL.'/poi/'.$poiInfo['poi_id'];
		}
		
		$poi_link = self::__addBid($poi_link, $row['bid'], $row['protocol']);
		
		switch ($row['protocol']) {
			case 'bedo.cn':				
				$len = 1000;				
				break;			
			case '9911.com':				
				$len = self::__getLength($poi_link);				
				break;	
			case 'sina.com':
				$cnt = floor(substr_count($text, ' ') / 2);				
				$len = 140 - mb_strlen($poi_link, 'UTF-8') / 2 + $cnt; //新浪真的很挫 				
				break;	
			default:
				$len = 140 - mb_strlen($poi_link, 'UTF-8');
				break;
		}
		
		$text = mb_substr($text, 0, $len, 'UTF-8');	
	
		$result = $text . $poi_link;
		return $result;
	}
}