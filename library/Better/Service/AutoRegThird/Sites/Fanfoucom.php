<?php

class Better_Service_PushToOtherSites_Sites_Fanfoucom extends Better_Service_PushToOtherSites_Common
{
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.fanfou.com/account/verify_credentials.xml';
		$this->_api_url = 'http://api.fanfou.com/statuses/update.xml';
	}

	public function post($msg)
	{
		$this->_request = array(
			'status' => $msg,
			'source' => '',
			);
		return parent::post($msg);
	}
	
	public function checkPost($return)
	{
		return true;
	}
}