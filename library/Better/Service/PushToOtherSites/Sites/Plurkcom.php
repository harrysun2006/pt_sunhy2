<?php

class Better_Service_PushToOtherSites_Sites_Plurkcom extends Better_Service_PushToOtherSites_Base
{
	protected $_login_url;
	protected $_api_url;
	protected $_api_key;
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://www.plurk.com/API/Users/login';
		$this->_api_url = 'http://www.plurk.com/API/Timeline/plurkAdd';
		$this->_api_key = '';//需要申请
	}
	
	public function fakeLogin()
	{
		return $this->login();
	}

	public function login()
	{
		$request = array(
			'api_key' =>  $this->_api_key ,
			'username' => $this->_username,
			'password' => $this->_password,
			'no_data' => 1,
			);
		$client = new Zend_Http_Client($this->_login_url);
		$client->setParameterPost($request);
		//$client->setAuth($this->_username, $this->_password);
		$client->setCookieJar();
		$client->request(Zend_Http_Client::POST);
		
		$this->_cookieJar = $client->getCookieJar();
		$html = $client->getLastResponse()->getBody();
		$result = Zend_Json::decode($html);

		if (isset($result['success_text']) && $result['success_text']=='ok')
		{
			$this->_logined = true;
		}
		
		return $this->_logined;
	}
	
	
	
	public function post($msg, $qualifier='says', $no_comments = 0, $limited_to = NULL, $lang = 'en')
	{
		$this->_logined==false && $this->login();
		$ok = false;
		
		if ($this->_logined)
		{
			$request = array(
				'api_key' =>  $this->_api_key ,
				'content' => $msg,
				'qualifier' => $qualifier,
				'no_comments' => $no_comments,
				'limited_to' => $limited_to,
				'lang' => $lang,
				);
				
			$client = new Zend_Http_Client($this->_api_url);
			$client->setParameterPost($request);
			$client->setHeaders('Referer', 'http://plurk.com/');
			$client->setCookieJar($this->_cookieJar);
			$client->request(Zend_Http_Client::POST);
			
			$html = $client->getLastResponse()->getBody();

			try {
				$x = Zend_Json::decode($html);
				if (isset($x['plurk_id']) && !empty($x['plurk_id'])) {
					$ok = true;
				}

			} catch(Exception $e) {
				
			}

		}
		
		return $ok;
	}
	
}