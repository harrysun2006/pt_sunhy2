<?php

/**
 * 登录控制器
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class LoginController extends Better_Controller_Front 
{

	public function init()
	{
		parent::init();
		$this->commonMeta();

		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/login.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
		
		//$this->view->css = 'index';
	}
	
	/**
	 * 登录操作
	 *
	 * @return null
	 */
	public function indexAction()
	{
		define('BETTER_PARTNER', 'web');
		$this->view->ref_url = $this->getRequest()->getParam('ref_url', '');
		$login_from = $this->getRequest()->getParam('login_from', '');
		Better_Log::getInstance()->logInfo($login_from,'signuplogin');
		if($login_from){
			$this->view->headScript()->prependScript("
    	var Better_Login_From = '".addslashes($login_from)."';
    	");
		}
		
		if (!Better_Registry::get('sess')->get('gotoref') &&  strpos($_SERVER['HTTP_REFERER'], BETTER_BASE_URL)!==false){
			Better_Registry::get('sess')->set('gotoref',$_SERVER['HTTP_REFERER']);	
			
		}
	
		$this->view->midhadlogin = 1;
		if ($this->getRequest()->isPost()) {
			$filters = array(
				'email' => 'StringTrim',
				);
			$validation = array(
				'email' => array(
									array('StringLength', 4, 50),
								),
				);
			$post = $this->getRequest()->getPost();

			$zfi = new Zend_Filter_Input($filters, $validation, $post);
			$pwdMd5 = (isset($post['pwd_plain']) && $post['pwd_plain']=='1') ? false : false;
			$loginMsg = '';

			if ($zfi->isValid() && $post['password']!='') {

				$remember = (isset($post['rememberme']) && $post['rememberme']=='1') ? true : false;
				$login_type = isset($post['login_type']) && $post['login_type'] != 'bedobind' ? $post['login_type']: 'local';				
				$result = Better_User_Login::newlogin($post['email'], $post['password'],$login_type, $pwdMd5, $remember);

				switch ($result) {
					case Better_User_Login::INVALID_PWD:
						if (eregi('^[0-9]+$',$post['email'])) {
							$bedoInfo = Better_Service_BedoBinding::getInstance()->login($post['email'], $post['password']);
							if ($bedoInfo['kaiPass'] && $bedoInfo['kaiEmail']) {
								if ($post['login_type'] == 'bedobind') {
									$_SESSION['bedobindretry'] = 1;
									$this->_helper->getHelper('Redirector')->gotoUrl('/signup/bedosignup');
								} else {
									Better_User_Login::autoLogin($bedoInfo['kaiEmail'], $bedoInfo['kaiPass'],$remember);
									$this->_helper->getHelper('Redirector')->gotoUrl('/home');
								}
							} else if (is_array($bedoInfo)) {
								$result = Better_User_Login::BEDO_LOGIN;
							} else {
						$loginMsg = $this->lang->error->login->password_incorrect;
								$result = Better_User_Login::INVALID_PWD;
							}
						} else if ($post['login_type'] == 'bedobind') {
							 $_SESSION['bedobinderrorpass'] = 1;
							 $this->_helper->getHelper('Redirector')->gotoUrl('/signup/bedosignup');
						} else {
							$loginMsg = $this->lang->error->login->password_incorrect;
						}
						break;
					case Better_User_Login::NEED_VALIDATED:
						$loginMsg = $this->lang->error->login->account_not_actived;
						break;
					case Better_User_Login::ACCOUNT_BANNED:
						$loginMsg = $this->lang->error->login->account_banned;
						break;
					case Better_User_Login::FORCE_VALIDATING:
						$loginMsg = $this->lang->error->login->force_validating;
						break;
					default:
						$loginMsg = $this->lang->error->login->unknown;
						break;
				}
			} else {
				$loginMsg = $this->lang->error->login->password_incorrect;
			}			
			if ($result == Better_User_Login::BEDO_LOGIN) {
				$_SESSION['bedojid'] = $post['email'];
				$_SESSION['bedopass'] = $post['password'];
				$this->_helper->getHelper('Redirector')->gotoUrl('/signup/bedosignup');
				
				$ref_url = $post['ref_url'] ? $post['ref_url'] : Better_Registry::get('sess')->get('ref_url');
				$ref_url = base64_decode($ref_url);
			} else if ($result==Better_User_Login::LOGINED) {
				$ref_url = $post['ref_url'] ? $post['ref_url'] : Better_Registry::get('sess')->get('ref_url');
				$ref_url = base64_decode($ref_url);
				
				if ($login_type=='sina' || $login_type=='sohu' || $login_type=='kaixin001') {
					Better_Log::getInstance()->logInfo($login_type.'|'.$post['email'], 'partner_login', true);
				}
				$uid = Better_Registry::get('sess')->get('uid');	
				$synclist = Better_User_Syncsites::getInstance($uid)->getSites();			
				if($uid){
					$signupbythrid = 0;
					if(Better_Registry::get('sess')->get('qqdobind') || Better_Registry::get('sess')->get('163dobind') || Better_Registry::get('sess')->get('renrendobind') || Better_Registry::get('sess')->get('doubandobind')){						
						$protocol_site = Better_Registry::get('sess')->get('protocol_site');
						$protocol_username = Better_Registry::get('sess')->get('protocol_username');
						$protocol_callback = Better_Registry::get('sess')->get('protocol_callback');
						$protocol_oauthtoken = Better_Registry::get('sess')->get('protocol_oauthtoken');
						$protocol_oauthtokensecret = Better_Registry::get('sess')->get('protocol_oauthtokensecret');
						$protocol_tid = Better_Registry::get('sess')->get('protocol_tid');				
						$expires_in = 0;
						if(Better_Registry::get('sess')->get('expires_in')){
							$expires_in = Better_Registry::get('sess')->get('expires_in');
						}	
						Better_User_Syncsites::getInstance($uid)->add($protocol_site, $protocol_username, $protocol_callback, $protocol_oauthtoken, $protocol_oauthtokensecret,$protocol_tid,$expires_in);	
						Better_Registry::get('sess')->set('qqdobind','');
						Better_Registry::get('sess')->set('163dobind','');		
						Better_Registry::get('sess')->set('renrendobind','');
						Better_Registry::get('sess')->set('doubandobind','');
						$signupbythrid = 1;				
					} else if(Better_Registry::get('sess')->get('snstothird')){		
						Better_Log::getInstance()->logInfo($uid,'step2sync');	
						$protocol_site = Better_Registry::get('sess')->get('protocol_site');
						$protocol_oauthtoken = '';
						$protocol_oauthtokensecret= '';
						if($protocol_site=='sina.com'){
							$protocol_oauthtoken = Better_Registry::get('sess')->get('protocol_oauthtoken');
							$protocol_oauthtokensecret = Better_Registry::get('sess')->get('protocol_oauthtokensecret');
						}
						$expires_in =0;
						$protocol_username = Better_Registry::get('sess')->get('protocol_email');
						$protocol_password = Better_Registry::get('sess')->get('protocol_password');
						$protocol_tid = Better_Registry::get('sess')->get('protocol_tid');			
						Better_User_Syncsites::getInstance($uid)->add($protocol_site, $protocol_username, $protocol_password,$protocol_oauthtoken,$protocol_oauthtokensecret,$protocol_tid,$expires_in);	
						Better_Registry::get('sess')->set('snstothird','');	
						$signupbythrid = 1;						
					}
					if($signupbythrid){
						if(isset($synclist[$protocol_site])){						
							Better_User_Syncsites::getInstance($uid)->updatesms($protocol_site);							
						}	
						Better_User::getInstance($uid)->updateUser(array(
							'state' => Better_User_State::ENABLED
							));
					}
				}
				
				if ($post['login_type'] == 'bedobind' && $login_type == 'local') {
					$return = Better_Service_BedoBinding::getInstance()->bind($uid, $_SESSION['bedojid'], $_SESSION['bedopass']);
					if ($return == 'ok') {
						$_SESSION['showbedofriends'] = 1;
						$this->_helper->getHelper('Redirector')->gotoUrl('/setting/bedoimport');
						exit(0);
					} else {
						Better_Registry::get('sess')->set('uid');
						Better_Registry::get('sess')->set('user');
						Better_User_AutoLogin::clear();
						$_SESSION['bedobindretry'] = 1;
						$this->_helper->getHelper('Redirector')->gotoUrl('/signup/bedosignup/');
						exit(0);
					}
				}
				if(Better_Registry::get('sess')->get('gotoref')){
					$refurl = Better_Registry::get('sess')->get('gotoref');
					Better_Registry::get('sess')->set('gotoref','');
					try{
						$this->_helper->getHelper('Redirector')->gotoUrl($refurl);
					} catch (Exception $refurlbug){
						
					}
				}
				$this->_helper->getHelper('Redirector')->gotoUrl('/home');
				//}
			} else if($result==Better_User_Login::JUMP_SIGNUP){
				$this->_helper->getHelper('Redirector')->gotoUrl('/signup');
			} else if($result==Better_User_Login::JUMP_STEP2){
				$this->_helper->getHelper('Redirector')->gotoUrl('/signup/step2');
			} else if($result==Better_User_Login::Thrid_False){
				$loginMsg = str_replace('{THIRDPARTY}',$login_type,$this->lang->login->third_login->falseinfo);
				$this->view->headScript()->prependScript("
    	var Better_LoginMsg = '".addslashes($loginMsg)."';
    	");
			} else {
				$this->view->headScript()->prependScript("
    	var Better_LoginMsg = '".addslashes($loginMsg)."';
    	");
			}
	
		}
		
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function thirdqqsnsAction()
	{
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qqsns_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qqsns_skey;
		$callback = $this->getRequest()->getParam('callback', '');
		$atkai = $this->getRequest()->getParam('atkai', '');
		if($callback){
			$loginfrom = 'qqsns.com';
			$keys = $_SESSION['oauth_keys'];
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );				
			$o->protocol = 'qqsns';
			
			$oauth_verifier = $_GET['oauth_vericode'];
			$oauth_token = $_GET['oauth_token'];
			$last_key = $o->getAccessToken($oauth_verifier, $oauth_token);
			$oauth_token = $last_key['oauth_token'];
			$oauth_token_secret = $last_key['oauth_token_secret'];
			$openid = $last_key['openid'];
			
			$_name = $openid;
			if(!$_name){
				$this->_helper->getHelper('Redirector')->gotoUrl('/login');
			}
			
			$protocol = $loginfrom;
			$service = Better_Service_PushToOtherSites::factory($protocol, $_name, $openid, $oauth_token, $oauth_token_secret);		
			$third_info = $service->getInfo();
			
			$qqnick = $third_info['nickname'];
			$avatarurl = $third_info['image_url'];
			$tid = $openid;	
			$username = &$_name;
			
			$userlist = Better_DAO_ThirdBinding::getBindUser('qqsns.com', $openid);
			if(is_array($userlist) && $userlist['uid']>0){
				$uid = $userlist['uid'];			
				$userInfo = Better_user::getInstance($uid)->getUserInfo();	
				if($userInfo['state']==Better_User_State::BANNED){									
					$yourbanned = 1;
				}						
				if (!$yourbanned) {	
					Better_Registry::get('sess')->set('uid', $uid);
					Better_User_AutoLogin::getInstance($uid)->putCookie();
					Better_Registry::get('sess')->stick();	
				}
				$this->_helper->getHelper('Redirector')->gotoUrl('/home');	
			} else {
				try {
					$testemail = $openid . "@t.qzone.qq.com";
					$testuser = Better_User::getInstance($testemail, 'email');					
					$testuserInfo = $testuser->getUserInfo();
					if($testuserInfo['uid']>0){
						$uid = $testuserInfo['uid'];
						Better_User::getInstance($uid)->syncsites()->add( $protocol, 'Qzone', $openid, $oauth_token, $oauth_token_secret, $openid);
						Better_Registry::get('sess')->set('uid', $testuserInfo['uid']);
						Better_User_AutoLogin::getInstance($testuserInfo['uid'])->putCookie();
						Better_Registry::get('sess')->stick();				
						$this->_helper->getHelper('Redirector')->gotoUrl('/home');
					}
				} catch(Exception $e){
					
				}				
				Better_Registry::get('sess')->set('qqsnsdobind', 1);
				Better_Registry::get('sess')->set('protocol_site', 'qqsns.com');
				Better_Registry::get('sess')->set('protocol_tid', $openid);
				Better_Registry::get('sess')->set('protocol_username', $openid);
				Better_Registry::get('sess')->set('protocol_nickname', $qqnick);
				Better_Registry::get('sess')->set('protocol_callback', $callback);
				Better_Registry::get('sess')->set('protocol_oauthtoken', $oauth_token);
				Better_Registry::get('sess')->set('protocol_oauthtokensecret', $oauth_token_secret);
				Better_Registry::get('sess')->set('avatarurl', $avatarurl);
				$this->_helper->getHelper('Redirector')->gotoUrl('/signup/partnersignup');	
			} 
			
		} else {
			$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qqsns_akey;
			$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qqsns_skey;
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$target = 'qqsns';
			$o->protocol = $target;
			$callback = Better_Config::getAppConfig()->base_url . '/login/thirdqqsns';
			$callback .= "?callback=$target";
			//$callback = "http://k.ai/setting/sync?callback=$target";
			if (APPLICATION_ENV!='production') {
				//$o->proxy = 'http://10.10.1.254:808';
			}		
			if($atkai){
				$_SESSION['atkai'] =1;
			}	
			$keys = $o->getRequestToken($callback);
			$_SESSION['oauth_keys'] = $keys;
			$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
			
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
		}	
	}	
	
	
	public function thirdqqAction(){
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qq_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qq_skey;
		$callback = $this->getRequest()->getParam('callback', '');
		$atkai = $this->getRequest()->getParam('atkai', '');
		if($callback){
			$loginfrom = 'qq.com';
			$keys = $_SESSION['oauth_keys'];
			Better_Log::getInstance()->logInfo(serialize($keys)."--".$_GET['oauth_verifier'],'qq500');
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );				
			$o->protocol = $callback;
			
			if (APPLICATION_ENV!='production') {
				//$o->proxy = 'http://10.10.1.254:808';
			}
			$oauth_verifier = $_GET['oauth_verifier'];
			$oauth_token = $_GET['oauth_token'];
			$last_key = $o->getAccessToken($oauth_verifier, $oauth_token);
			$oauth_token = $last_key['oauth_token'];
			$oauth_token_secret = $last_key['oauth_token_secret'];
			Better_Log::getInstance()->logInfo(serialize($last_key),'qq500');
			$_name = $last_key['name'];
			if(!$_name){
				$this->_helper->getHelper('Redirector')->gotoUrl('/login');
			}
			$protocol = $loginfrom;
			$thirdinfo = Better_Service_PushToOtherSites::factory($protocol, $_name, 'qq', $oauth_token, $oauth_token_secret);		
			$qqnick = $thirdinfo->getThirdUserNick($_name, 'qq', $oauth_token, $oauth_token_secret);
			$tid = $thirdinfo->tid;	
			$avatarurl = $thirdinfo->getAvatar();
			if($_SESSION['atkai']){
				$useratkai = $thirdinfo->friendsAdd();
				$_SESSION['atkai'] = '';
			}					
			$username = &$_name;
			
			$userlist = Better_DAO_SyncQueue::getSyncbysiteuser($loginfrom,$username);
			if(is_array($userlist) && $userlist['uid']>0){
				$uid = $userlist['uid'];			
				$userInfo = Better_user::getInstance($uid)->getUserInfo();	
				if($userInfo['state']==Better_User_State::BANNED){									
					$yourbanned = 1;
				}						
				if (!$yourbanned) {	
					Better_Registry::get('sess')->set('uid', $uid);
					Better_User_AutoLogin::getInstance($uid)->putCookie();
					Better_Registry::get('sess')->stick();	
				}
				$this->_helper->getHelper('Redirector')->gotoUrl('/home');	
			} else {
				try {
					$testemail = $username."@t.".$loginfrom;
					$testuser = Better_User::getInstance($testemail, 'email');					
					$testuserInfo = $testuser->getUserInfo();
					if($testuserInfo['uid']>0){
						Better_Registry::get('sess')->set('uid', $testuserInfo['uid']);
						Better_User_AutoLogin::getInstance($testuserInfo['uid'])->putCookie();
						Better_Registry::get('sess')->stick();				
						$this->_helper->getHelper('Redirector')->gotoUrl('/home');
					}
				} catch(Exception $e){
					
				}				
				Better_Registry::get('sess')->set('qqdobind', 1);
				Better_Registry::get('sess')->set('protocol_site', 'qq.com');
				Better_Registry::get('sess')->set('protocol_tid', $username);
				Better_Registry::get('sess')->set('protocol_username', $username);
				Better_Registry::get('sess')->set('protocol_nickname', $qqnick);
				Better_Registry::get('sess')->set('protocol_callback', $callback);
				Better_Registry::get('sess')->set('protocol_oauthtoken', $oauth_token);
				Better_Registry::get('sess')->set('protocol_oauthtokensecret', $oauth_token_secret);
				Better_Registry::get('sess')->set('avatarurl', $avatarurl);
				$this->_helper->getHelper('Redirector')->gotoUrl('/signup/partnersignup');	
			} 
			
		} else {
			$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qq_akey;
			$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qq_skey;
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$target = 'qq';
			$o->protocol = $target;
			$callback = Better_Config::getAppConfig()->base_url . '/login/thirdqq';
			$callback .= "?callback=$target";
			//$callback = "http://k.ai/setting/sync?callback=$target";
			if (APPLICATION_ENV!='production') {
				//$o->proxy = 'http://10.10.1.254:808';
			}		
			if($atkai){
				$_SESSION['atkai'] =1;
			}	
			$keys = $o->getRequestToken($callback);
			$_SESSION['oauth_keys'] = $keys;
			$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
			
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
		}	
	}
	
	public function third163Action(){
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->t163_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->t163_skey;
		$callback = $this->getRequest()->getParam('callback', '');
		if($callback){
			$loginfrom = '163.com';
			$keys = $_SESSION['oauth_keys'];
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );				
			$o->protocol = $callback;
			
			if (APPLICATION_ENV!='production') {
				//$o->proxy = 'http://10.10.1.254:808';
			}
			$oauth_verifier = $_GET['oauth_verifier'];
			$oauth_token = $_GET['oauth_token'];
			$last_key = $o->getAccessToken($oauth_verifier, $oauth_token );
			$oauth_token = $last_key['oauth_token'];
			$oauth_token_secret = $last_key['oauth_token_secret'];			
			$protocol = $loginfrom;			
			$thirdinfo = Better_Service_PushToOtherSites::factory($protocol,'xxa', '163.com', $oauth_token, $oauth_token_secret);		
			$t163info = $thirdinfo->getUserInfo();
			$username = $t163info['id'];
			$nickname = $t163info['name'];
			
			$userlist = Better_DAO_SyncQueue::getSyncbysiteuser($loginfrom,$username);
			if(is_array($userlist) && $userlist['uid']>0){
				$uid = $userlist['uid'];			
				$userInfo = Better_user::getInstance($uid)->getUserInfo();	
				if($userInfo['state']==Better_User_State::BANNED){									
					$yourbanned = 1;
				}						
				if (!$yourbanned) {	
					Better_Registry::get('sess')->set('uid', $uid);
					Better_User_AutoLogin::getInstance($uid)->putCookie();
					Better_Registry::get('sess')->stick();	
				}
				$this->_helper->getHelper('Redirector')->gotoUrl('/home');	
			} else {	
				try {
					$testemail = $username."@t.".$loginfrom;
					$testuser = Better_User::getInstance($testemail, 'email');					
					$testuserInfo = $testuser->getUserInfo();
					$expires_in = 0;			
					if($testuserInfo['uid']>0){
						Better_User_Syncsites::getInstance($testuserInfo['uid'])->add($loginfrom, $username, $username, $oauth_token, $oauth_token_secret,$username,$expires_in);	
						Better_Registry::get('sess')->set('uid', $testuserInfo['uid']);
						Better_User_AutoLogin::getInstance($testuserInfo['uid'])->putCookie();
						Better_Registry::get('sess')->stick();				
						$this->_helper->getHelper('Redirector')->gotoUrl('/home');
					}
				} catch(Exception $e){
					
				}
				Better_Registry::get('sess')->set('163dobind', 1);
				Better_Registry::get('sess')->set('protocol_site', '163.com');
				Better_Registry::get('sess')->set('protocol_tid', $username);
				Better_Registry::get('sess')->set('protocol_username', $username);
				Better_Registry::get('sess')->set('protocol_nickname', $nickname);
				Better_Registry::get('sess')->set('protocol_callback', $callback);
				Better_Registry::get('sess')->set('protocol_oauthtoken', $oauth_token);
				Better_Registry::get('sess')->set('protocol_oauthtokensecret', $oauth_token_secret);
				$this->_helper->getHelper('Redirector')->gotoUrl('/signup/partnersignup');	
			} 
			
		} else {			
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$target = '163';
			$o->protocol = $target;
			$callback = Better_Config::getAppConfig()->base_url . '/login/third163';
			$callback .= "?callback=$target";
			//$callback = "http://k.ai/setting/sync?callback=$target";
			if (APPLICATION_ENV!='production') {
				//$o->proxy = 'http://10.10.1.254:808';
			}		
			$keys = $o->getRequestToken($callback);
			$_SESSION['oauth_keys'] = $keys;
			$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
			
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
		}	
	}
	
	
	public function thirdsinaAction(){
		
		$callback = $this->getRequest()->getParam('callback', '');
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->sina_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->sina_skey;	
		$atkai = $this->getRequest()->getParam('atkai', '');		
		if($callback){			
			$loginfrom = 'sina.com';	
			$keys = $_SESSION['oauth_keys'];
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );		
			$o->protocol = $callback;		
			$oauth_verifier = $_GET['oauth_verifier'];
			$oauth_token = $_GET['oauth_token'];
			
			$last_key = $o->getAccessToken($oauth_verifier, $oauth_token );
			$oauth_token = $last_key['oauth_token'];
			$oauth_token_secret = $last_key['oauth_token_secret'];
			$tid = $last_key['user_id'];			
			$protocol = $loginfrom;
			
			$thirdinfo = Better_Service_PushToOtherSites::factory($protocol, $tid, 'sina', $oauth_token, $oauth_token_secret,$tid);	
			$tempinfo = $thirdinfo->verify_credentials();
			if(!$tempinfo){
				$this->_helper->getHelper('Redirector')->gotoUrl('/login');	
			}
			$sinainfo = $thirdinfo->getInfo();					
			$username = $thirdinfo->tid;
			if($_SESSION['atkai']){
				$useratkai = $thirdinfo->followKai();
				$_SESSION['atkai'] = '';
			}
			$nickname = $sinainfo['nickname'];
			$avatarurl = $sinainfo['image_url'];			
			$tid = $thirdinfo->tid;	
			$tempinfo && $tid && $bindlist = Better_DAO_ThirdBinding::getBindUser($loginfrom, $tid);
			if($_SESSION['sina_username']){
				$userlist = Better_DAO_SyncQueue::getSyncbysiteuser($loginfrom,$_SESSION['sina_username']);
			}
			if((is_array($userlist) && $userlist['uid']>0) || $bindlist){
				$uid = $userlist['uid']? $userlist['uid']:$bindlist['uid'];							
				$userInfo = Better_user::getInstance($uid)->getUserInfo();	
				if($userInfo['state']==Better_User_State::BANNED){									
					$yourbanned = 1;
				}						
				if (!$yourbanned) {	
					Better_Registry::get('sess')->set('uid', $uid);
					Better_User_AutoLogin::getInstance($uid)->putCookie();
					Better_Registry::get('sess')->stick();	
				}
				$this->_helper->getHelper('Redirector')->gotoUrl('/home');	
			} else {
				try {
					$testemail = $tid."@t.".$loginfrom;
					$testuser = Better_User::getInstance($testemail, 'email');					
					$testuserInfo = $testuser->getUserInfo();
					$expires_in = 0;
					if($testuserInfo['uid']>0){
						Better_User_Syncsites::getInstance($testuserInfo['uid'])->add($loginfrom, $tid, $tid, $oauth_token, $oauth_token_secret,$tid,$expires_in);	
						Better_Registry::get('sess')->set('uid', $testuserInfo['uid']);
						Better_User_AutoLogin::getInstance($testuserInfo['uid'])->putCookie();
						Better_Registry::get('sess')->stick();				
						$this->_helper->getHelper('Redirector')->gotoUrl('/home');
					}
				} catch(Exception $e){
					
				}	
				$third_info = $thirdinfo->getInfo();
				$avatarurl = $thirdinfo->getAvatar();	
				$tid = $thirdinfo->tid;						
				$username = $_SESSION['sina_username'];
				$password = $_SESSION['sina_password'];
				$nickname = $third_info['nickname'];									
				Better_Registry::get('sess')->set('snstothird', 1);
				Better_Registry::get('sess')->set('protocol_tid', $tid);
				Better_Registry::get('sess')->set('protocol_site', $loginfrom);
				Better_Registry::get('sess')->set('protocol_email', $tid);
				Better_Registry::get('sess')->set('protocol_username', $tid);
				Better_Registry::get('sess')->set('protocol_nickname', $nickname);
				Better_Registry::get('sess')->set('protocol_password', 'sina');
				Better_Registry::get('sess')->set('avatarurl', $avatarurl);				
				Better_Registry::get('sess')->set('protocol_oauthtoken', $oauth_token);
				Better_Registry::get('sess')->set('protocol_oauthtokensecret', $oauth_token_secret);					
				$this->_helper->getHelper('Redirector')->gotoUrl('/signup/partnersignup');	
			} 
			
		} else {			
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$target = 'sina';
			$o->protocol = $target;
			$callback = Better_Config::getAppConfig()->base_url . '/login/thirdsina';
			$callback .= "?callback=$target";
			if($atkai){
				$_SESSION['atkai'] =1;
			}				
			$keys = $o->getRequestToken($callback);
			$_SESSION['oauth_keys'] = $keys;
			$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
		
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
		}
		
	}
	
			
			
	public function thirdrenrenAction(){
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->renren_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->renren_skey;
		$callback = $this->getRequest()->getParam('callback', '');
		if($callback){
			$loginfrom = 'renren.com';
			$code = $this->getRequest()->getParam('code', '');
			$type = $this->getRequest()->getParam('type', '');			
			if ($code) {
				$callback_url = Better_Config::getAppConfig()->base_url . '/login/thirdrenren';
				$callback_url .= "?callback=$callback&type=token";				
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
					$oauth_token = $access_token;
					$oauth_token_secret = $refresh_token;					
					//$this->user->syncsites()->add( $callback . '.com', ucfirst($callback), $callback, $access_token, $refresh_token, $tid, $expires_in);
				}

			}
			$protocol = $loginfrom;			
			$thirdinfo = Better_Service_PushToOtherSites::factory($protocol,$tid, 'renren.com', $oauth_token, $oauth_token_secret);	
			
		    $login = $thirdinfo->fakeLogin(); 
				
		   // $r = $service->getUserInfo();	
			$trenreninfo = $thirdinfo->getUserInfo();
			$username = $trenreninfo['id'];
			$nickname = $trenreninfo['name'];
			$avatarurl = $trenreninfo['profile_image_url'];
			
			$bindlist = Better_DAO_ThirdBinding::getBindUser($loginfrom, $username);
			$userlist = Better_DAO_SyncQueue::getSyncbysiteuser($loginfrom,$username);
			if((is_array($userlist) && $userlist['uid']>0) || $bindlist){
				$uid = $userlist['uid']? $userlist['uid']:$bindlist['uid'];			
				$userInfo = Better_user::getInstance($uid)->getUserInfo();	
				if($userInfo['state']==Better_User_State::BANNED){									
					$yourbanned = 1;
				}						
				if (!$yourbanned) {	
					Better_Registry::get('sess')->set('uid', $uid);
					Better_User_AutoLogin::getInstance($uid)->putCookie();
					Better_Registry::get('sess')->stick();	
				}
				$this->_helper->getHelper('Redirector')->gotoUrl('/home');			
			} else {
				try {
					$testemail = $username."@t.".$loginfrom;
					$testuser = Better_User::getInstance($testemail, 'email');					
					$testuserInfo = $testuser->getUserInfo();			
					if($testuserInfo['uid']>0){
						Better_User_Syncsites::getInstance($testuserInfo['uid'])->add($loginfrom, $username, $username, $oauth_token, $oauth_token_secret,$tid,$expires_in);	
						Better_Registry::get('sess')->set('uid', $testuserInfo['uid']);
						Better_User_AutoLogin::getInstance($testuserInfo['uid'])->putCookie();
						Better_Registry::get('sess')->stick();				
						$this->_helper->getHelper('Redirector')->gotoUrl('/home');
					}
				} catch(Exception $e){
					
				}
				Better_Registry::get('sess')->set('renrendobind', 1);
				Better_Registry::get('sess')->set('protocol_site', 'renren.com');
				Better_Registry::get('sess')->set('protocol_tid', $username);
				Better_Registry::get('sess')->set('protocol_username', $username);
				Better_Registry::get('sess')->set('protocol_nickname', $nickname);
				Better_Registry::get('sess')->set('protocol_callback', $callback);
				Better_Registry::get('sess')->set('protocol_oauthtoken', $oauth_token);
				Better_Registry::get('sess')->set('expires_in', $expires_in);
				Better_Registry::get('sess')->set('protocol_oauthtokensecret', $oauth_token_secret);
				Better_Registry::get('sess')->set('avatarurl', $avatarurl);
				$this->_helper->getHelper('Redirector')->gotoUrl('/signup/partnersignup');	
			}		
			
		} else {			
		//	$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$target = 'renren';;
			//$o->protocol = $target;
			$callback = "http://k.ai" . '/login/thirdrenren';
			//$callback = Better_Config::getAppConfig()->base_url . '/login/thirdrenren';
			$callback .= "?callback=$target";
			//$callback = "http://k.ai/setting/sync?callback=$target";
			if (APPLICATION_ENV!='production') {
				//$o->proxy = 'http://10.10.1.254:808';
		}
			//$keys = $o->getRequestToken($callback);
		//	$_SESSION['oauth_keys'] = $keys;
			$aurl = "https://graph.renren.com/oauth/authorize?client_id={$WB_AKEY}&response_type=code&scope=publish_checkin+publish_feed+photo_upload+status_update&redirect_uri={$callback}";
			//$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
		
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
		}	
	}
	
	
	/**
	 * 退出登录
	 *
	 * @return null
	 */
	public function logoutAction()
	{
		Better_User_Login::logout();
		$this->_helper->getHelper('Redirector')->gotoUrl('/');
		exit(0);
	}
	
	public function kaiAction()
	{
		
		$from_third = strlen($_SESSION['thirdfrom'])>0?"third".$_SESSION['thirdfrom']:'';
		$third_page = strlen($_SESSION['homepage'])>0?$_SESSION['homepage']:'http://k.ai/kai';			
		$_SESSION['thirdfrom'] = '';	
		$_SESSION['homepage'] = '';		
		$had_at_kai = array("thirdsina","thirdqq");
		$this->view->had_at_kai= 0;
		$this->view->oauthatkai = Better_Config::getAppConfig()->base_url."/login/".$from_third;
		if(in_array($from_third,$had_at_kai)){
			$this->view->had_at_kai=1;
			$this->view->oauthatkai = Better_Config::getAppConfig()->base_url."/login/".$from_third."?atkai=1";
		}				
		$this->view->oauthurl = Better_Config::getAppConfig()->base_url."/login/".$from_third;
		$this->view->thirdpage = $third_page;
		$this->view->css = 'kai_third';
	}
	
	
	public function thirddoubanAction(){
		
		$callback = $this->getRequest()->getParam('callback', '');
		$WB_AKEY = Better_Config::getAppConfig()->oauth->key->douban_akey;
		$WB_SKEY = Better_Config::getAppConfig()->oauth->key->douban_skey;	
		//$atkai = $this->getRequest()->getParam('atkai', '');

		if($callback){
			$loginfrom = 'douban.com';	
			$keys = $_SESSION['oauth_keys'];
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );		
			$o->protocol = $callback;		
			$oauth_verifier = $_GET['oauth_verifier'];
			$oauth_token = $_GET['oauth_token'];			
			$last_key = $o->getAccessToken($oauth_verifier, $oauth_token );
			$oauth_token = $last_key['oauth_token'];
			$oauth_token_secret = $last_key['oauth_token_secret'];
			$tid = $last_key['douban_user_id'];		
		
			$protocol = $loginfrom;			
			$thirdinfo = Better_Service_PushToOtherSites::factory($protocol, $tid, 'douban', $oauth_token, $oauth_token_secret,$tid);			
			$sinainfo = $thirdinfo->getInfo();					
			$username = $tid;			
			$nickname = $sinainfo['nickname'];
			$avatarurl = $sinainfo['image_url'];		
			
			
			
			
			$bindlist = Better_DAO_ThirdBinding::getBindUser($loginfrom, $username);
			$userlist = Better_DAO_SyncQueue::getSyncbysiteuser($loginfrom,$username);
			if((is_array($userlist) && $userlist['uid']>0) || $bindlist){
				$uid = $userlist['uid']? $userlist['uid']:$bindlist['uid'];	
				$userInfo = Better_user::getInstance($uid)->getUserInfo();	
				if($userInfo['state']==Better_User_State::BANNED){									
					$yourbanned = 1;
				}						
				if (!$yourbanned) {	
					Better_Registry::get('sess')->set('uid', $uid);
					Better_User_AutoLogin::getInstance($uid)->putCookie();
					Better_Registry::get('sess')->stick();	
				}
				$this->_helper->getHelper('Redirector')->gotoUrl('/home');	
			} else {	
				try {
					$testemail = $username."@t.".$loginfrom;
					$testuser = Better_User::getInstance($testemail, 'email');					
					$testuserInfo = $testuser->getUserInfo();
					$expires_in = 0;			
					if($testuserInfo['uid']>0){
						Better_User_Syncsites::getInstance($testuserInfo['uid'])->add($loginfrom, $username, $username, $oauth_token, $oauth_token_secret,$username,$expires_in);	
						Better_Registry::get('sess')->set('uid', $testuserInfo['uid']);
						Better_User_AutoLogin::getInstance($testuserInfo['uid'])->putCookie();
						Better_Registry::get('sess')->stick();				
						$this->_helper->getHelper('Redirector')->gotoUrl('/home');
					}
				} catch(Exception $e){
					
				}			
				Better_Registry::get('sess')->set('doubandobind', 1);
				Better_Registry::get('sess')->set('protocol_site', 'douban.com');
				Better_Registry::get('sess')->set('protocol_tid', $username);
				Better_Registry::get('sess')->set('protocol_username', $username);
				Better_Registry::get('sess')->set('protocol_nickname', $nickname);
				Better_Registry::get('sess')->set('protocol_callback', $callback);
				Better_Registry::get('sess')->set('protocol_oauthtoken', $oauth_token);
				Better_Registry::get('sess')->set('protocol_oauthtokensecret', $oauth_token_secret);
				Better_Registry::get('sess')->set('avatarurl', $avatarurl);
				$this->_helper->getHelper('Redirector')->gotoUrl('/signup/partnersignup');	
			} 
			
		} else {			
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$target = 'douban';
			$o->protocol = $target;
			$callback = Better_Config::getAppConfig()->base_url . '/login/thirddouban';
			$callback .= "?callback=$target";
			/*
			if($atkai){
				$_SESSION['atkai'] =1;
			}			
			*/	
			$keys = $o->getRequestToken($callback);
			$_SESSION['oauth_keys'] = $keys;
			$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);		
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);	
		}
		
	}
}

?>