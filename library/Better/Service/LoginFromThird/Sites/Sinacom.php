<?php

class Better_Service_LoginFromThird_Sites_Sinacom extends Better_Service_LoginFromThird_Common
{
	protected $apiKey = '';
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		
		$this->apiKey = Better_Config::getAppConfig()->sina_api->key;		
		$this->_login_url = 'http://api.t.sina.com.cn/account/verify_credentials.xml?source='.$this->apiKey;
		$this->_login_url_json = 'http://api.t.sina.com.cn/account/verify_credentials.json?source='.$this->apiKey;
		$this->_api_url = 'http://api.t.sina.com.cn/statuses/update.xml';				
		$this->_login_find_key = 'id';
		$this->_find_gender = 'gender';
		$this->_find_screen_name = 'screen_name';
		$this->_find_avatar = 'profile_image_url';
		$this->_find_img_url = &$this->_find_avatar;

	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Service/LoginFromThird/Better_Service_LoginFromThird_Common#fakeLogin()
	 */
	public function fakeLogin()
	{
		$logined = false;
		
		$client = new Zend_Http_Client($this->_login_url_json, array(
			'keepalive' => true,
			));
		$client->setAuth($this->_username, $this->_password);
		$client->request();
		$html = $client->getLastResponse()->getBody();	
		$status = $client->getLastResponse()->getStatus();
		
		if ($status == '200') {
			$logined = true;
			$json = json_decode($html);
			$json->id && $this->tid = $json->id;			
		}
			
		return $logined;
	}
	
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function getInfo()
	{		
		$client = new Zend_Http_Client($this->_login_url, array(
			'keepalive' => true,
			));
		$client->setAuth($this->_username, $this->_password);
		$client->request();
		$html = $client->getLastResponse()->getBody();
		$date = array();
		
		try {
			if (substr($html, 0, 5)=='<'.'?xml') {
				$dom = new Zend_Dom_Query();
				$dom->setDocumentXML($html);
				$name = $dom->query($this->_find_screen_name);	
				$gender = $dom->query($this->_find_gender);	
				$avatar = $dom->query($this->_find_avatar);					
				
				foreach($name as $item) {
					$date['nickname'] = trim($item->nodeValue);
					break;
				}
				foreach($gender as $item) {
					$usergender = trim($item->nodeValue);
					switch ($usergender){
						case 'm':
							$date['gender'] = 'male';
							break;
						case 'f':
							$date['gender'] = 'female';
							break;
					}
					break;
				}

				foreach($avatar as $item) {
					$date['image_url'] = trim($item->nodeValue);
					$date['imgurl'] = trim($item->nodeValue);

					break;
				}
			}
		} catch(Exception $e) {
			
		}	
		
		return $date;
	}	
	
	public function post($msg, $attach='')
	{
		$this->_request = array(
			'status' => $msg,
			'source' => $this->apiKey,
			);
		
			
		if ($attach) {
			$this->_api_url = 'http://api.t.sina.com.cn/statuses/upload.xml';
			$this->_file = 'pic';
		}	
		parent::post($msg, $attach);
		
		return true;
	}
	
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
	
}