<?php

class Better_Service_LoginFromThird_Sites_Sohucom extends Better_Service_LoginFromThird_Common
{
	protected $_api_with_photo_url = '';
	protected $apiKey = 'peptalk_kai_zd6t';
		
	public function __construct($username='',$password='')
	{
		$this->_login_find_key = 'id';
		$this->_post_find_key = 'created_at';
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.t.sohu.com/account/verify_credentials.xml?key='.$this->apiKey;
		$this->_api_url = 'http://api.t.sohu.com/statuses/update.xml?key='.$this->apiKey;
		$this->_find_screen_name = 'screen_name';
	}
	
	public function fakeLogin()
	{
		$logined = false;
		$client = new Zend_Http_Client($this->_login_url, array(
			'keepalive' => true,
			));
		$client->setAuth($this->_username, $this->_password);
		$client->request();
		$html = $client->getLastResponse()->getBody();

		try {
			if (substr($html, 0, 5)=='<'.'?xml') {
				$dom = new Zend_Dom_Query();
				$dom->setDocumentXML($html);
				$items = $dom->query($this->_login_find_key);
				foreach($items as $item) {
					$logined = trim($item->nodeValue)!='' ? true : false;
					break;
				}
			}
		} catch(Exception $e) {
			
		}
		
		return $logined;
	}	
	
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
				//$gender = $dom->query($this->_find_gender);							
				foreach($name as $item) {
					$date['nickname'] = trim($item->nodeValue);
					break;
				}				
			}
		} catch(Exception $e) {
			
		}	
		
		return $date;
	}	
	
	public function post($msg, $attach='')
	{
		$flag = false;
		$this->_request = array(
			'status' => urlencode($msg)
			);
			
		if ($attach) {
			$this->_api_url = 'http://api.t.sohu.com/statuses/upload.xml';
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
}