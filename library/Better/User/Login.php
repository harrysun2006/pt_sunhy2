<?php

/**
 * 登录、注销处理
 * 
 * @author pysche
 *
 */
class Better_User_Login extends Better_User_Base
{
	
	const INVALID_PWD = '1';
	const NEED_VALIDATED = '2';
	const ACCOUNT_BANNED = '3';
	const ACCOUNT_MUTE = '4';
	const LOGINED = '0';
	const FORCE_VALIDATING = '5';
	const JUMP_SIGNUP = '6';
	const JUMP_STEP2 = '7';
	const Thrid_False = '8';
	const Thrid_True = '9';
	const BEDO_LOGIN = '10';
	
	/**
	 * 登录
	 *
	 * @param $username
	 * @param $password
	 * @return integer
	*/
	public static function login($username, $password, $passMd5=true, $remember=false, $partner='')
	{	
		
		$username = trim($username);
		$result = self::INVALID_PWD;
		$loginbycellno  = 0;
		//	预先判断一下用户名格式，尽量减少登录时的sql查询次数
		if (preg_match('/^([0-9]+)$/', $username)) {
			$keys = array('cell_no');
			$loginbycellno = 1;
		} else if (Better_Functions::checkEmail($username)) {
			$keys = array('email');
		} else {
			$keys = array('email', 'cell_no');
		}
		Better_Log::getInstance()->logInfo($loginbycellno."**",'newkarma');
		foreach ($keys as $key) {
			if ($key=='cell_no' && preg_match(Better_User::CELL_PAT, $username)) {
				$username = '86'.$username;
			}			

			$row = Better_DAO_User::getInstance()->get(array(
				$key => $username
				));
			if (is_array($row) && count($row)>0) {
				$result = self::_checkUserState($row, $loginbycellno, $password, $passMd5);
			}
		}
		//增加贝多号登录
		if ( $result == self::INVALID_PWD ) {
			$_uid = self::bedoLogin($username, $password);
			if ($_uid) {
				$row = Better_DAO_User::getInstance()->get(array('a.uid' => $_uid));
				$result = self::_checkUserState($row, 0, $password, $passMd5, true);
			}
		}		

		return $result;
	}
	
	/**
	 * 使用Token登录
	 */
	public static function tokenLogin($username, $password, $passMd5=true, $remember=false, $partner='')
	{
		$username = trim($username);
		$result = self::INVALID_PWD;
		$uid = Better_Api::getInstance()->decToken($password);
		
		if ($uid) {
			$user = Better_User::getInstance($uid);
			$row = $user->getUser();

			if (is_array($row) && count($row)>0) {
				$state = $row['state'];

				switch ($state) {
					case Better_User_State::BANNED:
						$result = self::ACCOUNT_BANNED;
						break;
					case Better_User_State::MUTE:
					case Better_User_State::ENABLED:
					default:
						if ($row['state']==Better_User_State::SIGNUP_VALIDATING && time()-$row['regtime']>3600*24*30000) {
							$result = self::FORCE_VALIDATING;
						} else {
							$result = self::LOGINED;
							
							$uid = $row['uid'];
							$userInfo = &$row;

							Better_Registry::get('sess')->set('uid', $uid);
							Better_Hook::factory(array(
								'Karma', 'Badge', 'User', 'Cache', 'Secret'
							))->invoke('UserLogin', array(
								'uid' => $uid,
								'partner' => defined('BETTER_PARTNER') ? BETTER_PARTNER : $_REQUEST['kai_partner'],
								'secret' => defined('BETTER_IMEI') ? BETTER_IMEI : $_REQUEST['secret']
							));
							
							$language = Better_Registry::get('language');	
							if($userInfo['language'] !=$language){
								$tmpdata['language']=$language;
								Better_User::getInstance($uid)->updateUser($tmpdata);
							}	
						}
						break;
				}

			}
		}

		return $result;
	}	
	 
	//增加通过第三方登陆的验证
	public static function newlogin($username, $password,$login_type='local', $passMd5=false, $remember=false,$passthrid=false, $partner='')
	{
		$username = trim($username);
		$keys = array('email', 'cell_no');
		$loginbycellno  = 0;
		$loginbyapi = defined('IN_API') ? 1 : 0;
		$result = self::INVALID_PWD;
		$flag = 0;
		foreach ($keys as $key) {
			if ($key=='cell_no' && preg_match(Better_User::CELL_PAT, $username)) {
				$username = '86'.$username;
				$loginbycellno  = 1;
			}
			if($key=='email' && $login_type!='local'){
				switch ($login_type){
					case 'sina':
						$loginfrom = 'sina.com';						
						break;
					case 'sohu':
						$loginfrom = 'sohu.com';
						break;
					case 'kaixin001':
						$loginfrom = 'kaixin001.com';
						break;
				}		
				
				$service = Better_Service_PushToOtherSites::factory($loginfrom, $username, $password);
							

				$logined = $service->fakeLogin();
				$service_0 = $service;		
				$tid = $service->tid;
				Better_Log::getinstance()->logInfo($logined."--".$loginfrom."--".$username."--".$password."|".$login_type."|".$passthrid,'the2end');													

				if($logined){
					if($login_type=='sina' && !$passthrid){	
						$_accecss_token = $service->_accecss_token; 
						$_accecss_token_secret = $service->_accecss_token_secret; 						
						$WB_AKEY = Better_Config::getAppConfig()->oauth->key->sina_akey;
						$WB_SKEY = Better_Config::getAppConfig()->oauth->key->sina_skey;				
						$callback = Better_Config::getAppConfig()->base_url.'/login/thirdsina';				
						$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
						$o->protocol = $login_type;
						if(Better_Config::getAppConfig()->hadproxy){
							$o->proxy = '10.10.1.254:808';	
						}												
						$keys = $o->getRequestToken();
						$_SESSION['oauth_keys'] = $keys;
						$callback .= "?callback=$login_type";
						$_SESSION['sina_username'] = $username;
						$_SESSION['sina_password'] = $password;				
						$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
						Header("Location:".$aurl);
						exit;					
					}	
					$yourbanned = 0;						
					$userlist = Better_DAO_SyncQueue::getSyncbysiteuser($loginfrom,$username);						
					$bindlist = Better_DAO_ThirdBinding::getBindUser($loginfrom, $tid);											
					if((is_array($userlist) && $userlist['uid']>0) || $bindlist){
						$uid = $userlist['uid']? $userlist['uid']: $bindlist['uid'];
						Better_User_Syncsites::getInstance($uid)->delete($loginfrom);
						if($login_type=='sina'){
							Better_User_Syncsites::getInstance($uid)->add($loginfrom, $username, $password, $_accecss_token, $_accecss_token_secret, $service_0->tid);
						} else {						
							Better_User_Syncsites::getInstance($uid)->add($loginfrom, $username, $password, '', '', $service_0->tid);
						}
						$userInfo = Better_user::getInstance($uid)->getUserInfo();	
						if($userInfo['state']==Better_User_State::BANNED){									
							$yourbanned = 1;
						}						
						if (!$yourbanned) {	
							Better_Registry::get('sess')->set('uid', $uid);
							Better_User_AutoLogin::getInstance($uid)->putCookie();
							Better_Registry::get('sess')->stick();	
						}
						$result = self::LOGINED;
						$flag = 4;	
					} else {
						$user = Better_User::getInstance($username, 'email');					
						$userInfo = $user->getUserInfo();	
						if($userInfo['state']==Better_User_State::BANNED){
							$yourbanned = 1;
						}		
						$uid = (int)$user->uid;	
						if($uid>0){						
							$synclist = Better_User_Syncsites::getInstance($uid)->getSites();							
							if(isset($synclist[$loginfrom])){
								if($synclist[$loginfrom]['username']==$username){						
									Better_User_Syncsites::getInstance($uid)->delete($loginfrom);
									if($login_type=='sina'){
										Better_User_Syncsites::getInstance($uid)->add($loginfrom, $username, $password, $_accecss_token, $_accecss_token_secret, $service_0->tid);
									} else {						
										Better_User_Syncsites::getInstance($uid)->add($loginfrom, $username, $password, '', '', $service_0->tid);
									}
								}
							} else {
								$service = Better_Service_PushToOtherSites::factory($loginfrom, $username, $password);
								$ck = $service->checkAccount($uid, $loginfrom, $username);		
								if ($ck) {
									Better_User_Syncsites::getInstance($uid)->add($loginfrom, $username, $password, '', '', $service_0->tid );		
								}							
							}
							if (!$yourbanned) {
								Better_Registry::get('sess')->set('uid', $uid);
								Better_User_AutoLogin::getInstance($uid)->putCookie();
								Better_Registry::get('sess')->stick();	
							}
							$result = self::LOGINED;
							$flag = 4;		
						} else {
							$third_info = $service->getInfo();
							$avatarurl = $service->getAvatar();	
							$tid = $service->tid;							
							$data['email'] = $username;
							$temnickname = split('@',$username);
							$nickname = $third_info['nickname'];
							Better_Log::getInstance()->logInfo(serialize($third_info),'thirdloginxx');								
							Better_Registry::get('sess')->set('snstothird', 1);
							Better_Registry::get('sess')->set('protocol_tid', $tid);
							Better_Registry::get('sess')->set('protocol_site', $loginfrom);
							Better_Registry::get('sess')->set('protocol_email', $username);
							Better_Registry::get('sess')->set('protocol_username', $nickname);
							Better_Registry::get('sess')->set('protocol_nickname', $nickname);
							Better_Registry::get('sess')->set('protocol_password', $password);
							Better_Registry::get('sess')->set('avatarurl', $avatarurl);
							if($login_type=='sina'){
								Better_Registry::get('sess')->set('protocol_oauthtoken', $_accecss_token);
								Better_Registry::get('sess')->set('protocol_oauthtokensecret', $_accecss_token_secret);							
							} 
						//	Better_Registry::get('sess')->set('protocol_oauthtoken', $oauth_token);
						//	Better_Registry::get('sess')->set('protocol_oauthtokensecret', $oauth_token_secret);
							$gotosign = Better_Config::getAppConfig()->base_url.'/signup/partnersignup';	
						
							Header("Location:".$gotosign);
							exit;
							//$this->_helper->getHelper('Redirector')->gotoUrl('/signup/partnersignup');	
							
							
							$checkid = Better_User_Signup::getInstance(10000)->validNickname($nickname, 0);
							
							switch ($checkid['code']) {
								case $checkid['codes']['SUCCESS']:
									
									break;
								case $checkid['codes']['NICKNAME_EXISTS']:
							
									$i = 0;														
									while($i==0 || $checkid['code']==$checkid['NICKNAME_EXISTS']){
										$attemp = $nickname."_".rand(1,999);
										$checkid = Better_User_Signup::getInstance(10000)->validNickname($attemp,0);														
										$i++;								
									}
																		
									if ($checkid['code']!=$checkid['codes']['SUCCESS']) {
										$nickname = '';
									}
									break;
								default:
									$nickname = '';
									break;
							}	
					    	$data['nickname'] = $nickname;
					    	$data['password'] = $password;	
					    	$thirdloginreg=1;			    	
							$uid = Better_User_Signup::Signup($data);
							$user = Better_User::getInstance($username, 'email');
							$userInfo = $user->getUserInfo();
							$uid = (int)$user->uid;
							Better_User_Syncsites::getInstance($uid)->add($loginfrom, $username, $password, '', '', $service->tid);
							Better_Log::getInstance()->logInfo(serialize($data)."RegFrom".$loginfrom,'thirdtokai');											
							Better_Registry::get('sess')->set('step2nickname',1);
							Better_Registry::get('sess')->set('uid', $uid);
							Better_User_AutoLogin::getInstance($uid)->putCookie();
							Better_Registry::get('sess')->stick();					
							$flag=2;								
						}					
					}
				} else {
					$flag = 3;
				}	
					
			}

			!$flag && $row = call_user_func(array(
							Better_DAO_User::getInstance() , 'find'.ucfirst($key)
							), $username);

			if (is_array($row) && count($row)>0) {
				$state = $row['state'];

				switch ($state) {
					case Better_User_State::BANNED:
						$result = self::ACCOUNT_BANNED;
						break;
					case Better_User_State::MUTE:
					case Better_User_State::ENABLED:
					default:
						if ($row['state']==Better_User_State::SIGNUP_VALIDATING && time()-$row['regtime']>3600*24*30000) {
							$result = self::FORCE_VALIDATING;
						} else {
							$salt = $row['salt'];
							$dbPwd = $row['password'];
							$md5pass = $passMd5==true ? $password : md5($password);

							if ($dbPwd==md5($md5pass.$salt)) {
								$result = self::LOGINED;
								
								$uid = $row['uid'];
								$user = Better_User::getInstance($uid);
								$userInfo = $user->getUser('uid', '', true);
	
								Better_Registry::get('sess')->set('uid', $uid);
								if($loginbycellno){
									setcookie("loginbycellno", $loginbycellno,time()+Better_Session_Base::$stickTime, '/');
								}
								Better_User_AutoLogin::getInstance($uid)->putCookie();
								Better_Registry::get('sess')->stick();

								Better_Hook::factory(array(
									'Karma', 'Badge', 'User', 'Cache'
								))->invoke('UserLogin', array(
									'uid' => $uid,
									'partner' => defined('BETTER_PARTNER') ? BETTER_PARTNER : $_REQUEST['kai_partner'],
									'loginbycellno' => $loginbycellno,
									'loginbyapi' => $loginbyapi
								));
							}
							$language = Better_Registry::get('language');	
							if($userInfo['language'] !=$language){
								$tmpdata['language']=$language;
								Better_User::getInstance($uid)->updateUser($tmpdata);
							}	
						}
						break;
				}

			}
		}
		
		if($flag==1){
			$result = self::JUMP_SIGNUP;
		} else if($flag==2){
			$result = self::JUMP_STEP2;
		} else if($flag==3){
			$result = self::Thrid_False;
		}			
		if($yourbanned){
			Better_Registry::get('sess')->set('uid', 0);
			$result = self::ACCOUNT_BANNED;	
		}
		if($result == self::LOGINED){
			Better_Hook::factory(array(
									'Rp'
								))->invoke('UserLogin', array(
									'uid' => $uid,
									'partner' => defined('BETTER_PARTNER') ? BETTER_PARTNER : $_REQUEST['kai_partner'],
									'loginbycellno' => $loginbycellno,
									'loginbyapi' => $loginbyapi
								));
		}
		return $result;
		
	}
		
	
	/**
	 * 注销
	 * 
	 * @return unknown_type
	 */
	public static function logout()
	{
		$uid = Better_Registry::get('sess')->get('uid');
		
		setcookie('lan');
		
		Better_Registry::get('sess')->set('uid');
		Better_Registry::get('sess')->set('user');
		Better_Registry::get('sess')->destroy();

		Better_User_AutoLogin::clear();
		
		Better_Hook::factory(array(
			'Karma',
		))->invoke('UserLogout', array(
			'uid' => $uid,
		));
	}
	
	
	/**
	 * 
	 * @param $row
	 * @param $loginbycellno
	 * @return unknown_type
	 */
	public static function _checkUserState($row, $loginbycellno, $password, $passMd5, $isAutoLogin = false)
	{
		$result = self::INVALID_PWD;
		
		$state = $row['state'];

		switch ($state) {
			case Better_User_State::BANNED:
				$result = self::ACCOUNT_BANNED;
				break;
			case Better_User_State::MUTE:
			case Better_User_State::ENABLED:
			default:
				if ($row['state']==Better_User_State::SIGNUP_VALIDATING && time()-$row['regtime']>3600*24*30000) {
					$result = self::FORCE_VALIDATING;
					Better_Registry::set('force_validating_uid', $row['uid']);
				} else {
					$salt = $row['salt'];
					$dbPwd = $row['password'];
					$md5pass = $passMd5==true ? $password : md5($password);

					if ($dbPwd==md5($md5pass.$salt) || $isAutoLogin) {
						$result = self::LOGINED;
						Better_Registry::set('force_validating_uid', $row['uid']);
						
						$uid = $row['uid'];
						$user = Better_User::getInstance($uid);
						$userInfo = $user->getUser('uid', '', true);
	
						Better_Registry::get('sess')->set('uid', $uid);
	
						Better_User_AutoLogin::getInstance($uid)->putCookie();
						Better_Registry::get('sess')->stick();
						Better_Log::getInstance()->logInfo($loginbycellno,'newkarma');
						if (!defined('IN_API') || (defined('IN_API') && defined('BETTER_API_AUTH'))) {
							Better_Hook::factory(array(
								'Karma', 'Badge', 'User', 'Cache', 'Secret','Rp' 
							))->invoke('UserLogin', array(
								'uid' => $uid,
								'partner' => defined('BETTER_PARTNER') ? BETTER_PARTNER : $_REQUEST['kai_partner'],
								'secret' => defined('BETTER_IMEI') ? BETTER_IMEI : $_REQUEST['secret'],
								'loginbycellno' => $loginbycellno
							));
						}
	
						
						$language = Better_Registry::get('language');	
						if($userInfo['language'] !=$language){
							$tmpdata['language']=$language;
							Better_User::getInstance($uid)->updateUser($tmpdata);
						}
					}
				}
				break;
		}

		return $result;
	}
	
	
	/**
	 * 
	 * @param $username
	 * @param $password
	 * @param $remember
	 * @return unknown_type
	 */
	public static function autoLogin($username, $password, $remember=false)
	{
		$result = self::INVALID_PWD;
		$loginbycellno  = 0;
		//	预先判断一下用户名格式，尽量减少登录时的sql查询次数
		if (preg_match('/^([0-9]+)$/', $username)) {
			$keys = array('cell_no');
			$loginbycellno = 1;
		} else if (Better_Functions::checkEmail($username)) {
			$keys = array('email');
		} else {
			$keys = array('email', 'cell_no');
		}
		Better_Log::getInstance()->logInfo($loginbycellno."**",'newkarma');
		foreach ($keys as $key) {
			if ($key=='cell_no' && preg_match(Better_User::CELL_PAT, $username)) {
				$username = '86'.$username;
			}			

			$row = Better_DAO_User::getInstance()->get(array(
				$key => $username
				));
			if (is_array($row) && count($row)>0) {
				$state = $row['state'];

				switch ($state) {
					case Better_User_State::BANNED:
						$result = self::ACCOUNT_BANNED;
						break;
					case Better_User_State::MUTE:
					case Better_User_State::ENABLED:
					default:
						if ($row['state']==Better_User_State::SIGNUP_VALIDATING && time()-$row['regtime']>3600*24*30000) {
							$result = self::FORCE_VALIDATING;
							Better_Registry::set('force_validating_uid', $row['uid']);
						} else {
							
							$result = self::LOGINED;
							Better_Registry::set('force_validating_uid', $row['uid']);
							
							$uid = $row['uid'];
							$user = Better_User::getInstance($uid);
							$userInfo = $user->getUser('uid', '', true);

							Better_Registry::get('sess')->set('uid', $uid);

							Better_User_AutoLogin::getInstance($uid)->putCookie();
							Better_Registry::get('sess')->stick();
							Better_Log::getInstance()->logInfo($loginbycellno,'newkarma');
							if (!defined('IN_API') || (defined('IN_API') && defined('BETTER_API_AUTH'))) {
								Better_Hook::factory(array(
									'Karma', 'Badge', 'User', 'Cache', 'Secret','Rp' 
								))->invoke('UserLogin', array(
									'uid' => $uid,
									'partner' => defined('BETTER_PARTNER') ? BETTER_PARTNER : $_REQUEST['kai_partner'],
									'secret' => defined('BETTER_IMEI') ? BETTER_IMEI : $_REQUEST['secret'],
									'loginbycellno' => $loginbycellno
								));
							}

							
							$language = Better_Registry::get('language');	
							if($userInfo['language'] !=$language){
								$tmpdata['language']=$language;
								Better_User::getInstance($uid)->updateUser($tmpdata);
							}	
						}
						break;
				}

			}
		}

		return $result;
	}
	
	
	/**
	 * 
	 * @param $jid
	 * @param $password
	 * @return unknown_type
	 */
	public static function bedoLogin($jid, $password)
	{
		$bedoInfo = Better_Service_BedoBinding::getInstance()->login($jid, $password);
		if ($bedoInfo['kaiPass'] && $bedoInfo['kaiEmail']) {
			return $bedoInfo['uid'];
		} else if (is_array($bedoInfo)) {
			$r = Better_Service_BedoBinding::getInstance()->getValidInfo($jid, $password, $bedoInfo['username']);
			$post['email'] = $r['email'];
			$post['nickname'] = $r['username'];
			$post['password'] = $r['password'];
			$post['repassword'] = $r['password'];
			$changeNickname = $r['changeNickname'];
			$changePassword = $r['changePassword'];
			$post['partner'] = 'bedo';
			$uid = Better_User_Signup::signup($post);
			if (!$uid) {
				return false;
			}
			Better_Service_BedoBinding::getInstance()->bind($uid, $jid, $password);
			if ($changePassword || $changeNickname) {
				$bedosign_notice = str_replace('xxxxxx',$post['nickname'] . '/' . $post['password'],Better_Language::load()->signup->onkey_bedobind_notice);						
				Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
				'content' => $bedosign_notice,
				'receiver' => $uid
				));
			}
			Better_Service_BedoBinding::getInstance()->syncInfo($jid);
			return $uid;
		} else {
			return false;
		}
	}
}
