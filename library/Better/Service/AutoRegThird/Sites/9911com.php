<?php

class Better_Service_PushToOtherSites_Sites_9911com extends Better_Service_PushToOtherSites_Common
{

	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.9911.com/account/verify_credentials.xml';
		$this->_api_url = 'http://api.9911.com/statuses/update.xml';
		$this->_login_find_key = 'name';
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

	public function post($msg, $attach='')
	{
		$this->_request = array(
			'status' => $msg,
			'source' => 'Better',
			);

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