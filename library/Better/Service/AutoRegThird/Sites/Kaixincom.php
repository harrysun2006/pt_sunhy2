<?php

require_once('Zend/Uri.php');
define('BETTER_PASSBY_ZEND_URI', true);

class Better_Service_PushToOtherSites_Sites_Kaixincom extends Better_Service_PushToOtherSites_Base
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
		$url = 'http://www.renren.com/PLogin.do';
		$request = 'email='.$this->_username.'&password='.$this->_password;
		$request = array(
			'domain' => 'kaixin.com',
			'email' => $this->_username,
			'password' => $this->_password,
			);
			
		$client = new Zend_Http_Client($url, array(
			'keepalive' => true,
			));
		$client->setParameterPost($request);
		$client->setCookieJar();
		$client->request(Zend_Http_Client::POST);
		
		$this->_cookieJar = $client->getCookieJar();
		$this->_parseCookie();

		if (isset($this->_cookies['kl']) && $this->_cookies['kl']!='')
		{
			$this->_logined = true;
		}

		return $this->_logined;
	}
	
	public function post($msg)
	{
		$this->_logined==false && $this->login();
		$ok = false;
		
		if ($this->_logined)
		{
			$url = 'http://www.kaixin.com/Home.do';
			$client = new Zend_Http_Client($url);
			$client->setCookieJar($this->_cookieJar);
			$client->request();
			$html = $client->getLastResponse()->getBody();	
			preg_match('<input type="hidden" id="publisher_form_ticket" value="(.*)" /> ', $html, $matches);
			$ticket = $matches[1];
			
			
			$url = 'http://status.kaixin.com/doing/update.do';
			$request = array(
				'c' => $msg,
				'raw'=> $msg,
				'isAtHome' => '1',
				'publisher_form_ticket' => $ticket,
				);
				
			$client = new Zend_Http_Client($url);
			$client->setParameterPost($request);
			$client->setCookieJar($this->_cookieJar);
			$client->setHeaders('Referer','http://status.kaixin.com');
			$client->request(Zend_Http_Client::POST);
			
			$html = $client->getLastResponse()->getBody();

			try {
				$obj = Zend_Json::decode($html);
				$ok = is_numeric($obj['updateStatusId']) ? true : false;
			} catch(Better_Exception $e) {
				
			}

		}
		
		return $ok;
	}
}