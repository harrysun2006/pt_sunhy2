<?php

/**
 * 生成推送到其他微博客的对象
 *
 * @package Better.Service
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Service_LoginFromThird
{
	/**
	 * 目前支持的协议（网站）
	 *
	 * @var array
	 */
	public static $supportedProtocols = array(
		'4sq.com', 'zuosa.com','renren.com','digu.com','fanfou.com','twitter.com','51.com','9911.com','follow5.com','kaixin001.com','kaixin.com', 'sina.com','douban.com', 'tongxue.com', 'plurk.com','sohu.com'
		);
		
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
			
			$protocolClass = 'Better_Service_LoginFromThird_Sites_'.ucfirst(str_replace('.', '', $protocol));
			if (class_exists($protocolClass)) {
				if ($oauth_token &&  $oauth_token_secret) {
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
			if ( preg_match('/掌门/', $row['content']) || preg_match('/mayor/', $row['content'])  ) {
				$link = ' ' . BETTER_BASE_URL . '/poi/' . $poiInfo['poi_id'];
			} else {
				$link = ' ' . BETTER_BASE_URL . '/' . $userInfo['username'];
			}
						
			$text = $row['content'];
			
			switch ($row['protocol']) {
				case '9911.com':
					$len = self::__getLength($link);				
					break;				
				default:
					$len = 140 - mb_strlen($link, 'UTF-8');
					break;
			}
			
			$text = mb_substr($text, 0, $len, 'UTF-8');				
			$result = $text . $link;

		} else {
			$result = $row['message'];
			
			switch ($row['type']) {
				case 'checkin':
					$result = self::_formatCheckin($row, $userInfo, $poiInfo);
					break;
				case 'normal':
				default:
					$result = self::_formatShout($row, $userInfo, $poiInfo);
					break;
			}
		}
		
		return $result;
	}
	
	protected static function _formatCheckin(array $row, array $userInfo, array $poiInfo)
	{
		$message = trim($row['message'])!='' ? ' : '.trim($row['message']) : '';
		$result = '';
	
		$lang = Better_Language::loadIt($userInfo['language'] ? $userInfo['language'] : 'zh-cn');
		$imAt = $lang->global->imat;
		$imAt = $imAt ? $imAt : '我在';
		
		$text = $imAt . ' ' . $poiInfo['city'] . ' '.$poiInfo['name'] . $message;
		$poi_link = ' ' . BETTER_BASE_URL.'/poi/'.$poiInfo['poi_id'];
		
		switch ($row['protocol']) {
			case '9911.com':				
				$len = self::__getLength($poi_link);				
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
		$my_link = ' ' . BETTER_BASE_URL . '/' . $userInfo['username'];
		$message = $row['message'];
				
		switch ($row['protocol']) {						
			case '9911.com': 
				$len = self::__getLength($my_link);
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
	
}