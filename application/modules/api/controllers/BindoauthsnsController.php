<?php

/**
 * Oauth 客户端绑定
 * 
 * @package Controllers
 * @author fengj <fengj@peptalk.cn>
 *
 */
class Api_BindoauthsnsController extends Better_Controller_Api
{
	public function init()
	{		
		parent::init();
	}	

	/**
	 * 
	 * 
	 * @return
	 */
	public function indexAction()
	{
		session_start();
		
		$domain = $this->getRequest()->getParam('domain', '');
		$uid = $this->getRequest()->getParam('uid', '');
		$key = $this->getRequest()->getParam('key', '');
		$callback = $this->getRequest()->getParam('callback', '');
		$aa = $this->getRequest()->getParam('aa', '');
		
		if ($aa) {
			$binded = $this->getRequest()->getParam('binded', 'true');
			$this->outputoutput($binded);
			exit;
		}		
		
		if ( $key !== md5($uid . 'bindoauthsns') && !$_SESSION['oauth']['uid']  ) exit('auth fail!');
		
		if (!$callback) {
			$callback = $_SESSION['oauth']['callback'];
		}
		if ($callback) {
			$this->_callback($callback, $uid);
			exit;
		}

		if ($domain == 'douban.com') {
			$this->_douban($uid, $key);
		} elseif ($domain == '163.com')	{
			$this->_bind163($uid, $key);
		} elseif ($domain == 'qq.com')	{
			$this->_qq($uid, $key);
		} elseif ($domain == 'qqsns.com')	{
			$this->_qqsns($uid, $key);
		} elseif ($domain == 'renren.com')	{
			$this->_rr($uid, $key);
		} elseif ($domain == '4sq.com')	{		
			$this->_4sq($uid, $key);
		} else {
			
		}

		
		exit;
	}
	
	
	/**
	 * 
	 */
	public function _callback($callback, $uid)
	{
		if ($callback == 'douban.com') {
			$this->_doubanCallback($uid);
		} elseif ($callback == '163.com')	{
			$this->_bind163Callback($uid);
		} elseif ($callback == 'qq.com')	{
			$this->_qqCallback($uid);
		} elseif ($callback == 'qqsns.com')	{
			$this->_qqsnsCallback($uid);
		} elseif ($callback == 'renren.com') {
			$this->_rrCallback($uid);
		} elseif ($callback == '4sq.com') {
			$this->_4sqCallback($uid);
		} else {
			$WB_AKEY = Better_Config::getAppConfig()->oauth->key->twitter_akey;
			$WB_SKEY = Better_Config::getAppConfig()->oauth->key->twitter_skey;	
		}		
	}
	
	
	/**
	 * 
	 */
	public function _douban($uid, $key)
	{
		$protocol = 'douban';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->douban_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->douban_skey;
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		$callback = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml?';
		$keys = $o->getRequestToken();
		$_SESSION['oauth_keys'] = $keys;
		
		$c['callback'] = $protocol . '.com';
		$c['uid'] = $uid;
		$c['key'] = $key;
		$callback .= http_build_query($c);
		
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);		
	}	
	
	/**
	 * 
	 */
	public function _doubanCallback($uid)
	{
		$protocol = 'douban';
		$keys = $_SESSION['oauth_keys'];
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->douban_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->douban_skey;
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );
		$o->protocol = $protocol;
		$last_key = $o->getAccessToken();
		$oauth_token = $last_key['oauth_token'];
		$oauth_token_secret = $last_key['oauth_token_secret'];
		$douban_user_id = $last_key['douban_user_id'];
		
		if ($oauth_token && $oauth_token_secret) {
			$user = Better_User::getInstance($uid);
			$user->syncsites()->add( $protocol . '.com', ucfirst($protocol), $protocol, $oauth_token, $oauth_token_secret, $douban_user_id);
			//成功页面
			$this->outputS();
		} else {
			//不成功
			$this->outputF();
		}				
	}
	
	/**
	 * 
	 */
	public function _bind163($uid, $key)
	{
		$protocol = '163';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->t163_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->t163_skey;
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		$o->client_type = 'mobile';
		$callback = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml?';
		$keys = $o->getRequestToken();
		$_SESSION['oauth_keys'] = $keys;
		
		$c['callback'] = $protocol . '.com';
		$c['uid'] = $uid;
		$c['key'] = $key;
		$callback .= http_build_query($c);
		
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);			
	}
	
	/**
	 * 
	 */
	public function _bind163Callback($uid)
	{
		$protocol = '163';
		$keys = $_SESSION['oauth_keys'];
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->t163_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->t163_skey;
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );
		$o->protocol = $protocol;
		$last_key = $o->getAccessToken();
		$oauth_token = $last_key['oauth_token'];
		$oauth_token_secret = $last_key['oauth_token_secret'];		
		if ($oauth_token && $oauth_token_secret) {
			$service = Better_Service_PushToOtherSites::factory($protocol . '.com', '', '', $oauth_token, $oauth_token_secret);
			$third_info = $service->getInfo();
			$t163_id = $third_info['id'];			
			$user = Better_User::getInstance($uid);
			$user->syncsites()->add( $protocol . '.com', ucfirst($protocol), $protocol, $oauth_token, $oauth_token_secret, $t163_id);
			//成功页面
			$this->outputS();
		} else {
			//不成功
			$this->outputF();
		}		
	}
	
	
	/**
	 * 
	 */
	public function _qq($uid, $key)
	{
		$protocol = 'qq';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qq_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qq_skey;	
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		$callback = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml?';
		$c['callback'] = $protocol . '.com';
		$c['uid'] = $uid;
		$c['key'] = $key;
		$callback .= http_build_query($c);		

		$keys = $o->getRequestToken($callback);
		$_SESSION['oauth_keys'] = $keys;
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
	}
	
	/**
	 * 
	 */
	public function _qqCallback($uid)
	{
		$protocol = 'qq';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qq_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qq_skey;			
		
		$keys = $_SESSION['oauth_keys'];
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );				
		$o->protocol = $protocol;
		
		$oauth_verifier = $_GET['oauth_verifier'];
		$oauth_token = $_GET['oauth_token'];
		$last_key = $o->getAccessToken($oauth_verifier, $oauth_token );
		$oauth_token = $last_key['oauth_token'];
		$oauth_token_secret = $last_key['oauth_token_secret'];
		$_name = $last_key['name'];
		if ($oauth_token && $oauth_token_secret) {
			$user = Better_User::getInstance($uid);
			$user->syncsites()->add( $protocol . '.com', $_name, $protocol, $oauth_token, $oauth_token_secret, $_name);
			$service = Better_Service_PushToOtherSites::factory('qq.com', $_name, $protocol, $oauth_token, $oauth_token_secret);
			$service->friendsAdd();
			$this->outputS();
		} else {
			//不成功
			$this->outputF();
		}		
	}

	
	/**
	 * 
	 */
	public function _qqsns($uid, $key)
	{
		$protocol = 'qqsns';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qqsns_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qqsns_skey;	
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		if ( APPLICATION_ENV != 'production' ) {
			$o->proxy = 'http://10.10.1.254:1080';
			$o->proxy_type = CURLPROXY_SOCKS5;
		}
		$callback = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml?';
$callback = "http://k.ai/api/bindoauthsns.xml?";
		$c['callback'] = $protocol . '.com';
		$c['uid'] = $uid;
		$c['key'] = $key;
		$callback .= http_build_query($c);		

		$keys = $o->getRequestToken($callback);
		$_SESSION['oauth_keys'] = $keys;
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
	}
	
	/**
	 * 
	 */
	public function _qqsnsCallback($uid)
	{
		$protocol = 'qqsns';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qqsns_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qqsns_skey;			
		
		$keys = $_SESSION['oauth_keys'];
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );				
		$o->protocol = $protocol;
		if ( APPLICATION_ENV != 'production' ) {
			$o->proxy = 'http://10.10.1.254:1080';
			$o->proxy_type = CURLPROXY_SOCKS5;
		}		
		$oauth_verifier = $_GET['oauth_vericode'];
		$oauth_token = $_GET['oauth_token'];
		$last_key = $o->getAccessToken($oauth_verifier, $oauth_token );
		$oauth_token = $last_key['oauth_token'];
		$oauth_token_secret = $last_key['oauth_token_secret'];
		$openid = $last_key['openid'];
		
		if ($oauth_token && $oauth_token_secret) {
			$user = Better_User::getInstance($uid);
			$user->syncsites()->add( $protocol . '.com', 'Qzone', $openid, $oauth_token, $oauth_token_secret, $openid);
			$this->outputS();
		} else {
			//不成功
			$this->outputF();
		}		
	}	
	
	/**
	 * 
	 */
	public function _rr($uid, $key)
	{
		$_SESSION['oauth']['uid'] = $uid;
		
		$protocol = 'renren';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->renren_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->renren_skey;
		
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		
		$callback = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml?';
		$c['callback'] = $protocol . '.com';
		$c['uid'] = $uid;
		$c['key'] = $key;
		$callback .= http_build_query($c);		
		
		$aurl = "https://graph.renren.com/oauth/authorize?client_id={$WB_AKEY}&response_type=code&display=touch&scope=publish_checkin+publish_feed+photo_upload+status_update&redirect_uri={$callback}";
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
	}
	
	
	/**
	 * 
	 */
	public function _rrCallback($uid)
	{
		$uid = $_SESSION['oauth']['uid'];
		$protocol = 'renren';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->renren_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->renren_skey;

		
		$code = $_GET['code'];
		$type = $_GET['type'];
		if ($code) {
			$callback_url = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml';
			$callback_url .= "?callback=$protocol.com&type=token";
			
			$url = "https://graph.renren.com/oauth/token?client_id={$WB_AKEY}&client_secret={$WB_SKEY}&redirect_uri={$callback_url}&grant_type=authorization_code&code={$code}";				
			$a = file_get_contents($url);
			$json = json_decode($a);
			$access_token = $json->access_token;
			if ($access_token) {
				$expires_in = $json->expires_in + time();
				$refresh_token = $json->refresh_token;
				$scope = $json->scope;
				$_temp_a = explode('-', $access_token);
				$tid = $_temp_a[1];
				
				$bind_user = Better_DAO_ThirdBinding::getBindUser('renren.com', $tid);
			}
			
		}		

		if ($access_token && $refresh_token && !$bind_user) {
			$user = Better_User::getInstance($uid);
			$user->syncsites()->add( $protocol . '.com', ucfirst($protocol), $protocol, $access_token, $refresh_token, $tid, $expires_in);
			//成功页面
			$this->outputS();
		} else {
			//不成功
			$this->outputF();
		}				
	}	

	
	/**
	 * 
	 */
	public function _4sq($uid, $key)
	{
		$_SESSION['oauth']['uid'] = $uid;
		
		$protocol = '4sq';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->foursq_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->foursq_skey;
		
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		
		$callback = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml?';
		$c['callback'] = $protocol . '.com';
		$c['uid'] = $uid;
		$c['key'] = $key;
		$callback .= http_build_query($c);	

		$_SESSION['oauth']['callback'] = $c['callback'];
		
		$aurl = "https://foursquare.com/oauth2/authenticate?client_id={$WB_AKEY}&response_type=code&display=touch&&redirect_uri={$callback}";
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
	}
	
	
	/**
	 * 
	 */
	public function _4sqCallback($uid)
	{
		$uid = $_SESSION['oauth']['uid'];
		$protocol = '4sq';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->foursq_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->foursq_skey;

		
		$code = $_GET['code'];
		$type = $_GET['type'];
		if ($code) {
			$callback_url = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml';
			$callback_url .= "?callback=$protocol.com&type=token";
			$access_token = Better_Service_PushToOtherSites_Sites_4sqcom::gottoken($callback_url, $code);
			if ($access_token) {
				$bind_user = false;
				$refresh_token = '';
			}
			
		}		

		if ($access_token  && !$bind_user) {
			$user = Better_User::getInstance($uid);
			$user->syncsites()->add( $protocol . '.com', 'Foursquare', $protocol, $access_token, $refresh_token);
			//成功页面
			$this->outputS();
		} else {
			//不成功
			$this->outputF();
		}				
	}

	
	/**
	 * 
	 */
	public function outputS()
	{
		$output = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml?aa=output&binded=true';
		$this->_helper->getHelper('Redirector')->gotoUrl($output);
		exit;
	}
	
	
	/**
	 * 
	 */
	public function outputF()
	{
		$output = Better_Config::getAppConfig()->base_url . '/api/bindoauthsns.xml?aa=output&binded=false';	
		$this->_helper->getHelper('Redirector')->gotoUrl($output);
		exit;
	}
	
	
	/**
	 * 
	 */
	public function outputoutput($binded)
	{
		$i = $binded == 'true' ? 's' : 'f';
		
		$html = <<<EOT
<html>
<head>
<style type="text/css">
body
{ 
  background-color:#CEEBFF;
  background-image:url('/images/3rdSite/bind_$i.png');
  background-repeat:no-repeat;
  background-attachment:fixed;
  background-position:center;
}
</style>
</head>
<body>
</body>
</html>
EOT;
		
		echo $html;
		
		exit;
	}
	
	
}