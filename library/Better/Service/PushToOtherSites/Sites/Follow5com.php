<?php
/**
 * Follow5 
 * @author Jeff
 *
 */
class Better_Service_PushToOtherSites_Sites_Follow5com extends Better_Service_PushToOtherSites_Common 
{

	public function __construct($username='',$password='')
	{
		$username = iconv("UTF-8", "GB2312//IGNORE", $username);
		$this->setUserPwd($username, $password);
		$this->apiKey = Better_Config::getAppConfig()->follow5_api->key;
		//$this->apiKey = 'C9BF6B738FC9157E';
		
		$this->_login_url = 'http://api.follow5.com/api/users/verify_credentials.json?api_key=' . $this->apiKey;
		$this->_api_url = 'http://api.follow5.com/api/statuses/update.json';
		
		
		$this->_protocol = 'follow5.com';
	}
	
	public function post($msg, $attach='')
	{
		$this->_request = array(
			'api_key' => $this->apiKey,
			'status' => $msg,
			);	
			
		$attach = '';	
		return parent::post($msg, $attach);
	}
	
	public function delete($id)
	{
		$this->_api_url = "http://api.follow5.com/api/statuses/destroy.json?api_key=" . $this->apiKey . '&id=' . $id;
		return parent::delete($id);
	}	
	
	/**
	 * 得到粉丝数
	 */
	public function getFollowers()
	{
		$followers_count = 0;
		$_url = "http://api.follow5.com/api/users/show.json?api_key=" . $this->apiKey;
		
		$client = new Zend_Http_Client($_url, array(
			'keepalive' => true,
			));
		$client->setAuth($this->_username, $this->_password);
		$client->request(Zend_Http_Client::GET);
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