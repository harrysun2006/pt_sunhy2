<?php

class Better_Service_PushToOtherSites_Sites_Fanfoucom extends Better_Service_PushToOtherSites_Common
{
	protected $apiKey = '';
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		
		$this->apiKey = Better_Config::getAppConfig()->sina_api->key;
		
		$this->_login_url = 'http://api2.fanfou.com/account/verify_credentials.json';
		$this->_api_url = 'http://api2.fanfou.com/statuses/update.json';
				
		$this->_login_find_key = 'name';
		
		$this->_protocol = 'fanfou.com';
	}
	
	/**
	 * 提交的参数赋值
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#post($msg, $attach, $poiId, $geo)
	 */
	public function post($msg, $attach='', $poiId='', $geo='')
	{
		$this->_request = array(
			'status' => $msg,
			'source' => 'kaikai',
			);
		if ($geo) {
			$this->_request['location'] = $geo['lon'] . ',' . $geo['lat'];
		}
			
		if ($attach) {
			$this->_api_url = 'http://api2.fanfou.com/photos/upload.json';
			$this->_file = 'photo';
		}	
				
		return parent::post($msg, $attach, $poiId='', $geo='');
	}
	
	
	/**
	 * 删除
	 * @param $id
	 * @return unknown_type
	 */
	public function delete($id)
	{
		$this->_api_url = "http://api2.fanfou.com/statuses/destroy/$id.xml";
		
		$this->_request = array(
			//'source' => 'kaikai',
			'id' => $id,
			);
			
		return parent::delete($id);
	}
	
	/**
	 * 检查返回值
	 * @see Service/PushToOtherSites/Better_Service_PushToOtherSites_Common#checkPost($return)
	 */
	public function checkPost($return)
	{
		$flag = false;
		
		try {
			$dom = new Zend_Dom_Query();
			$dom->setDocumentXml($return);
			$items = $dom->query('update');
			foreach($items as $item) {
				$flag = $item->nodeValue=='true' ? true : false;
				break;
			}
		} catch(Exception $e) {
		}
		
		return $flag;
	}
	
	/**
	 * 寻找好友
	 */
	public function getFriends()
	{
		$_api_url = 'http://api2.fanfou.com/users/friends.json';
		$client = new Zend_Http_Client($_api_url , array(
				'keepalive' => true,
				));			
		$client->setAuth($this->_username, $this->_password);
		$client->request(Zend_Http_Client::GET);
		$http_code = $client->getLastResponse()->getStatus();
		$result = $client->getLastResponse()->getBody();

		if ($http_code == '200') {
			$json = json_decode($result);
			foreach ($json as $v) {
				$ids[] = $v->id;	
			}
		} else {
			$ids = array();
		}
		return $ids;
	}

	/**
	 * 粉丝数
	 */
	public function getFollowerids()
	{
		$_api_url = 'http://api2.fanfou.com/followers/ids.json';
		$client = new Zend_Http_Client($_api_url , array(
				'keepalive' => true,
				));			
		$client->setAuth($this->_username, $this->_password);
		$client->request(Zend_Http_Client::GET);
		$http_code = $client->getLastResponse()->getStatus();
		$result = $client->getLastResponse()->getBody();

		if ($http_code == '200') {
			$json = json_decode($result);
			foreach ($json as $v) {
				$ids[] = $v->id;	
			}
		} else {
			$ids = array();
		}
		return $ids;		
	}
}