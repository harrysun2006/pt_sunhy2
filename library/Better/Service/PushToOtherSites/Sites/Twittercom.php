<?php

class Better_Service_PushToOtherSites_Sites_Twittercom extends Better_Service_PushToOtherSites_Common
{
	protected $_host = 'www.twitter.com';
	
	public function __construct($username='', $password='', $accecss_token='', $accecss_token_secret='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = 'http://api.twitter.com/account/verify_credentials.xml';
		$this->_api_url = 'http://api.twitter.com/statuses/update.json';
    	$this->_accecss_token = $accecss_token;
        $this->_accecss_token_secret = $accecss_token_secret;		
	}

	public function post($msg, $attach='', $poiId='', $geo='')
	{		
		if (!$this->_accecss_token || !$this->_accecss_token_secret) {
			return $this->__post($msg, $attach, $poiId, $geo);
		}
		
		$akey = Better_Config::getAppConfig()->oauth->key->twitter_akey;;
		$skey = Better_Config::getAppConfig()->oauth->key->twitter_skey;;
		
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		
		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
		$hosts = explode('|', Better_Config::getAppConfig()->ssh->proxy->hosts);
		$this->oauth->proxy = $hosts[0];	
		$this->oauth->proxy_type = CURLPROXY_SOCKS5;
		$this->oauth->protocol == 'twitter';
		
        $param = array();
            	
    	$url = $this->_api_url;
		  	
		$param['status'] = $msg;
		if ($geo) {
			$url .= "?lat={$geo['lat']}&long={$geo['lon']}";
			$param['lat'] = $geo['lat'];
			$param['long'] = $geo['lon'];
		}    	
		
		$text = $this->oauth->post($url , $param);			
		$this->html = $text;
		
		$flag = false;
		$flag = $this->checkPost($text);	
		return $flag;	
	}
	
	public function delete($id)
	{
		$akey = Better_Config::getAppConfig()->oauth->key->twitter_akey;;
		$skey = Better_Config::getAppConfig()->oauth->key->twitter_skey;;
		
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		
		$this->oauth = new Better_Oauth_Weibo( $akey , $skey , $accecss_token , $accecss_token_secret ); 
		$hosts = explode('|', Better_Config::getAppConfig()->ssh->proxy->hosts);
		$this->oauth->proxy = $hosts[0];	
		$this->oauth->proxy_type = CURLPROXY_SOCKS5;
		
		$url = "http://api.twitter.com/statuses/destroy/$id.xml";
		$html = $this->oauth->post($url);
		
		return $this->oauth->http_code == 200 ? true : false;
	}
	
	public function login()
	{
		return $this->fakeLogin();
	}
	
	public function fakeLogin()
	{	
		if ($this->_accecss_token && $this->_accecss_token_secret) {
			return true;
		}
		
		return $this->__fakeLogin();	
	}
	
	/**
	 * base auth
	 * @return unknown_type
	 */
	public function __fakeLogin()
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
	
	/**
	 * base auth
	 * @param $msg
	 * @param $attach
	 * @param $poiId
	 * @param $geo
	 * @return unknown_type
	 */
	public function __post($msg, $attach='', $poiId='', $geo='')
	{
		$flag = false;
		
		$url = $this->_api_url . "?status=" . urlencode($msg);
		if ($geo) {
			$url .= "&lat={$geo['lat']}&long={$geo['lon']}";
		} 		
		
		$authCredentials = base64_encode($this->_username.':'.$this->_password);
		$request[] = "POST " . $url . " HTTP/1.1";
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
	
	public function checkPost($text)
	{
		$json = json_decode($text);
		//parent::_log($text, 'twitter');
		if ($json->id) {
			return true;
		}
var_dump($text);				
		return false;
	}
	
	/**
	 * 取token
	 * @return unknown_type
	 */
	public function getToken()
	{
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->twitter_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->twitter_skey;	
		$tokens = array();

		$hosts = explode('|', Better_Config::getAppConfig()->ssh->proxy->hosts);
		$proxy = $hosts[0];
		$proxyType = CURLPROXY_SOCKS5;
		
		$tmpfname = tempnam("/tmp", "FOO");			

		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = 'twitter';
	
		if (APPLICATION_ENV!='production') {
			$proxy = 'http://127.0.0.1:8580';
			$proxyType = CURLPROXY_HTTP;
		}
				
		$o->proxy = $proxy;
		$o->proxy_type = $proxyType;
		
		$keys = $o->getRequestToken();		
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , 'http://k.ai/setting/sync?callback=twitter');	

		$ch = curl_init("http://twitter.com/");	
		$options = array(
						CURLOPT_HEADER => 0,
						CURLOPT_PROXY => $proxy,
						CURLOPT_PROXYTYPE => $proxyType,
						CURLOPT_FOLLOWLOCATION => 1,
						CURLOPT_NOBODY => 0,
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_COOKIEJAR => $tmpfname,
						CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8',
						CURLOPT_CONNECTTIMEOUT => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_SSL_VERIFYHOST => false,
						);		
		curl_setopt_array($ch, $options);
		
		$text = curl_exec($ch);	
//var_dump($text);
		
		//取登陆页面中
		preg_match('<input name="authenticity_token" type="hidden" value="(.*)" />', $text, $matches);
		$authenticity_token = $matches[1];		
		preg_match('<input id="return_to_ssl" name="return_to_ssl" type="hidden" value="(.*)" />', $text, $matches);
		$return_to_ssl = $matches[1];
		preg_match('<input type="hidden" name="q" id="signin_q" value="(.*)"/>', $text, $matches);
		$q = $matches[1];				
		$postfields = array(
				'authenticity_token' => (string)$authenticity_token,
				'return_to_ssl' => (string)$return_to_ssl,
				'q' => (string)$q,
				'session[username_or_email]' => (string)$this->_username,
				'session[password]' => (string)$this->_password,
				);
		
		curl_setopt($ch, CURLOPT_POST, TRUE);		
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
		curl_setopt($ch, CURLOPT_URL, 'https://twitter.com/sessions');
		$text = curl_exec($ch);
		$info = curl_getinfo($ch);
//print_r($info);		
//var_dump($text);
//file_put_contents('e:/temp/login.html', $text);

		//取同意页面
//var_dump($aurl);		
		curl_setopt($ch, CURLOPT_POST, 0); 
		$url = $aurl;
		curl_setopt($ch, CURLOPT_URL, $url);
		$text = curl_exec($ch);
		$info = curl_getinfo($ch);
//print_r($info);	
//var_dump($text);
//file_put_contents('e:/temp/agree.html', $text);

				
		//解析同意页面 后发送请求
		preg_match('<input name="authenticity_token" type="hidden" value="(.*)" />', $text, $matches);
		$authenticity_token = substr($matches[1], 0, 40);
		preg_match('<input id="oauth_token" name="oauth_token" type="hidden" value="(.*)" />', $text, $matches);
		$oauth_token = $matches[1];
		$postfields = array(
						'authenticity_token' => (string)$authenticity_token,
						'oauth_token' => (string)$oauth_token,
						'session[username_or_email]' => (string)$this->_username,
						'session[password]' => (string)$this->_password,		
						);	
//var_dump($postfields);						
		curl_setopt($ch, CURLOPT_POST, TRUE); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
		curl_setopt($ch, CURLOPT_URL, 'https://api.twitter.com/oauth/authorize');
		$text = curl_exec($ch);
		$info = curl_getinfo($ch);
//print_r($info);	
//var_dump($text);
//file_put_contents('e:/temp/agree_after.html', $text);
		
		if ($info['http_code'] != 200) {
			return $tokens;
		}
//var_dump($WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']);		
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );
		$o->protocol = 'twitter';
		$o->proxy = $proxy;
		$o->proxy_type = $proxyType;			

	
		$last_key = $o->getAccessToken();							
		$oauth_token = $last_key['oauth_token'];
		$oauth_token_secret = $last_key['oauth_token_secret'];
		$tokens['oauth_token'] = $oauth_token;
		$tokens['oauth_token_secret'] = $oauth_token_secret;
		$tokens['user_id'] = $last_key['user_id'];
		$tokens['screen_name'] = $last_key['screen_name'];
//var_dump($last_key);exit;			
		return $tokens;
	}
	
}