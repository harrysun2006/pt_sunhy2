<?php

/**
 * Oauth 客户端oauth 登录
 * 
 * @package Controllers
 * @author fengj <fengj@peptalk.cn>
 *
 */
class Api_OauthloginController extends Better_Controller_Api
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
		
		$sns = $this->getRequest()->getParam('sns', '');		
		$callback = $this->getRequest()->getParam('callback', '');
		$aa = $this->getRequest()->getParam('aa', '');
	
		if ($callback) {
			$this->_callback($callback);
			exit;
		}
		
		if ($aa) {
			$logined = $this->getRequest()->getParam('logined', 'true');
			$this->outputoutput($logined);
			exit;
		}		

    	$_SESSION['oauth']['partner'] = $this->getRequest()->getParam('kai_partner', ''); //
    	$_SESSION['oauth']['secret'] = $this->getRequest()->getParam('secret', ''); //	
    			
		if ( 'renren.com' == $sns )	{
			$this->_rr();
		} elseif( 'douban.com' == $sns) {
			$this->_dd();
		} elseif( '163.com' == $sns) {
			$this->_163();
		} elseif( 'qq.com' == $sns) {
			$this->_qq();
		} elseif( 'qqsns.com' == $sns) {
			$this->_qqsns();
		} else {
			exit;	
		}

		exit;
	}
	
	
	/**
	 * 
	 */
	public function _callback($callback)
	{
		if ( 'renren.com' == $callback )	{
			$this->_rrCallback();
		} elseif ( 'douban.com' == $callback ) {
			$this->_ddCallback();
		} elseif ( '163.com' == $callback ) {
			$this->_163Callback();
		} elseif ( 'qq.com' == $callback ) {
			$this->_qqCallback();
		} elseif ( 'qqsns.com' == $callback ) {
			$this->_qqsnsCallback();
		} 
		exit;		
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function _qq()
	{
		$protocol = 'qq';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qq_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qq_skey;	
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		if ( APPLICATION_ENV != 'production' ) {
			$o->proxy = 'http://10.10.1.254:1080';
			$o->proxy_type = CURLPROXY_SOCKS5;
		}
				
		$callback = Better_Config::getAppConfig()->base_url . '/api/oauthlogin.xml?';
		$c['callback'] = $protocol . '.com';
		$callback .= http_build_query($c);		

		$keys = $o->getRequestToken($callback);
		$_SESSION['oauth_keys'] = $keys;
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function _qqCallback()
	{
		$appConfig = Better_Config::getAppConfig();
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
			$service = Better_Service_PushToOtherSites::factory($protocol . '.com', '', '', $oauth_token, $oauth_token_secret);
			$third_info = $service->getInfo();
			$id = $_name;
			$email = $id . "@t.qq.com";
			$password = rand(1000, 9999);
			$bind_user = Better_DAO_ThirdBinding::getBindUser('qq.com', $id);			

			if ($bind_user) {
				//生成token
				$tmp = $this->api->loginToken($bind_user['uid']);
				$username = $tmp['username'];
				$password = $tmp['token'];
			} else {
				//检查是否注册过
				$user = Better_User::getInstance($email, 'email');					
				$userInfo = $user->getUserInfo();	
				if( $userInfo['state'] == Better_User_State::BANNED ){
					$this->error('error.users.account_banned');	
					exit;
				}
				$uid = (int)$user->uid;
				if ($uid) {
					$tmp = $this->api->loginToken($uid);
					$username = $tmp['username'];
					$password = $tmp['token'];
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', ucfirst($id), $protocol, $oauth_token, $oauth_token_secret, $id);						
				} else {
					//注册
					$uid = $this->_regNew($email, $password, $third_info, $protocol);
					
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', ucfirst($id), $protocol, $oauth_token, $oauth_token_secret, $id);
					//: 发私信告诉密码
					$content = "欢迎使用开开, 您的开开帐号是 {$email},密码是 {$password}。下次您也可以使用此账号直接登录开开。从今天起，记录足迹，分享城事吧！";
					Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
																												'content' => $content,
																												'receiver' => $uid
																												));					
					$username = $email;
					$password = $password;
					
					//找好友加好友
					Better_User::getInstance($uid)->friends()->autoAddFriend($uid, $protocol . '.com', $service, $third_info);
				}										
				
			}			
			
			//成功页面
			$this->outputS($username, $password);
		} else {
			//不成功
			$this->outputF();
		}		
	}	


	/**
	 * 
	 * @return unknown_type
	 */
	public function _qqsns()
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
				
		$callback = Better_Config::getAppConfig()->base_url . '/api/oauthlogin.xml?';
		//$callback = 'http://k.ai' . '/api/oauthlogin.xml?';
		$c['callback'] = $protocol . '.com';
		$callback .= http_build_query($c);		

		$keys = $o->getRequestToken($callback);
		$_SESSION['oauth_keys'] = $keys;
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);
	}
	
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function _qqsnsCallback()
	{
		$appConfig = Better_Config::getAppConfig();
		$protocol = 'qqsns';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qqsns_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qqsns_skey;			
		
		$keys = $_SESSION['oauth_keys'];
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );				
		$o->protocol = $protocol;
		if ( APPLICATION_ENV != 'production' ) {
//			$o->proxy = 'http://10.10.1.254:1080';
//			$o->proxy_type = CURLPROXY_SOCKS5;
		}		
		$oauth_verifier = $_GET['oauth_vericode'];
		$oauth_token = $_GET['oauth_token'];
		$last_key = $o->getAccessToken($oauth_verifier, $oauth_token );
		
		//  'oauth_signature' => string 'oPVhWWP9pJVzB99JcicZoP9/h5A=' (length=28)
		//  'oauth_token' => string '15377440330878864096' (length=20)
		//  'oauth_token_secret' => string 'EY4qu7CKZVsF4gs9' (length=16)
		//  'openid' => string '103B903ABDF5181A4CFCF885D28B0840' (length=32)
		//  'timestamp' => string '1315985210' (length=10)	
			
		$oauth_token = $last_key['oauth_token'];
		$oauth_token_secret = $last_key['oauth_token_secret'];
		$openid = $last_key['openid'];
		if ($oauth_token && $oauth_token_secret) {
			$service = Better_Service_PushToOtherSites::factory($protocol . '.com', '', $openid, $oauth_token, $oauth_token_secret);
			$third_info = $service->getInfo();
			$id = $openid;
			$email = $id . "@t.qzone.qq.com";
			$password = rand(1000, 9999);
			$bind_user = Better_DAO_ThirdBinding::getBindUser('qqsns.com', $id);			
			if ($bind_user) {
				//生成token
				$tmp = $this->api->loginToken($bind_user['uid']);
				$username = $tmp['username'];
				$password = $tmp['token'];
			} else {
				//检查是否注册过
				$user = Better_User::getInstance($email, 'email');					
				$userInfo = $user->getUserInfo();	
				if( $userInfo['state'] == Better_User_State::BANNED ){
					$this->error('error.users.account_banned');	
					exit;
				}
				$uid = (int)$user->uid;
				if ($uid) {
					$tmp = $this->api->loginToken($uid);
					$username = $tmp['username'];
					$password = $tmp['token'];
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', 'Qzone', $openid, $oauth_token, $oauth_token_secret, $id);						
				} else {
					//注册
					$uid = $this->_regNew($email, $password, $third_info, $protocol);
					
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', 'Qzone', $openid, $oauth_token, $oauth_token_secret, $id);
					//: 发私信告诉密码
					$content = "欢迎使用开开, 您的开开帐号是 {$email},密码是 {$password}。下次您也可以使用此账号直接登录开开。从今天起，记录足迹，分享城事吧！";
					Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
																												'content' => $content,
																												'receiver' => $uid
																												));					
					$username = $email;
					$password = $password;
					
					//找好友加好友
					Better_User::getInstance($uid)->friends()->autoAddFriend($uid, $protocol . '.com', $service, $third_info);
				}										
				
			}			
			//成功页面
			$this->outputS($username, $password);
		} else {
			//不成功
			$this->outputF();
		}		
	}		
	

	/**
	 * 
	 * @return unknown_type
	 */
	public function _163()
	{
		$protocol = '163';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->t163_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->t163_skey;
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		$o->client_type = 'mobile';
		if ( APPLICATION_ENV != 'production' ) {
			$o->proxy = 'http://10.10.1.254:1080';
			$o->proxy_type = CURLPROXY_SOCKS5;
		}
				
		$callback = Better_Config::getAppConfig()->base_url . '/api/oauthlogin.xml?';
		$keys = $o->getRequestToken();
		$_SESSION['oauth_keys'] = $keys;
		
		$c['callback'] = $protocol . '.com';
		$callback .= http_build_query($c);
		
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function _163Callback()
	{
		$appConfig = Better_Config::getAppConfig();
		$protocol = '163';
		$keys = $_SESSION['oauth_keys'];
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->t163_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->t163_skey;
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );
		$o->protocol = $protocol;
		if ( APPLICATION_ENV != 'production' ) {
			$o->proxy = 'http://10.10.1.254:1080';
			$o->proxy_type = CURLPROXY_SOCKS5;
		}		
		$last_key = $o->getAccessToken();
		$oauth_token = $last_key['oauth_token'];
		$oauth_token_secret = $last_key['oauth_token_secret'];
		if ($oauth_token && $oauth_token_secret) {
			$service = Better_Service_PushToOtherSites::factory($protocol . '.com', '', '', $oauth_token, $oauth_token_secret);
			$third_info = $service->getInfo();
			$t163_id = $third_info['id'];
			$t163_id = str_replace('-', '', $t163_id);		
			$email = $t163_id . "@t.163.com";
			$password = substr($t163_id, 0, 4);
			$bind_user = Better_DAO_ThirdBinding::getBindUser('163.com', $t163_id);			

			if ($bind_user) {
				//生成token
				$tmp = $this->api->loginToken($bind_user['uid']);
				$username = $tmp['username'];
				$password = $tmp['token'];
			} else {
				//检查是否注册过
				$user = Better_User::getInstance($email, 'email');					
				$userInfo = $user->getUserInfo();	
				if( $userInfo['state'] == Better_User_State::BANNED ){
					$this->error('error.users.account_banned');	
					exit;
				}
				$uid = (int)$user->uid;
				if ($uid) {
					$tmp = $this->api->loginToken($uid);
					$username = $tmp['username'];
					$password = $tmp['token'];
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', ucfirst($protocol), $protocol, $oauth_token, $oauth_token_secret, $t163_id);						
				} else {
					//注册
					$uid = $this->_regNew($email, $password, $third_info, $protocol);
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', ucfirst($protocol), $protocol, $oauth_token, $oauth_token_secret, $t163_id);
					//: 发私信告诉密码
					$content = "欢迎使用开开, 您的开开帐号是 {$email},密码是 {$password}。下次您也可以使用此账号直接登录开开。从今天起，记录足迹，分享城事吧！";
					Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
																												'content' => $content,
																												'receiver' => $uid
																												));					
					$username = $email;
					$password = $password;
				}										
				
			}			
			
			//成功页面
			$this->outputS($username, $password);
		} else {
			//不成功
			$this->outputF();
		}		
	}	
	
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function _dd()
	{
		$protocol = 'douban';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->douban_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->douban_skey;
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		if ( APPLICATION_ENV != 'production' ) {
			$o->proxy = 'http://10.10.1.254:1080';
			$o->proxy_type = CURLPROXY_SOCKS5;
		}
				
		$callback = Better_Config::getAppConfig()->base_url . '/api/oauthlogin.xml?';
		$keys = $o->getRequestToken();
		$_SESSION['oauth_keys'] = $keys;
		
		$c['callback'] = $protocol . '.com';
		$callback .= http_build_query($c);
		
		$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function _ddCallback()
	{
		$appConfig = Better_Config::getAppConfig();
		$protocol = 'douban';
		$keys = $_SESSION['oauth_keys'];
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->douban_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->douban_skey;
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );
		$o->protocol = $protocol;
		if ( APPLICATION_ENV != 'production' ) {
			$o->proxy = 'http://10.10.1.254:1080';
			$o->proxy_type = CURLPROXY_SOCKS5;
		}		
		$last_key = $o->getAccessToken();
		$oauth_token = $last_key['oauth_token'];
		$oauth_token_secret = $last_key['oauth_token_secret'];
		$douban_user_id = $last_key['douban_user_id'];
		
		if ($oauth_token && $oauth_token_secret) {
			$email = $douban_user_id . "@t.douban.com";
			$password = substr($douban_user_id, 0, 4);
			$bind_user = Better_DAO_ThirdBinding::getBindUser('douban.com', $douban_user_id);			

			if ($bind_user) {
				//生成token
				$tmp = $this->api->loginToken($bind_user['uid']);
				$username = $tmp['username'];
				$password = $tmp['token'];
			} else {
				//检查是否注册过
				$user = Better_User::getInstance($email, 'email');					
				$userInfo = $user->getUserInfo();	
				if( $userInfo['state']==Better_User_State::BANNED ){
					$this->error('error.users.account_banned');	
					exit;
				}
				$uid = (int)$user->uid;
				if ($uid) {
					$tmp = $this->api->loginToken($uid);
					$username = $tmp['username'];
					$password = $tmp['token'];
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', ucfirst($protocol), $protocol, $oauth_token, $oauth_token_secret, $douban_user_id);						
				} else {
					//注册
					$service = Better_Service_PushToOtherSites::factory($protocol . '.com', '', '', $oauth_token, $oauth_token_secret);
					$third_info = $service->getInfo();
					$uid = $this->_regNew($email, $password, $third_info, $protocol);
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', ucfirst($protocol), $protocol, $oauth_token, $oauth_token_secret, $douban_user_id);
					//: 发私信告诉密码
					$content = "欢迎使用开开, 您的开开帐号是 {$email},密码是 {$password}。下次您也可以使用此账号直接登录开开。从今天起，记录足迹，分享城事吧！";
					Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
																												'content' => $content,
																												'receiver' => $uid
																												));					
					$username = $email;
					$password = $password;
				}										
				
			}			
			
			//成功页面
			$this->outputS($username, $password);
		} else {
			//不成功
			$this->outputF();
		}		
	}	
	
	
	/**
	 * 
	 */
	public function _rr()
	{
		$protocol = 'renren';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->renren_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->renren_skey;
		
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		
		$callback = Better_Config::getAppConfig()->base_url . '/api/oauthlogin.xml?';
		$c['callback'] = $protocol . '.com';
		$callback .= http_build_query($c);		
		//display=touch&
		$aurl = "https://graph.renren.com/oauth/authorize?client_id={$WB_AKEY}&display=touch&response_type=code&scope=publish_checkin+publish_feed+photo_upload+status_update&redirect_uri={$callback}";
		$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
	}
	
	
	/**
	 * 
	 */
	public function _rrCallback()
	{
		$appConfig = Better_Config::getAppConfig();
		$protocol = 'renren';
		$username = $password = '';
		
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->renren_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->renren_skey;

		
		$code = $_GET['code'];
		$type = $_GET['type'];
		if ($code) {
			$callback_url = Better_Config::getAppConfig()->base_url . '/api/oauthlogin.xml';
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
				$email = $tid . "@t.renren.com";
				$password = substr($tid, 0, 4);
				
				$bind_user = Better_DAO_ThirdBinding::getBindUser('renren.com', $tid);			
			}
		}	
			
		if ( $access_token && $refresh_token ) {
			//成功
			
			if ($bind_user) {
				//生成token
				$tmp = $this->api->loginToken($bind_user['uid']);
				$username = $tmp['username'];
				$password = $tmp['token'];
			} else {
				//检查是否注册过
				$user = Better_User::getInstance($email, 'email');					
				$userInfo = $user->getUserInfo();	
				if($userInfo['state']==Better_User_State::BANNED){
					$this->error('error.users.account_banned');	
					exit;
				}
				$uid = (int)$user->uid;
				if ($uid) {
					$tmp = $this->api->loginToken($uid);
					$username = $tmp['username'];
					$password = $tmp['token'];
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', ucfirst($protocol), $protocol, $access_token, $refresh_token, $tid, $expires_in);						
				} else {
					//注册
					$service = Better_Service_PushToOtherSites::factory($protocol . '.com', '', '', $access_token, $refresh_token);
					$third_info = $service->getInfo();
					$uid = $this->_regNew($email, $password, $third_info, $protocol);
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', ucfirst($protocol), $protocol, $access_token, $refresh_token, $tid, $expires_in);
					//: 发私信告诉密码
					$content = "欢迎使用开开, 您的开开帐号是 {$email},密码是 {$password}。下次您也可以使用此账号直接登录开开。从今天起，记录足迹，分享城事吧！";
					Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
																												'content' => $content,
																												'receiver' => $uid
																												));					
					$username = $email;
					$password = $password;
					
					
					//vip
					if ( $third_info['is_vip'] ) {
						$msg = "第三方认证用户加入开开  http://k.ai/kai" . $uid;
						$service->sendVipMail($msg);								
					}					
					
					//找好友加好友
					Better_User::getInstance($uid)->friends()->autoAddFriend($uid, $protocol . '.com', $service, $third_info);					
				}										
				
			}
			$this->outputS($username, $password);
		} else {
			//不成功
			$this->outputF();
		}				
	}	
	
	/**
	 * 
	 */
	public function outputS($username, $password)
	{
		$t = base64_encode("$username:$password");
		$output = Better_Config::getAppConfig()->base_url . '/api/oauthlogin.xml?aa=output&logined=true&t=' . $t;
		$this->_helper->getHelper('Redirector')->gotoUrl($output);
		exit;
	}
	
	
	/**
	 * 
	 */
	public function outputF()
	{
		$output = Better_Config::getAppConfig()->base_url . '/api/oauthlogin.xml?aa=output&logined=false';	
		$this->_helper->getHelper('Redirector')->gotoUrl($output);
		exit;
	}
	
	
	/**
	 * 
	 */
	public function outputoutput($logined)
	{
		$i = $logined == 'true' ? 's' : 'f';
		
		$html = <<<EOT
<html>
<head>
<style type="text/css">
body
{ 
  background-color:#CEEBFF;
  background-image:url('/images/3rdSite/oauth_$i.png');
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
	
	/**
	 * 注册新用户
	 * @return unknown_type
	 */
	public function _regNew($email, $password, $third_info, $protocol='renren')
	{
		$temnickname = split('@', $email);
		$nickname = strlen($third_info['nickname'])>0 ? $third_info['nickname'] : $temnickname[0];
		//$nickname = mb_substr($nickname, 0, 6, 'UTF-8');
		$nickname = substr($nickname, 0, 15);
		$nickname = str_pad($nickname, 5, "-", STR_PAD_RIGHT);  
	
		$checkid = Better_User_Signup::getInstance(10000)->validNickname($nickname, 0);
		switch ($checkid['code']) {
			case $checkid['codes']['VALID']:
				break;
			case $checkid['codes']['NICKNAME_EXISTS']:
				$i = 0;														
				while($i==0 || $checkid['code'] != $checkid['codes']['VALID']){
					$attemp = $nickname . "_" . rand(1, 99);
					$checkid = Better_User_Signup::getInstance(10000)->validNickname($attemp, 0);														
					$i++;	
					if ($i > 100) {
						$attemp = '';
						break;
					}							
				}
				
				$nickname = $attemp;
				break;
			default:
				$nickname = '';
				break;
		}
		$data['email'] = $email;
	    $data['nickname'] = $nickname;
	    $data['password'] = $password;
	    $data['partner'] = $_SESSION['oauth']['partner'];
	    $data['secret'] = $_SESSION['oauth']['secret'];
		$uid = Better_User_Signup::Signup($data);
		$result = 'true';
		$user = Better_User::getInstance($email, 'email');
		//设置头像
		$avatarurl = $third_info['image_url'];
		if ($avatarurl) {
			$user->avatar()->upload($avatarurl, 'jpg', $uid);
		}							
	

		return $uid;
	}
	
}