<?php

require_once('Zend/Uri.php');
define('BETTER_PASSBY_ZEND_URI', true);

class Better_Service_PushToOtherSites_Sites_Kaixin001com extends Better_Service_PushToOtherSites_Base
{

	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
	}
	
	public function __destruct()
	{

	}
	
	public function fakeLogin()
	{
		return $this->login();
	}
		
	public function login()
	{
		$url = 'http://wap.kaixin001.com/home/';
		$request = array(
			'bind' => '',
			'email' => $this->_username,
			'from' => '',
			'gotourl' => '',
			'login' => '登 录',
			'password' => $this->_password,
			'refcode' => '',
			'refuid' => 0,
			'remember' => 1,
			);
		
		$client = new Zend_Http_Client($url);
		$client->setParameterPost($request);
		$client->setCookieJar();
		$client->setHeaders('Referer', 'http://wap.kaixin001.com/');
		$client->request(Zend_Http_Client::POST);
		$html = $client->getLastResponse()->getBody();
		
		$this->_cookieJar = $client->getCookieJar();
		$this->_parseCookie();

		if ($this->_cookies['_user']!='' && $this->_cookies['_uid']!='')
		{
			$this->_logined = true;
		}

		return $this->_logined;	
	}
	
	public function post($msg)
	{				
		$this->_logined==false && $this->login();
$log_array = array();
$log_array[] = $this->_username;
$log_array[] = $this->_password;

		if ($this->_logined == true) {
			$url = 'http://wap.kaixin001.com/record/write.php';
			$client = new Zend_Http_Client($url);
			$client->setCookieJar($this->_cookieJar);
			$client->request();
			$html = $client->getLastResponse()->getBody();			
		    preg_match('<form method="post" action="(.*)" style="margin:0">', $html, $matches);	
		    $url = "http://wap.kaixin001.com" . $matches[1];	
$log_array[] = "url:$url";
			
			$request = array(
				'content' => $msg,
				'privacy' => '1',
				
				);							
			$client = new Zend_Http_Client($url);
			$client->setCookieJar($this->_cookieJar);
			$client->setParameterPost($request);
			$client->setHeaders('Referer', 'http://wap.kaixin001.com/record/write.php');
			$client->request(Zend_Http_Client::POST);


			$html = $client->getLastResponse()->getBody();
$log_array[] = "httpcode:" . $client->getLastResponse()->getStatus();			
$log_str = implode('||' ,$log_array);
Better_Log::getInstance()->logAlert($log_str, 'kaixin001', true);			
			
			$ok = true;
			return $ok;
		}
		
		return $ok;
	}
	
}