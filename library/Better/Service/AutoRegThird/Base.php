<?php

/**
 * PushToOtherSites基类，所有PushToOtherSites的对象都继承自该类
 *
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_Service_AutoRegThird_Base
{
	protected $_username = '';
	protected $_password = '';
	protected $_accecss_token = '';
	protected $_accecss_token_secret = '';	
	protected $_logined = false;
	protected $_outputType = 'php';
	protected $_cookieJar = null;
	protected $_cookies = array();

	/**
	 * 设置登录用的用户名密码
	 *
	 * @param $username
	 * @param $password
	 * @return null
	 */
	public function setUserPwd($username,$password)
	{
		$this->_username = $username;
		$this->_password = $password;
	}

	/**
	 * 提交一个状态消息
	 * 具体实现在各个子类的方法中
	 *
	 * @param string $msg
	 * @return bool
	 */
	public function post($msg, $attach='') {}
	
	/**
	 * 解析网站返回的cookie
	 * 用在需要使用curl模拟登录的协议中
	 *
	 * @param string $headers
	 * @return array
	 */
	protected function _parseCookie()
	{
		$cookies = $this->_cookieJar->getAllCookies(Zend_Http_CookieJar::COOKIE_STRING_ARRAY);
		foreach($cookies as $v) {
			list($key,$value) = explode('=',$v);
			$this->_cookies[ $key ] = $value;
		}
		
		return $this->_cookies;
	}

}