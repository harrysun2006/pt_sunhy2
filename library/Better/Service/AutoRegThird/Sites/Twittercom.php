<?php

class Better_Service_PushToOtherSites_Sites_Twittercom extends Better_Service_PushToOtherSites_Common
{
	protected $_host = 'www.twitter.com';
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.twitter.com/account/verify_credentials.xml';
		$this->_api_url = 'http://api.twitter.com/statuses/update.xml';
	}

	public function post($msg, $attach='')
	{
		//$this->_logined==false && $this->login();
		$flag = false;
		
		$authCredentials = base64_encode($this->_username.':'.$this->_password);
		$request[] = "POST ".$this->_api_url."?status=".urlencode($msg)." HTTP/1.1";
		$request[] = "Host: ".$this->_host;
		$request[] = "Content-Type: application/x-www-form-urlencoded";
		$request[] = "Authorization: Basic {$authCredentials}";
		$request[] = "Connection: Close";

		$socket5 = Better_Proxy::getSocket($this->_host, 80);
		if($socket5 instanceof Better_Socket5) {
			$html = $socket5->request(implode("\r\n", $request) . "\r\n\r\n");
			
			$flag = $this->checkPost($html);
			
			unset($socket5);
		}

		
		return $flag;	
	}
	
	public function login()
	{
		return $this->fakeLogin();
	}
	
	public function fakeLogin()
	{
		$authCredentials = base64_encode($this->_username.':'.$this->_password);

		$request = array();
		$request[] = "GET ".$this->_login_url." HTTP/1.1";
		$request[] = "Host: ".$this->_host;
		$request[] = "Content-Type: application/x-www-form-urlencoded";
		$request[] = "Authorization: Basic {$authCredentials}";
		$request[] = "Connection: Close";

        $logined = false;
        $socket5 = Better_Proxy::getSocket($this->_host, 80);

        if ($socket5 instanceof Better_Socket5) {
			$html = $socket5->request(implode("\r\n", $request) . "\r\n\r\n");
			try {
				if (preg_match('/<name>(.+)<\/name>/', $html)) {
					$logined = true;
				} else {
					Better_Log::getInstance()->logAlert($html, 'twitter');
				}
			} catch(Exception $e) {
				Better_Log::getInstance()->logEmerg($e->getTraceAsString(), 'twitter');
			}	

			unset($socket5);
        }
        
		return $logined;		
	}
	
	public function checkPost($html)
	{
		if (strpos($html, '<status>')) {
			return true;
		} else {
			return false;
		}
	}
}