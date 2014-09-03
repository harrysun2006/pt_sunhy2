<?php

/**
 * 生成推送到其他微博客的对象
 *
 * @package Better.Service
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Service_AutoRegThird
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
	public static function factory($protocol)
	{
		$protocol = strtolower($protocol);
		
		if (in_array($protocol, self::$supportedProtocols)) {
			
			$protocolClass = 'Better_Service_AutoRegThird_Sites_'.ucfirst(str_replace('.', '', $protocol));
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
	

	
	
}