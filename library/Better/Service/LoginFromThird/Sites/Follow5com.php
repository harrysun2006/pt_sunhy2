<?php

class Better_Service_PushToOtherSites_Sites_Follow5com extends Better_Service_PushToOtherSites_Base 
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
		$url = 'http://www.follow5.com/f5/mwfm/uaccount';
		$request = array(
			'ctrl' => 'lg',
			'useremail' => $this->_username,
			'password' => $this->_password,
			'cookie' => '',
			);
		$client = new Zend_Http_Client($url);
		$client->setParameterPost($request);
		$client->setCookieJar();
		$client->setHeaders('Referer', 'http://www.follow5.com');
		$client->request(Zend_Http_Client::POST);
		$this->_cookieJar = $client->getCookieJar();
		$this->_parseCookie();

		if ($this->_cookies['NhjiKUu890ApfdsPKplsMIOe']!='') {
			$this->_logined = true;
		}

		return $this->_logined;
	}
	
	public function post($msg)
	{
		$this->_logined==false && $this->login();
		
		if ($this->_logined==true) {
			$url = 'http://www.follow5.com/f5/mwfm/unote?c=wn';

			$request = array(
				'filterFiendAccId' => '',
				'filterFriendAccName' => '',
				'gpr' => '',
				'groupId' => '-1',
				'linkCover' => '',
				'linkType' => '-1',
				'noteContent' => $msg,
				'noteId' => '',
				'noteLink' => 'http://',
				'notePhoto' => '',
				'noteType' => '0',
				'noteTypePrepare' => '-1',
				'photoNoteContent' => '',
				'photoNoteGroupTitle' => '',
				'photoNoteSetTitle' => '',
				'realname' => '',
				'receiverSelect' => '6',
				'receiverType' => '6',
				'setId' => '',
				'setTitle' => '',
				'c' => 'wn',
				);
			$client = new Zend_Http_Client($url);
			$client->setParameterPost($request);
			$client->setCookieJar($this->_cookieJar);
			$client->setHeaders('Referer', 'http://www.follow5.com/f5/mwfm/home');
			$client->request(Zend_Http_Client::POST);
			
			$html = $client->getLastResponse()->getBody();

		}
		
		return $this->_logined;
	}
}