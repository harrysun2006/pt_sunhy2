<?php

require_once('Zend/Uri.php');
define('BETTER_PASSBY_ZEND_URI', true);

class Better_Service_PushToOtherSites_Sites_51com extends Better_Service_PushToOtherSites_Base
{
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
	}

	public function login()
	{
		$url = 'http://passport.51.com/login.5p';
		$request = array(
			'passport_51_user' => $this->_username,
			'passport_51_password' => $this->_password,
			'gourl' => urlencode('http://my.51.com'),
			);
		
		$client = new Zend_Http_Client($url, array(
			'keepalive' => true,
			));
		$client->setParameterPost($request);
		$client->setCookieJar();
		$client->setHeaders('Referer','http://www.51.com');
		$client->request(Zend_Http_Client::POST);
		
		$this->_cookieJar = $client->getCookieJar();
		$this->_parseCookie();

		if ($this->_cookies['FO_TOKEN']!='') {
			$this->_logined = true;
		}
		
		return $this->_logined;
	}
	
	public function fakeLogin()
	{
		return $this->login();
	}
	
	public function post($msg)
	{
		$this->_logined==false && $this->login();
		$ok = false;
		
		if ($this->_logined==true) {
			$url = 'http://my.51.com/user/editsigned.php';
			$request = array(
				'action' => 'save',
				'memo' => iconv("utf-8","gb2312",$msg)
				);
			$client = new Zend_Http_Client($url);
			$client->setParameterPost($request);
			$client->setCookieJar($this->_cookieJar);
			$client->request(Zend_Http_Client::POST);
			
			$html = $client->getLastResponse()->getBody();

			try {
				$obj = Zend_Json::decode($html);
				$ok = $obj['record']!='' ? true : false;
			} catch (Better_Exception $e) {
				return $ok;
			}
			
		}
		
		return $ok;
	}
}