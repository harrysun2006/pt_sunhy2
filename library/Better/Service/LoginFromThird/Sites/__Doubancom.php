<?php

class Better_Service_PushToOtherSites_Sites_Doubancom extends Better_Service_PushToOtherSites_Common
{
	//Add your own API_KEY and SECRET
	protected  $api_key='0a89a3b7930e99411f9ceb26b92bcbc3';
	protected  $api_secret='f072898a1cb665f0';
	
	/* the Douban client does everything */
	protected $client;
	
	function _construct($username, $password){	
		$this->client = new Zend_Gdata_DouBan($this->api_key, $this->api_secret);
	}
	
	
	public function post($msg) {
		
		/* step 2: when it comes back from Douban auth page */
		if (isset ( $_GET ['oauth_token'] )) {
			/* exchange the request token for access token */
			$key = $_COOKIE ['key'];
			$secret = $_COOKIE ['secret'];
			$result = $this->client->getAccessToken ( $key, $secret );
			$key = $result ["oauth_token"];
			$secret = $result ["oauth_token_secret"];
			if ($key) {
				
				/* access success, let's say something. */
				$this->client->programmaticLogin ( $key, $secret );
				//echo 'logged in.';
				$entry = new Zend_Gdata_Douban_BroadcastingEntry();
				$content = new Zend_Gdata_App_Extension_Content($msg);
				$entry->setContent ( $content );
				$entry = $this->client->addBroadcasting ( "saying", $entry );
				//echo '<br/>you just posted: ' . $entry->getContent ()->getText ();
			} else {
				echo 'Oops, get access token failed';
			}
		} 

	}
	
	//跳转到豆瓣页面，让用户授权
	public function authorization(){
			/* first, get request token. */
			$result = $this->client->getRequestToken ();
			$key = $result ["oauth_token"];
			$secret = $result ["oauth_token_secret"];
			
			/* save them somewhere, you'll need them in step 2. */
			setcookie ( 'key', $result ["oauth_token"], time () + 3600 );
			setcookie ( 'secret', $result ["oauth_token_secret"], time () + 3600 );
			
			/* get the auth url */
			$authurl = $this->client->getAuthorizationURL ( $key, $secret, 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );//callback 写什么？
			//echo '<a href="' . $authurl . '">click me to oauth it.</a>';
			header('Loaction: '.$authurl);
			exit();
	}
	
}

