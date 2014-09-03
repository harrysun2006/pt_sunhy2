<?php

class Better_Service_PushToOtherSites_Sites_Tongxuecom extends Better_Service_PushToOtherSites_Base
{
	protected $_login_url;
	protected $_api_url;
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.tongxue.com/account/verify_credentials.php';
		$this->_api_url = 'http://api.tongxue.com/statuses/update.php';
	}
	
	public function fakeLogin()
	{
		return $this->login();
	}

	public function login()
	{
		$request = array(
			'email' => $this->_username,
			'password' => $this->_password,
			);
		$client = new Zend_Http_Client($this->_login_url);
		$client->setCookieJar();
		$client->setParameterPost($request);
		$client->setAuth($this->_username, $this->_password);
		$client->request(Zend_Http_Client::POST);
		
		$this->_cookieJar = $client->getCookieJar();
		$html = $client->getLastResponse()->getBody();
		$result = Zend_Json::decode($html);

		if (isset($result['id']) && !empty($result['id']))
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
			$request = array(
				'status' => $msg,
				'source' => 'Better',
				);
				
			$client = new Zend_Http_Client($this->_api_url);
			$client->setParameterPost($request);
			$client->setHeaders('Referer', 'http://tongxue.com/');
			$client->setCookieJar($this->_cookieJar);
			$client->request(Zend_Http_Client::POST);
			
			$html = $client->getLastResponse()->getBody();

			try {
				$x = Zend_Json::decode($html);
				if (isset($x['id']) && !empty($x['id'])) {
					$ok = true;
				}

			} catch(Exception $e) {
				
			}

		}
		
		return $ok;
	}
	
}