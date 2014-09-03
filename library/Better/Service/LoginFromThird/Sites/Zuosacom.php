<?php

class Better_Service_PushToOtherSites_Sites_Zuosacom extends Better_Service_PushToOtherSites_Common
{
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.zuosa.com/account/verify_credentials.xml';
		$this->_api_url = 'http://api.zuosa.com/statuses/update.xml';
		$this->_login_find_key = 'authorized';
	}

	public function post($msg, $attach='')
	{
		$this->_request = array(
			'status' => $msg,
			'source' => 'kai',
			);
		return parent::post($msg, $attach);
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
	
}