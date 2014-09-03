<?php

class Better_Service_PushToOtherSites_Sites_Zuosacom extends Better_Service_PushToOtherSites_Common
{
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.zuosa.com/account/verify_credentials.json';
		$this->_api_url = 'http://api.zuosa.com/statuses/update.json';
		$this->_login_find_key = 'authorized';
		
		$this->_protocol = 'zuosa.com';
	}

	public function post($msg, $attach='')
	{
		$this->_request = array(
			'status' => $msg,
			'source' => 'kai',
			);
		return parent::post($msg, $attach);
	}
	
	/**
	 * 删除
	 * @param $id
	 * @return unknown_type
	 */
	public function delete($id)
	{
		//http://api.zuosa.com/statuses/destroy.xml?id=123456
		$this->_api_url = "http://api.zuosa.com/statuses/destroy.xml" . '?id=' . $id;				
		return parent::delete($id, 'g');		
	}		
	
	public function checkPost($return)
	{
		try {
			$dom = new Zend_Dom_Query();
			$dom->setDocumentXml($return);
			$items = $dom->query('id');
			foreach($items as $item) {
				$flag = is_numeric($item->nodeValue) ? true : false;
				break;
			}
			return $flag;
		} catch(Better_Exception $e) {
			return false;
		}
	}
	
	/**
	 * 得到粉丝数
	 */
	public function getFollowers()
	{
		$followers_count = 0;
		$this->_api_url = "http://api.zuosa.com/users/show.json?id=" . $this->_username;
		
		$client = new Zend_Http_Client($this->_api_url, array(
			'keepalive' => true,
			));
		$client->setAuth($this->_username, $this->_password);
		$client->request();
		$html = $client->getLastResponse()->getBody();
		$status = $client->getLastResponse()->getStatus();
		if ($status != 200) {
			return $followers_count;
		}

		$json = json_decode($html);	
		$followers_count = $json->followers_count;		
		return $followers_count;		
	}		
}