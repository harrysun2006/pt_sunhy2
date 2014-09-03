<?php

require_once('Zend/Uri.php');
define('BETTER_PASSBY_ZEND_URI', true);

class Better_Service_PushToOtherSites_Sites_Renrencom extends Better_Service_PushToOtherSites_Base
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
		$request = array(
			'email' => $this->_username,
			'password' => $this->_password,
			'domain' => 'renren.com',
			);
		$client = new Zend_Http_Client($url);
		$client->setParameterPost($request);
		$client->setCookieJar();
		$client->request(Zend_Http_Client::POST);
		
		$this->_cookieJar = $client->getCookieJar();
		$this->_parseCookie();

		if (isset($this->_cookies['kl']) && $this->_cookies['kl']!='null')
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
			$url = 'http://www.renren.com/Home.do';
			$client = new Zend_Http_Client($url);
			$client->setCookieJar($this->_cookieJar);
			$client->request();
			$html = $client->getLastResponse()->getBody();	
			preg_match('<input type="hidden" id="publisher_form_ticket" value="(.*)" /> ', $html, $matches);
			$ticket = $matches[1];
			
			$url = 'http://status.renren.com/doing/update.do';
			$request = array(
				'c' => $msg,
				'raw' => $msg,
				'isAtHome' => '1',
				'publisher_form_ticket' => $ticket,
				);
				
			$client = new Zend_Http_Client($url);
			$client->setParameterPost($request);
			$client->setHeaders('Referer', 'http://status.renren.com/ajaxproxy.htm');
			$client->setCookieJar($this->_cookieJar);
			$client->request(Zend_Http_Client::POST);
			
			$html = $client->getLastResponse()->getBody();
			
			try {
				$x = Zend_Json::decode($html);
				if (isset($x['updateStatusId']) && isset($x['code']) && $x['code']=='0') {
					$ok = true;
				}

			} catch(Better_Exception $e) {
				
			}

		}
		
		return $ok;
	}
}
