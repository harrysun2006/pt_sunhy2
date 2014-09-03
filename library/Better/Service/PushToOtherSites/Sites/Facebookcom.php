<?php
/**
 * Facebook
 * @author Jeff
 *
 */
class Better_Service_PushToOtherSites_Sites_Facebookcom extends Better_Service_PushToOtherSites_Common
{
	protected $_host = 'www.facebook.com';
	
	public function __construct($username='', $password='', $accecss_token='', $accecss_token_secret='')
	{
		$this->setUserPwd($username,$password);
		$this->_login_url = '';
		$this->_api_url = 'https://graph.facebook.com/me/feed';
    	$this->_accecss_token = $accecss_token;
        $this->_accecss_token_secret = $accecss_token_secret;
        	
        $this->_protocol = 'facebook.com';	
	}
	
	public function _initCurl()
	{
		$tmpfname = tempnam("/tmp", "FOO");
		
		$hosts = explode('|', Better_Config::getAppConfig()->ssh->proxy->hosts);
		$proxy = $hosts[0];
		$ch = curl_init("http://www.facebook.com/");	
		$options = array(
						CURLOPT_HEADER => 0,
						CURLOPT_PROXY => $proxy,
						CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
						CURLOPT_COOKIEJAR => $tmpfname,
						CURLOPT_FOLLOWLOCATION => 1,
						CURLOPT_NOBODY => 0,
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8',
						CURLOPT_CONNECTTIMEOUT => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_SSL_VERIFYHOST => false,
						CURLOPT_AUTOREFERER => true,
						);		
		curl_setopt_array($ch, $options);

		return $ch;
	}

	public function post($msg, $attach='', $poiId='', $geo='')
	{		
		$accecss_token = $this->_accecss_token;
		$accecss_token_secret = $this->_accecss_token_secret;
		
		$ch = $this->_initCurl();	

		curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me/feed");	
		curl_setopt($ch, CURLOPT_POST, TRUE); 
		$postfields['access_token'] = $accecss_token;
		$postfields['message'] = $msg ;
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
		$text = curl_exec($ch);
		$this->html = $text;
		$info = curl_getinfo($ch);	
		$flag = false;
		$flag = $this->checkPost($text);
			
		if (!$flag) {
			$log_str = $accecss_token . '||' . $text;
			$this->_log($log_str, $this->_protocol);			
		}
		return $flag;
	}
	
	
	public function delete($id)
	{
		$ch = $this->_initCurl();
		
		curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/$id?method=delete"); ///COMMENT_ID?method=delete	
		curl_setopt($ch, CURLOPT_POST, TRUE); 
		$postfields['access_token'] = $this->_accecss_token;

		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
		$text = curl_exec($ch);
		$info = curl_getinfo($ch);

		return $info['http_code'] == 200 ? true : false;
	}
	
	public function login()
	{
		return $this->fakeLogin();
	}
	
	public function fakeLogin()
	{	
		return true;
	}

	public function checkPost($text)
	{
		$json = json_decode($text);
	
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
		$tokens = array();
		
		$app_id = Better_Config::getAppConfig()->oauth->key->facebook_appid;
		$scope = Better_Config::getAppConfig()->oauth->key->facebook_scope;

		$ch = $this->_initCurl();

		$postfields = array();
		//login
		$text = curl_exec($ch);
		$info = curl_getinfo($ch);

		preg_match('<input type="hidden" id="lsd" name="lsd" value="(.*)" autocomplete="off" />', $text, $matches);
		$postfields['lsd'] = $matches[1];
		
		$postfields['persistent'] = 1;
		$postfields['charset_test'] = '&euro;,&acute;,€,´,水,Д,Є';
		$postfields['charset_test'] = '&euro;,&acute;,€,´,水,Д,Є';
		$postfields['locale'] = 'zh_CN';
		$postfields['email'] = (string)$this->_username;
		$postfields['pass'] = (string)$this->_password;

		curl_setopt($ch, CURLOPT_POST, TRUE);		
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
		curl_setopt($ch, CURLOPT_URL, 'https://login.facebook.com/login.php?login_attempt=1');

		$text = curl_exec($ch);	
		$info = curl_getinfo($ch);
		
		//agree
		$url = "https://graph.facebook.com/oauth/authorize?client_id=$app_id" . 
		"&redirect_uri=http://www.facebook.com/connect/login_success.html&type=user_agent&display=popup&" . 
		"scope=$scope";

		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		$text = curl_exec($ch);
		$info = curl_getinfo($ch);

		$postfields = array();
		preg_match('<input type="hidden" autocomplete="off" name="post_form_id" value="(.*)" />', $text, $matches);
		$postfields['post_form_id'] = substr($matches[1], 0, 32);
		preg_match('<input type="hidden" name="fb_dtsg" value="(.*)" autocomplete="off" />', $text, $matches);
		$postfields['fb_dtsg'] = $matches[1];

		$postfields['app_id'] = $app_id;
		$postfields['display'] = 'popup';

		//preg_match('<input type="hidden" name="next" value="(.*)" />', $text, $matches);
		$postfields['next'] = "https://graph.facebook.com/oauth/authorize_success?client_id=$app_id&amp;redirect_uri=http%3A%2F%2Fwww.facebook.com%2Fconnect%2Flogin_success.html&amp;type=user_agent&amp;display=popup&amp;scope=" . urlencode($scope);
		$postfields['cancel_url'] = "https://graph.facebook.com/oauth/authorize_cancel?client_id=$app_id&amp;redirect_uri=http%3A%2F%2Fwww.facebook.com%2Fconnect%2Flogin_success.html&amp;type=user_agent&amp;display=popup&amp;scope=" . urlencode($scope);
		$postfields['client_id'] = $app_id;
		$postfields['redirect_uri'] = "http://www.facebook.com/connect/login_success.html";
		$postfields['type'] = "user_agent";
		$postfields['scope'] = $scope;
		$postfields['return_session'] = 1;
		$postfields['fbconnect'] = 1;
		$postfields['perms'] = $scope;
		$postfields['from_post'] = 1;
		$postfields['__uiserv_method'] = 'permissions.request';

		preg_match('<input value="(.*)" name="grant_clicked" type="submit" />', $text, $matches);
		$postfields['grant_clicked'] = $matches[1];

		curl_setopt($ch, CURLOPT_POST, TRUE);		
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
		curl_setopt($ch, CURLOPT_URL, 'https://www.facebook.com/connect/uiserver.php');

		$text = curl_exec($ch);	
		$info = curl_getinfo($ch);
		
		$url = htmlspecialchars_decode(urldecode($info['url']));
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		$text = curl_exec($ch);	
		$info = curl_getinfo($ch);

		$a = parse_url($info['url']);
		parse_str($a['fragment'], $output);
		$access_token = $output['access_token'];
		
		if ($access_token) {
			$tokens['oauth_token'] = $access_token;
			$tokens['oauth_token_secret'] = 'facebook';
		}
			
		return $tokens ;
	}
	
}