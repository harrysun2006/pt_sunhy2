<?php

class Better_Service_PushToOtherSites_Sites_Sohucom extends Better_Service_PushToOtherSites_Common
{
	protected $_api_with_photo_url = '';
	protected $apiKey = 'peptalk_kai_zd6t';
		
	public function __construct($username='',$password='')
	{
		$this->_login_find_key = 'id';
		$this->_post_find_key = 'created_at';
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.t.sohu.com/account/verify_credentials.json?key=' . $this->apiKey;
		$this->_api_url = 'http://api.t.sohu.com/statuses/update.json?key=' . $this->apiKey;

        $this->_protocol = 'sohu.com';
	}
	

	public function post($msg, $attach='')
	{
		$flag = false;
		$this->_request = array(
			'status' => urlencode($msg)
			);			
		if ($attach) {
			$this->_api_url = 'http://api.t.sohu.com/statuses/upload.json' . '?key=' . $this->apiKey;
			$this->_file = 'pic';
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
		$this->_api_url = "http://api.t.sohu.com/statuses/destroy/$id.xml";
		return parent::delete($id, 'd');		
	}
	
	public function checkPost($return)
	{
		$flag = false;
		
		try {
			$dom = new Zend_Dom_Query();
			$dom->setDocumentXml($return);
			$items = $dom->query('created_at');
			foreach($items as $item) {
				$flag = $item->nodeValue=='true' ? true : false;
				break;
			}
		} catch(Exception $e) {
			Better_Log::getInstance()->logEmerg($e->getTraceAsString(), 'sync_sohu');
		}
		
		return $flag;
	}
	
	/**
	 * 
	 */
	public function getInfo()
	{
		$data = array();
		$data['nickname'] = $this->userinfo_json->screen_name;
		//$data['gender'] = $this->userinfo_json->gender == 'm' ? 'male' : 'female';
		$data['image_url'] = $data['imgurl'] = $this->userinfo_json->profile_image_url;

		return $data;
	}	
	
	/**
	 * get Friends
	 */
	public function getFriends()
	{
		$ids = array();
		$cursor = -1;
		while (1) {
			list($fids, $cursor) = $this->__getFriends($cursor);
			foreach ($fids as $fid) {
				$ids[] = $fid;
			}
			if (!$fids || !$cursor) break;
			if (count($ids) > 5000) break;
		} 
		
		return $ids;
	}
	
	/**
	 * get Friend
	 */
	public function __getFriends($cursor)
	{
		$ids = array();
		
		$_api_url = 'http://api.t.sohu.com/statuses/friends.json?key='.$this->apiKey;
		$client = new Zend_Http_Client($_api_url , array(
				'keepalive' => true,
				));			
		$client->setAuth($this->_username, $this->_password);
		$this->_request['cursor'] = $cursor;
		$this->_request['count'] = 10;
		$client->setParameterGet($this->_request);
		$client->request(Zend_Http_Client::GET);
		$http_code = $client->getLastResponse()->getStatus();
		$result = $client->getLastResponse()->getBody();

		if ($http_code == '200') {
			$json = json_decode($result);
			$users = $json->users;
			$cursor_id = $json->cursor_id;
			
			foreach ($users as $user) {
				$ids[] = $user->id;
			}
		}
		
		return array($ids, $cursor_id);		
	}
	
	
	/**
	 * 粉丝数
	 */
	public function getFollowerids()
	{
		$ids = array();
		$cursor = -1;
		while (1) {
			list($fids, $cursor) = $this->__getFollowerids($cursor);
			foreach ($fids as $fid) {
				$ids[] = $fid;
			}
			if (!$fids || !$cursor) break;
			if (count($ids) > 5000) break;
		} 
		
		return $ids;		
	}		
	
	/**
	 * 
	 */
	public function __getFollowerids($cursor)
	{
		$ids = array();
		$_api_url = 'http://api.t.sohu.com/statuses/followers.json?key='.$this->apiKey;
		$client = new Zend_Http_Client($_api_url , array(
				'keepalive' => true,
				));			
		$client->setAuth($this->_username, $this->_password);
		$this->_request['cursor'] = $cursor;
		$this->_request['count'] = 10;
		$client->setParameterGet($this->_request);
		$client->request(Zend_Http_Client::GET);
		$http_code = $client->getLastResponse()->getStatus();
		$result = $client->getLastResponse()->getBody();

		if ($http_code == '200') {
			$json = json_decode($result);
			$users = $json->users;
			$cursor_id = $json->cursor_id;
			
			foreach ($users as $user) {
				$ids[] = $user->id;
			}
		}
		
		return array($ids, $cursor_id);			
	}
	
	/**
	 * http://s5.cr.itc.cn/mblog/icon/21/a2/m_12770902145453.jpg
       http://s5.cr.itc.cn/mblog/icon/21/a2/12770902145453.jpg

	 */
	public function getAvatar()
	{
		$url = '';
		$url = $this->userinfo_json->profile_image_url;
		$url = str_replace('/m_', '/', $url);
		
		return $url;
	}
	
}