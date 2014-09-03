<?php

class Better_Service_PushToOtherSites_Sites_Digucom extends Better_Service_PushToOtherSites_Common
{
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.minicloud.com.cn/account/verify.json';
		$this->_api_url = 'http://api.minicloud.com.cn/statuses/update.json';
		$this->_login_find_key = 'authorized';
		
		$this->_file = 'image0';
		
		$this->_protocol = 'digu.com';
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
	
	/**
	 * 删除
	 * @param $id
	 * @return unknown_type
	 */
	public function delete($id)
	{
		//http://api.minicloud.com.cn/statuses/destroy/
		$this->_api_url = "http://api.minicloud.com.cn/statuses/destroy/$id.xml";
			
		return parent::delete($id, 'g');		
	}	
	
	public function checkPost($return)
	{
		return true;
	}
	
}