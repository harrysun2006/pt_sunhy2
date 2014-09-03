<?php

class Better_Service_PushToOtherSites_Sites_Digucom extends Better_Service_PushToOtherSites_Common
{
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.minicloud.com.cn/account/verify.xml';
		$this->_api_url = 'http://api.minicloud.com.cn/statuses/update.xml';
		$this->_login_find_key = 'authorized';
		
		$this->_file = 'image0';
	}

	public function post($msg, $attach='')
	{
		$this->_request = array(
			'content' => urlencode($msg),
			'source' => '',
			);
			
		if ($attach) {
			$this->_request['uploadImg'] = 'xiexiezhichi';
			$this->_file = 'image0';
		}
					
		return parent::post($msg, $attach);
	}
	
	public function checkPost($return)
	{
		return true;
	}
	
}