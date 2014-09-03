<?php

/**
 * Oauth 客户端oauth 登录
 * 
 * @package Controllers
 * @author fengj <fengj@peptalk.cn>
 *
 */
class Public_OauthloginController extends Better_Controller_Public
{
	public $SNS = array('douban.com');
	
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
		
		$sns = $this->getRequest()->getParam('sns', 'douban.com');
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
		
		$_SESSION['cb'] = $this->getRequest()->getParam('cb', ''); //
		if ('douban.com' == $sns ) {
			$this->_dd();
		} else {
			exit;	
		}

		exit;
	}
	
	
	/**
	 * 
	 */
	public function _callback($callback )
	{
		if ( $callback == 'douban.com' )	{
			$this->_ddCallback();
		}
		exit;		
	}
	
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function _dd()
	{
		$key = $this->getRequest()->getParam('key', '');
		$protocol = 'douban';
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->douban_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->douban_skey;
		$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
		$o->protocol = $protocol;
		$callback = Better_Config::getAppConfig()->base_url_open . '/public/oauthlogin.xml?';
		$keys = $o->getRequestToken();
		$_SESSION['oauth_keys'] = $keys;
		
		$c['key'] = $key;
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
		$key = $this->getRequest()->getParam('key', '');
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
			$email = $douban_user_id . "@douban.com";
			$password = substr($douban_user_id, 0, 4);
			$bind_user = Better_DAO_ThirdBinding::getBindUser('douban.com', $douban_user_id);			

			$service = Better_Service_PushToOtherSites::factory($protocol . '.com', '', '', $oauth_token, $oauth_token_secret);
			$third_info = $service->getInfo();			
			
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
					$uid = $this->_regNew($email, $password, $third_info, $protocol);
					Better_User::getInstance($uid)->syncsites()->add( $protocol . '.com', ucfirst($protocol), $protocol, $oauth_token, $oauth_token_secret, $douban_user_id);
					//: 发私信告诉密码
					$content = "欢迎使用开开, 您的开开帐号是 {$email},密码是 {$password}。下次您也可以使用此账号直接登录开开。从今天起，记录足迹，分享城事吧！";
//					Better_User_Notification_DirectMessage::getInstance($appConfig->user->sys_user_id)->send(array(
//																												'content' => $content,
//																												'receiver' => $uid
//																												));					
					$username = $email;
					$password = $password;
				}										
				
			}			
			
			//成功页面			
			$this->outputS($username, $password, $oauth_token, $oauth_token_secret, $douban_user_id, $third_info);
		} else {
			//不成功
			$this->outputF();
		}		
	}
	
	
	/**
	 * 
	 */
	public function outputS($username, $password, $oauth_token, $oauth_token_secret, $douban_user_id, $third_info)
	{
		$email = "$douban_user_id@douban.com";
		
		$key = $this->getRequest()->getParam('key', '');
		$t = base64_encode("$username:$password:$oauth_token:$oauth_token_secret:$email:{$third_info['nickname']}");
		$output = Better_Config::getAppConfig()->base_url_open . '/public/oauthlogin.xml?aa=output&logined=true&t=' . $t . '&key=' . $key;
		if ($_SESSION['cb']) {
			$output = $_SESSION['cb'] . '?logined=true&t=' . urlencode($t);
		}
		
		$this->_helper->getHelper('Redirector')->gotoUrl($output);
		exit;
	}
	
	
	/**
	 * 
	 */
	public function outputF()
	{
		$key = $this->getRequest()->getParam('key', '');
		$output = Better_Config::getAppConfig()->base_url_open . '/public/oauthlogin.xml?aa=output&logined=false&key=' . $key;
		if ( $_SESSION['cb'] ) {
			$output = $_SESSION['cb'] . '?aa=output&logined=false';
		}
			
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
		$nickname = mb_substr($nickname, 0, 6, 'UTF-8');
	
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