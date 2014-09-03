<?php

/**
 * 用户帐号
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_AccountController extends Better_Controller_Api
{
	public function init()
	{
		parent::init();
		
		$this->xmlRoot = 'user';
	}	
	
	/**
	 * 4.6 重发email验证邮件
	 * 
	 * @return
	 */
	public function revalidateemailAction()
	{
		$this->xmlRoot = 'message';
		$this->needPost();
		
		$email = $this->post['email'];
		$message = '';
		
		if ($email=='') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.account.email_invalid');
		} else if (!Better_Functions::checkEmail($email)) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.account.email_invalid');
		} else {
			$uid = Better_Email_Bind::hasQueue($email);
			if ($uid) {
				$user = Better_User::getInstance($uid)->getUser();
				if (!isset($user['email'])) {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.account.email_not_exists');
				} else if ($user['enabled']=='1') {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$err = $this->lang->error->email_has_been_actived;
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.account.email_not_exists');
			}
		}

		if ($err=='') {
			$user['email'] = $email;
			Better_Email_Bind::send($user);
			$message = str_replace('{EMAIL}', $email, $this->langAll->signup->active->sent);
		}		
		
		$this->data[$this->xmlRoot] = $message;
		
		$this->output();
	}
	
	/**
	 * 4.4 可用的第三方登录应用列表
	 * 
	 * @return
	 */
	public function partnersAction()
	{
		$this->xmlRoot = 'login_partners';
		
		$version = $this->getRequest()->getParam('version', 1);
		$oauth = $this->getRequest()->getParam('oauth', '');
		
		$_renren = array(
					'id' => 'renren',
					'name' => $this->lang->login_partner->renren_twitter->name,
					'image_url' => BETTER_BASE_URL . '/images/m-t-renren.png',
					'only_oauth' => 'false',
					'oauth_url' => BETTER_BASE_URL . '/api/oauthlogin.xml?sns=renren.com',
					);
		$_douban = array(
					'id' => 'douban',
					'name' => $this->lang->login_partner->douban_twitter->name,
					'image_url' => BETTER_BASE_URL . '/images/m-t-douban.png',
					'only_oauth' => 'true',
					'oauth_url' => BETTER_BASE_URL . '/api/oauthlogin.xml?sns=douban.com',					
					);
		$_t163 = array(
					'id' => '163',
					'name' => $this->lang->login_partner->t163_twitter->name,
					'image_url' => BETTER_BASE_URL . '/images/m-t-163.png',
					'only_oauth' => 'true',
					'oauth_url' => BETTER_BASE_URL . '/api/oauthlogin.xml?sns=163.com',					
					);					
		$_qq = array(
					'id' => 'qq',
					'name' => $this->lang->login_partner->qq_twitter->name,
					'image_url' => BETTER_BASE_URL . '/images/m-t-qq.png',
					'only_oauth' => 'true',
					'oauth_url' => BETTER_BASE_URL . '/api/oauthlogin.xml?sns=qq.com',					
					);

		$_qqsns = array(
					'id' => 'qqsns',
					'name' => $this->lang->login_partner->qqsns_twitter->name,
					'image_url' => BETTER_BASE_URL . '/images/m-t-qqsns.png',
					'only_oauth' => 'true',
					'oauth_url' => BETTER_BASE_URL . '/api/oauthlogin.xml?sns=qqsns.com',					
					);					
					
		
		if($version!=$this->config->api->partners_version){
			$partners = array(
				0 => array(
					'id' => 'sina',
					'name' => $this->lang->login_partner->sina_twitter->name,
					'image_url' => BETTER_BASE_URL.'/images/m-t-sina.png'
					),
				1 => array(
					'id' => 'sohu',
					'name' => $this->lang->login_partner->sohu_twitter->name,
					'image_url' => BETTER_BASE_URL.'/images/m-t-sohu.png'
					),
				2 => array(
					'id' => 'kaixin001',
					'name' => $this->lang->login_partner->kaixin001_twitter->name,
					'image_url' => BETTER_BASE_URL.'/images/m-t-kaixin001.png'
					),
				);
				
			$oauth && $partners[] = $_renren;
			if ( 'true' == $oauth ) {
				$partners[] = $_douban;
				$partners[] = $_t163;
				$partners[] = $_qq;
				
				$ip = Better_Functions::getIp();
				if ( '221.224.52.24' == $ip ) {
					$partners[] = $_qqsns;
				}
			}
				 
			foreach ($partners as $row) {
				$this->data[$this->xmlRoot][] = array(
													'partner' => $this->api->getTranslator('partner_login')->translate(array(
														'data' => &$row
														))
													);
			}
		}
		
		//2011-2-14增加版本号
		$this->data[$this->xmlRoot]['version'] = $this->config->api->partners_version;
		
		$this->output();
	}
	
	/**
	 * 4.5 第三方登录
	 * 
	 * @return
	 */
	public function partnerloginAction()
	{
		$ver = $this->post['ver'] == 2 ? 2 : 1;
		$res = $this->getRequest()->getParam('res', '');
		$this->xmlRoot = 'partner_login';
		define('BETTER_API_AUTH', true);
		$partners = array(
			'sina', 'sohu', 'kaixin001', 'renren'
			);
		$partner = strtolower($this->post['partner']);
		$id = strtolower($this->post['id']);
		$password = $this->post['password'];
		$result = 'false';
		
		if ($ver == 2) {
			$kai_username = $this->post['kai_username'];
			$kai_password = $this->post['kai_password'];
		}
		Better_Log::getInstance()->logInfo(serialize($this->post),'partnerlogin_xx');
		if (in_array($partner, $partners)) {
			Better_Log::getInstance()->logInfo($id."--".Better_Functions::checkEmail($id),'partnerlogin_xx');
			if(1 || Better_Functions::checkEmail($id)){
				$partner .= '.com';				
				$j=0;
				for($i=1;$i<2;$i++){	
					if($j>1){
						break;
					}
					$service = Better_Service_PushToOtherSites::factory($partner, $id, $password);
					
					//人人
					if ($partner == 'renren.com') {
						$logined = $service->fakeLoginApi();
					} else {
						$logined = $service->fakeLogin();
					}
					if($partner=='sina.com' && !$logined && Better_Functions::isCell($id)){
						Better_Log::getInstance()->logInfo($id."--".$password,'sinamobile');
						$i=0;
						$j++;
						$id = $id."@sina.cn";
						continue;
					}
				}			
				$username = &$id;				
				if($logined){
					$third_info = $service->getInfo();
					if ($ver==2 && $kai_username && $kai_password) {
						$loginResult = Better_User_Login::login($kai_username, $kai_password, false, false, '');
						if ($loginResult == Better_User_Login::LOGINED) {
							$uid = Better_Registry::get('sess')->get('uid');
							Better_User_Syncsites::getInstance($uid)->delete($partner);
							$_accecss_token = $_accecss_token_secret = '';
							if ('sina.com' == $partner || 'renren.com' == $partner) {
								$_accecss_token = $service->_accecss_token;	
								$_accecss_token_secret = $service->_accecss_token_secret;								
							}
							Better_User_Syncsites::getInstance($uid)->add($partner, $id, $password, $_accecss_token, $_accecss_token_secret, $service->tid, $service->_expires_in);
							//设置一下用户的头像
							$avatarurl = $third_info['image_url'];
							if ($avatarurl) {
								Better_User::getInstance($uid)->avatar()->upload($avatarurl, 'jpg', $uid);
							}
						} else {
							Better_Controller::sendSquidHeaderC(-99);
							$this->error('error.user.auth_failed', 200);
						}
					}
					
					$bind_user = false;
					$service->tid && $bind_user = Better_DAO_ThirdBinding::getBindUser($partner, $service->tid);
					
					$userlist = Better_DAO_SyncQueue::getSyncbysiteuser($partner,$id);									
					if( (is_array($userlist) && $userlist['uid']>0) || $bind_user ){
						$uid = $userlist['uid'] ? $userlist['uid'] : $bind_user['uid'];
						if ($ver == 1) {
							Better_User_Syncsites::getInstance($uid)->delete($partner);
							Better_User_Syncsites::getInstance($uid)->add($partner, $id, $password, $service->_accecss_token, $service->_accecss_token_secret, $service->tid, $service->_expires_in);
						}
						
						$userInfo = Better_User::getInstance($uid)->getUserInfo();	
						
						$this->uid = $uid;
						$this->user = Better_User::getInstance($uid);
						
						if($userInfo['state'] == Better_User_State::BANNED ){									
							$this->error('error.users.account_banned');	
							exit;
						} elseif($userInfo['state'] == Better_User_State::SIGNUP_VALIDATING && time() - $userInfo['regtime'] > 3600*24*30000 ) {
							Better_Controller::sendSquidHeaderC(-2);
							$this->error('error.user.force_validating', 200);
							exit;
						}
						
						$result = 'true';
						$tmp = $this->api->loginToken($uid);
						//等待返回的信息						
						$this->data[$this->xmlRoot]['login_token'] = array(
							'username' => $tmp['username'],
							'token' => $tmp['token'],
							);												
						$this->data[$this->xmlRoot]['user'] = $this->api->getTranslator('user')->translate(array(
							'data' => &$userInfo
						));					

						$this->data[$this->xmlRoot]['achievement'] = array();
						$this->data[$this->xmlRoot]['requests'] = array();
						$this->data[$this->xmlRoot]['activities'] = array();
						$this->data[$this->xmlRoot]['version'] = array();
						$this->data[$this->xmlRoot]['recommends'] = array();
						$this->data[$this->xmlRoot]['coupons'] = array();
						
						//好友请求数
						$frCount = Better_DAO_DmessageReceive::getInstance($this->uid)->getMinesCount(array(
							'type'=> array('friend_request'),
							'readed'=> 0,
						));
						if ($frCount) {
							$this->data[$this->xmlRoot]['requests'][] = array(
								'fr_message' => str_replace('{TOTAL}', $frCount, $this->lang->account->fr_requests)
								);				
						}
						
						//私信数
						$msgCount = Better_DAO_DmessageReceive::getInstance($this->uid)->getMinesCount(array(
							'type' => array('direct_message'),
							'readed' => 0
							));
						if ($msgCount) {
							$this->data[$this->xmlRoot]['requests'][] = array(
								'dm_message' => str_replace('{TOTAL}', $msgCount, $this->lang->account->dm_requests)
								);
						}
						
						//	成就
						$hasAchievement = false;
						$as = $this->user->achievement()->apiParseNew($this->lang->global->this_login);
						foreach ($as as $ac) {
							$hasAchievement = true;
							$this->data[$this->xmlRoot]['achievement'][] = array(
								'message' => $ac
								);
						}						

						$this->data[$this->xmlRoot]['message'] = '';
						$allocs = Better_Api_Allocate::allocate($uid);
						foreach ($allocs as $k=>$v) {
							$this->data[$this->xmlRoot][$k] = $v;
						}						
									
						//	版本检查
						$platform = $this->getRequest()->getParam('platform', '');
						$model = $this->getRequest()->getParam('model', '');
						$language = 'zh-cn';
						$ver = $this->getRequest()->getParam('version', '');
						
						$testingMode = $this->getRequest()->getParam('testing_mode', '');
		
							if ($platform && $model && $ver) {
							$result1 = Better_Mobile_Upgrade::getInstance()->parse(array(
									'lang' => 'zh-cn',
									'platform' => $platform,
									'model' => $model,
									'language' => $language,
									'ver' => $ver
									));
					
								$this->user->cache()->set('client', array(
									'platform' => $platform,
									'model' => $model,
									'language' => $language,
									'ver' => $ver,
									'res' => $res,
									));			
					
								$this->data[$this->xmlRoot]['version'] = array(
								'action' => $result1['action'],
									'platform' => $platform,
									'model' => $model,
									'language' => $language,
								'ver' => $result1['ver'],
								'message' => $result1['message'],
								'code' => $result1['code'],
								'url' => $result1['url'],
									);
							}
						
						if ($hasAchievement) {
						//	活动
						$rows = Better_Poi_Notification::search(array(
							'lon' => $this->userInfo['lon'],
							'lat' => $this->userInfo['lat'],
							'range' => 500000,
							'page' => 1,
							'count' => 2
							));
						foreach ($rows['rows'] as $row) {
							$this->data[$this->xmlRoot]['coupons'][] = array(
								'coupon' => $this->api->getTranslator('coupon')->translate(array(
									'data' => &$row,
									'userInfo' => &$this->userInfo,
									)),
								);
						}
						
						//	推荐
						$rTips = Better_Poi_Tips::recommends(array(
							'uid' => $this->uid,
							'page' => 1,
							'page_size' => 3
							));
						foreach ($rTips['rows'] as $row) {
							$this->data[$this->xmlRoot]['recommends'][] = array(
								'status' => $this->api->getTranslator('status')->translate(array(
									'data' => &$row,
									'userInfo' => &$this->userInfo
									)),
								);
						}						
						}					
								
					} else {
						if ( $partner == 'sina.com' && Better_Functions::isCell($id) ) {
							$_email = $service->tid . '@t.sina.com';
							$user = Better_User::getInstance($_email, 'email');
							if (!$user) {
								$user = Better_User::getInstance($id, 'email');
							}
						} else {
							$user = Better_User::getInstance($id, 'email');	
						}
						$userInfo = $user->getUserInfo();	
						if($userInfo['state']==Better_User_State::BANNED){
							$this->error('error.users.account_banned');	
							exit;
						}						
						$uid = (int)$user->uid;	
						if($uid>0){
							$synclist = Better_User_Syncsites::getInstance($uid)->getSites();
							if(isset($synclist[$partner])){
								if($synclist[$partner]['username']==$id){						
									Better_User_Syncsites::getInstance($uid)->delete($partner);
									Better_User_Syncsites::getInstance($uid)->add($partner, $id, $password, $service->_accecss_token, $service->_accecss_token_secret, $service->tid, $service->_expires_in);
								}
							} else {
								Better_User_Syncsites::getInstance($uid)->add($partner, $id, $password, $service->_accecss_token, $service->_accecss_token_secret, $service->tid, $service->_expires_in);
							}
							
							$result = 'true';
							$tmp = $this->api->loginToken($uid);
							
							$this->user = Better_User::getInstance($uid);
							
							//等待返回的信息						
							$this->data[$this->xmlRoot]['login_token'] = array(
								'username' => $tmp['username'],
								'token' => $tmp['token'],
								);
							$this->data[$this->xmlRoot]['user'] = $this->api->getTranslator('user')->translate(array(
								'data' => &$userInfo
							));			
													
							$this->data[$this->xmlRoot]['message'] = '';
	
							$this->data[$this->xmlRoot]['achievement'] = array();
							$this->data[$this->xmlRoot]['requests'] = array();
							$this->data[$this->xmlRoot]['activities'] = array();
							$this->data[$this->xmlRoot]['version'] = array();
							$this->data[$this->xmlRoot]['recommends'] = array();
							$this->data[$this->xmlRoot]['coupons'] = array();
							
							//好友请求数
							$frCount = Better_DAO_DmessageReceive::getInstance($this->uid)->getMinesCount(array(
								'type'=> array('friend_request'),
								'readed'=> 0,
							));
							if ($frCount) {
								$this->data[$this->xmlRoot]['requests'][] = array(
									'fr_message' => str_replace('{TOTAL}', $frCount, $this->lang->account->fr_requests)
									);				
							}
							
							//私信数
							$msgCount = Better_DAO_DmessageReceive::getInstance($this->uid)->getMinesCount(array(
								'type' => array('direct_message'),
								'readed' => 0
								));
							if ($msgCount) {
								$this->data[$this->xmlRoot]['requests'][] = array(
									'dm_message' => str_replace('{TOTAL}', $msgCount, $this->lang->account->dm_requests)
									);
							}
							
							//	成就
							$hasAchievement = false;
							$as = $this->user->achievement()->apiParseNew($this->lang->global->this_login);
							foreach ($as as $ac) {
								$hasAchievement = true;
								$this->data[$this->xmlRoot]['achievement'][] = array(
									'message' => $ac
									);
							}												
							
							$allocs = Better_Api_Allocate::allocate($uid);
							foreach ($allocs as $k=>$v) {
								$this->data[$this->xmlRoot][$k] = $v;
							}									
						} else if ($ver == 1) {
							$third_info['username'] = $id;
							$third_info['password']	= $password;							
										
							if ( $partner == 'sina.com' && Better_Functions::isCell($id) ) {
								$_regEmail = $service->tid . '@t.sina.com';
							} else {
								$_regEmail = $id;
							}
							$data['email'] = $_regEmail;
							$temnickname = split('@',$username);
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
					    	$data['nickname'] = $nickname;
					    	$data['password'] = $password;
					    	$data['partner'] = $this->getRequest()->getParam('kai_partner', ''); //
					    	$data['secret'] = $this->getRequest()->getParam('secret', ''); //
							$uid = Better_User_Signup::Signup($data);
							$result = 'true';
							$user = Better_User::getInstance($uid);
							
							//设置头像
							$avatarurl = $third_info['image_url'];
							if ($avatarurl) {
								$user->avatar()->upload($avatarurl, 'jpg', $uid);
							}							
							$userInfo = $user->getUserInfo();
							$uid = (int)$user->uid;
							$this->user = Better_User::getInstance($uid);
							$tmp = $this->api->loginToken($uid);
							//等待返回的信息						
							$this->data[$this->xmlRoot]['login_token'] = array(
																		'username' => $tmp['username'],
																		'token' => $tmp['token'],
																		);								
							
							Better_User_Syncsites::getInstance($uid)->add($partner, $id, $password, $service->_accecss_token, $service->_accecss_token_secret, $service->tid, $service->_expires_in);	
							Better_Log::getInstance()->logInfo(serialize($data)."RegFrom".$partner,'thirdtokai');
							
							
							
							//vip用户发邮件通知
							if ( $third_info['is_vip'] ) {
								$msg = "第三方认证用户加入开开  http://k.ai/kai" . $uid;
								$service->sendVipMail($msg);								
							}
							
							
							//寻找好友 自动加好友
							$user->friends()->autoAddFriend($uid, $partner, $service, $third_info);
							
							$this->data[$this->xmlRoot]['partner_user'] = $third_info;	
							$this->data[$this->xmlRoot]['user'] = $this->api->getTranslator('user')->translate(array(
								'data' => &$userInfo
							));		
							
							$this->data[$this->xmlRoot]['message'] = '';
							$allocs = Better_Api_Allocate::allocate($uid);
							foreach ($allocs as $k=>$v) {
								$this->data[$this->xmlRoot][$k] = $v;
							}										
	
							//	版本检查
							$platform = $this->getRequest()->getParam('platform', '');
							$model = $this->getRequest()->getParam('model', '');
							$language = 'zh-cn';
							$ver = $this->getRequest()->getParam('version', '');
							
							$testingMode = $this->getRequest()->getParam('testing_mode', '');
							
								if ($platform && $model && $ver) {
								$result1 = Better_Mobile_Upgrade::getInstance()->parse(array(
										'lang' => 'zh-cn',
										'platform' => $platform,
										'model' => $model,
										'language' => $language,
										'ver' => $ver
										));
						
									$this->user->cache()->set('client', array(
										'platform' => $platform,
										'model' => $model,
										'language' => $language,
										'ver' => $ver,
										'res' => $res,
										));			
						
									$this->data[$this->xmlRoot]['version'] = array(
									'action' => $result1['action'],
										'platform' => $platform,
										'model' => $model,
										'language' => $language,
									'ver' => $result1['ver'],
									'message' => $result1['message'],
									'code' => $result1['code'],
									'url' => $result1['url'],
										);
								
							}
							
							if ($hasAchievement) {
							//	活动
								$rows = Better_Poi_Notification::search(array(
									'lon' => $this->userInfo['lon'],
									'lat' => $this->userInfo['lat'],
									'range' => 500000,
									'page' => 1,
									'count' => 2
									));
								foreach ($rows['rows'] as $row) {
									$this->data[$this->xmlRoot]['coupons'][] = array(
										'coupon' => $this->api->getTranslator('coupon')->translate(array(
											'data' => &$row,
											'userInfo' => &$this->userInfo,
											)),
										);
								}
								
								//	推荐
								$rTips = Better_Poi_Tips::recommends(array(
									'uid' => $this->uid,
									'page' => 1,
									'page_size' => 3
									));
								foreach ($rTips['rows'] as $row) {
									$this->data[$this->xmlRoot]['recommends'][] = array(
										'status' => $this->api->getTranslator('status')->translate(array(
											'data' => &$row,
											'userInfo' => &$this->userInfo
											)),
										);
								}													
							}
						} else if ($ver == 2) {
							$third_info['username'] = $id;
							$third_info['password']	= $password;
							$this->data[$this->xmlRoot]['partner_user'] = $third_info;
							$this->data[$this->xmlRoot]['result'] = $result;
							$this->output();
						}
					}
					
					$this->data[$this->xmlRoot]['result'] = $result;
					Better_Hook::factory(array(
											'Rp'
										))->invoke('UserLogin', array(
											'uid' => $uid,
											'partner' => $partner,											
											'loginbyapi' => 1
										));
					
					Better_Log::getInstance()->logInfo($partner.'|'.$username, 'partner_login', true);
				} else {
					Better_Log::getInstance()->logInfo(serialize($this->post),'partnerpass_false');
					$this->error('error.account.partner_login_false');
				}
			} else {
				//合作方目前仅支持mail登陆
				Better_Log::getInstance()->logInfo(serialize($this->post),'partnergeshi_false');
				$this->error('error.account.partner_login_onlymail');	
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			Better_Log::getInstance()->logInfo(serialize($this->post),'partnerbuzhici_false');
			$this->error('error.account.invalid_partner');
		}
		
		$this->output();
	}

	
	/**
	 * 4.1 注册
	 * 
	 * @return
	 */
	public function registerAction()
	{
		$ver = $this->getRequest()->getParam('ver', '1');

		switch ($ver) {
			case '2':
				$this->_registerVer2();
				break;
			default:
				$this->_registerVer1();
				break;
		}

	}
	
	/**
	 * 根据手机号码查找用户
	 * 
	 * @return
	 */
	public function mobileAction()
	{
		$userInfo = $this->auth();
		
		$identify = trim($this->getRequest()->getParam('identify', ''));
		if (!Better_Functions::isCell($identify)) {
			$this->error('error.account.mobile_invalid');
		}
		
		switch ($this->todo) {
			case 'check':
				if ($identify==$userInfo['cell_no']) {
					$dispUserInfo = &$userInfo;
				} else {
					$dispUserInfo = Better_User::getInstance()->getUserByCell($identify);
				}

				if ($dispUserInfo['uid']) {
					$this->data[$this->xmlRoot] = $this->api->getTranslator('user')->translate(array(
						'data' => &$dispUserInfo,
						'userInfo' => &$userInfo
						));
				} else {
					$this->error('error.account.user_invalid');
				}
				break;
		}		
		
		$this->output();
	}
	
	/**
	 * 4.2 用户帐号验证
	 * 
	 * @return
	 */
	public function verifycredentialsAction()
	{
		$this->xmlRoot = 'login';
		$partner = $this->getRequest()->getParam('kai_partner', '');
		$secret = $this->getRequest()->getParam('secret', '');
		$polo = (bool)($this->getRequest()->getParam('polo', 'false')=='true' ? true : false);
		
		define('BETTER_IMEI', $secret);
		define('BETTER_PARTNER', $partner);
		define('BETTER_API_AUTH', true);
		
		$params = $this->getRequest()->getParams();
		Better_Registry::set('POST', $params);
		
		$this->auth();
		
		$prefix = $this->langAll->javascript->sketch->delta->action;
		$prefix = str_replace('{ACTION}', $this->langAll->javascript->global->this_login, $prefix);
			
		$message = $this->parseAchievements($prefix);
		if(preg_match('/0177/', $partner)){
			try{				
				Better_Log::getInstance()->logInfo(serialize($this->userInfo),'sohu0177login');				
			}catch (Exception $e) {			   
			}
		}
		$output = array(
			'message' => $message,
			'user' => $this->api->getTranslator('user')->translate(array(
								'data' => &$this->userInfo,
								'from_login' => true,
								)),		
			'achievement' => array(),
			'requests' => array(),
			'coupons' => array(),
			'version' => array(),
			'recommends' => array()
			);
			
		//好友请求数
		$frCount = Better_DAO_DmessageReceive::getInstance($this->uid)->getMinesCount(array(
			'type'=> array('friend_request'),
			'readed'=> 0,
		));
		if ($frCount) {
			$output['requests'][] = array(
				'fr_message' => str_replace('{TOTAL}', $frCount, $this->lang->account->fr_requests)
				);				
		}
		
		//私信数
		$msgCount = Better_DAO_DmessageReceive::getInstance($this->uid)->getMinesCount(array(
			'type' => array('direct_message'),
			'readed' => 0
			));
		if ($msgCount) {
			$output['requests'][] = array(
				'dm_message' => str_replace('{TOTAL}', $msgCount, $this->lang->account->dm_requests)
				);
		}
		
		//	成就
		$hasAchievement = false;
		$as = $this->user->achievement()->apiParseNew($this->lang->global->this_login);
		foreach ($as as $ac) {
			$hasAchievement = true;
			$output['achievement'][] = array(
				'message' => $ac
				);
		}
			
		$allocs = Better_Api_Allocate::allocate($this->uid);
		foreach ($allocs as $k=>$v) {
			$output[$k] = $v;
		}
		
		//	版本检查
		$platform = $this->getRequest()->getParam('platform', '');
		$model = $this->getRequest()->getParam('model', '');
		$language = 'zh-cn';
		$ver = $this->getRequest()->getParam('version', $this->getRequest()->getParam('ver', ''));
		$res = $this->getRequest()->getParam('res', '');
		$testingMode = $this->getRequest()->getParam('testing_mode', '');
		
		if ($testingMode) {
			$result = array(
				'action' => $testingMode,
				'message' => '['.$testingMode.'] testing upgrade',
				'code' => rand(1000,9999),
				'url' => BETTER_BASE_URL.'/files/clients/better_3rd.sisx',
				'ver' => '9.0.0',
				);			
				
			$output['version'] = array(
				'action' => $result['action'],
				'platform' => $platform,
				'model' => $model,
				'language' => $language,
				'ver' => $result['ver'],
				'message' => $result['message'],
				'code' => $result['code'],
				'url' => $result['url'],
				);				
		} else {
			if ($platform && $model && $ver) {
				$result = Better_Mobile_Upgrade::getInstance()->parse(array(
					'lang' => 'zh-cn',
					'platform' => $platform,
					'model' => $model,
					'language' => $language,
					'ver' => $ver
					));
	
				$this->user->cache()->set('client', array(
					'platform' => $platform,
					'model' => $model,
					'language' => $language,
					'ver' => $ver,
					'res' => $res,
					));			
	
				$output['version'] = array(
					'action' => $result['action'],
					'platform' => $platform,
					'model' => $model,
					'language' => $language,
					'ver' => $result['ver'],
					'message' => $result['message'],
					'code' => $result['code'],
					'url' => $result['url'],
					);
			}
		}
		if ( $hasAchievement || APPLICATION_ENV!='production' || in_array( $this->uid, array('175659', '175657') ) ) {
			//	活动
			$rows = Better_Poi_Notification::search(array(
				'lon' => $this->userInfo['lon'],
				'lat' => $this->userInfo['lat'],
				'is_top' => 1,
				'range' => 50000000,
				'page' => 1,
				'count' => 2
				));
				
				
			foreach ($rows['rows'] as $row) {
					if ($row['nid'] == '4027') {
						$row['is_top'] = 0;
					}
					if ($row['image_url']) {
						list($a, $b) = explode('.', $row['image_url']);
						if (is_numeric($a) && is_numeric($b)) {
							$attach = Better_Attachment_Parse::getInstance($row['image_url'])->result();
							$row['image_url'] = $attach['url'];
						}
					}
									
				$output['coupons'][] = array(
					'coupon' => $this->api->getTranslator('coupon')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo,
						)),
					);
			}
			
			
	
			//	推荐
			$rTips = Better_Poi_Tips::recommends(array(
				'uid' => $this->uid,
				'page' => 1,
				'page_size' => 3
				));
			foreach ($rTips['rows'] as $row) {
				$output['recommends'][] = array(
					'status' => $this->api->getTranslator('status')->translate(array(
						'data' => &$row,
						'userInfo' => &$this->userInfo
						)),
					);
			}
		
			//好友推送
			$rows = Better_DAO_PushFriend::getInstance($this->uid)->getMyMsg($this->uid);
			$lang = $this->lang->toArray();
			foreach ($rows as $row) {
				$_refuid = $row['refuid'];
				$_refusername = $row['refusername'];
				$_refimageurl = $row['refimageurl'];
				$_type = $row['type'];
				$dispUser = Better_User::getInstance($_refuid);
				$dispUserInfo = $dispUser->getUserInfo();
				if ($dispUser->isFriend($this->uid)) {
					$update_row = $row;
					$update_row['flag'] = 1;				
					$rows = Better_DAO_PushFriend::getInstance($this->uid)->update($update_row, $row);					
					continue;
				}
				$_sitename = $lang['sns'][str_replace('.', '_', $_type)]['name'];
				
				$a = $_type == 'kaixin001.com' ? '的好友' : '关注的';
				if ($_refusername) {
					$message = "你在{$_sitename}{$a}{$_refusername}(@{$dispUserInfo['nickname']}) 也在使用开开，快加他为好友吧";
				} else {
					$message = "你在{$_sitename}{$a} @{$dispUserInfo['nickname']} 也在使用开开，快加他为好友吧";
				}
				
				
				$_row['category'] = 'user';
				$_row['message'] = $message;
				$_row['image_url'] = $_refimageurl ? $_refimageurl : $dispUserInfo['avatar_normal'];
				$_row['url'] = $_refuid;
				$output['specials'][] = array(
					'special' => $this->api->getTranslator('special')->translate(array(
						'data' => &$_row,
						)),
					);
					
				//更新
				$update_row = $row;
				$update_row['flag'] = 1;				
				$rows = Better_DAO_PushFriend::getInstance($this->uid)->update($update_row, $row);
			}
			
		}
		
		
		//提到我的数量
		 $newRtCount = Better_DAO_Mentionme::getInstance($this->uid)->newMentionmeCount($this->userInfo['last_rt_mine']);
		 $output['mentionmes'] = $newRtCount ? $newRtCount : 0;
		
		$this->data[$this->xmlRoot] = &$output;

		$this->output();
	}
	
	/**
	 * 4.3 重设密码
	 * 
	 * @return
	 */
	public function resetpasswordAction()
	{
		$this->xmlRoot = 'message';
		
		$this->needPost();
		
		$email = $this->post['email'];
		if (preg_match('/^([0-9]{,20}$/', $email)) {
			if (!preg_match('/^86([0-9]{11})$/', $email) && strlen($email)==11) {
				$email = '86'.$email;
			}
			$user = Better_User::getInstance($email, 'cell');
			$userInfo = $user->getUser();
			if ($userInfo['uid']) {
				$email = $userInfo['email'];
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.account.invalid_cell');
			}
		} 
		
		$result = Better_User_Resetpwd::request(trim($email));
		
		if ($result['has_err']) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.account.email_invalid');
		} else {
			$this->data[$this->xmlRoot] = str_replace('{EMAIL}', $email, $this->lang->account->resetpwd->sent);
		}
		
		$this->output();
	}
	
	private function _registerVer1()
	{
		if (Better_Config::getAppConfig()->in_testing) {
			$this->error('error.kai.in_testing');	
		}
		
		$this->needPost();
		$ver = $this->getRequest()->getParam('ver', '1');
		
		$patInvalid = '/([\s\r\t ])/is';
		$this->post['nickname'] = preg_replace($patInvalid, '', $this->post['nickname']);
		
		$email = $this->post['email'];
		$password = $this->post['password'];
		$this->post['partner'] = $this->post['kai_partner'];
		$this->post['repassword'] = $password;
		$this->post['login_type'] = 'local';
		$_POST['login_type'] = 'local';		
		
		$result = Better_User_Signup::quickSignup($this->post);
		
		$code = $result['code'];
		$codes = &$result['codes'];

		Better_Controller::sendSquidHeaderC($code);
		
		switch ($code) {
			case $codes['USERNAME_REQUIRED']:
				$this->error('error.account.username_required');
				break;
			case $codes['EMAIL_TOO_LONG']:
				$this->error('error.account.email_too_long');
				break;
			case $codes['NICKNAME_REQUIRED']:
				$this->error('error.account.nickname_required');
				break;
			case $codes['EMAIL_INVALID']:
				$this->error('error.account.email_invalid');
				break;
			case $codes['EMAIL_EXISTS']:
				$this->error('error.account.email_exists');
				break;
			case $codes['PASSWORD_INVALID']:
				$this->error('error.account.password_invalid');
				break;
			case $codes['PASSWORD_TOO_LONG']:
				$this->error('error.account.password_too_long');
				break;
			case $codes['PASSWORD_TOO_SHORT']:
				$this->error('error.account.password_too_short');
				break;
			case $codes['PASSWORD_NOT_MATCH']:
				$this->error('error.account.password_not_match');
				break;
			case $codes['SUCCESS']:
				$this->xmlRoot = 'register';
				$msg = $result['msg'];
				if (Better_Registry::get('signupMsg')) {
					$msg .= Better_Registry::get('signupMsg');
				}
				$msg .= $this->lang->account->register->spam_email;
				
				$this->data[$this->xmlRoot] = array('message' => $msg);
				break;
			case $codes['CELL_INVALID']:
				$this->error('error.account.phone_invalid');
				break;
			case $codes['CELL_EXISTS']:
				$this->error('error.account.phone_exists');
				break;
			case $codes['NIKCNAME_TOO_SHORT']:
				$this->error('error.account.nickname_too_short');
				break;
			case $codes['NICKNAME_TOO_LONG']:
				$this->error('error.account.nickname_too_long');
				break;
			case $codes['NICKNAME_FORBIDEN_WORD']:
				$this->error('error.account.nickname_forbiden_word');
				break;
			case $codes['NICKNAME_EXISTS']:
				$this->error('error.account.nickname_exists');
				break;
			case $codes['USERNAME_TOO_LONG']:
				$this->error('error.account.username_too_long');
				break;
			case $codes['USERNAME_FORBIDEN_WORD']:
				$this->error('error.account.username_forbiden_word');
				break;
			case $codes['USERNAME_EXISTS']:
				$this->error('error.account.username_exists');
				break;
			case $codes['USERNAME_TOO_SHORT']:
				$this->error('error.account.username_too_short');
				break;
			case $codes['NICKNAME_TOO_SHORT']:
				$this->error('error.account.nickname_too_short');
				break;
								
			case $codes['FAILED']:
			default:
				$this->serverError();
				Better_Log::getInstance()->logEmerg('Register_Failed:['.json_encode($this->post), 'register');
				break;
		}
		
		$this->output();		
	}
	
	private function _registerVer2()
	{
		if (Better_Config::getAppConfig()->in_testing) {
			$this->error('error.kai.in_testing');	
		}
		
		$this->needPost();
		$ver = $this->getRequest()->getParam('ver', '1');
		
		$patInvalid = '/([\s\r\t ])/is';
		$this->post['nickname'] = preg_replace($patInvalid, '', $this->post['nickname']);
		
		$email = $this->post['email'];
		$email = Better_Functions::fixEmail($email);
		$this->post['email'] = $email;
		
		$password = $this->post['password'];
		$this->post['partner'] = $this->post['kai_partner'];
		$this->post['repassword'] = $password;
		$this->post['login_type'] = 'local';
		$_POST['login_type'] = 'local';

		$result = Better_User_Signup::quickSignupVer2($this->post);
		
		$code = $result['code'];
		$codes = &$result['codes'];

		Better_Controller::sendSquidHeaderC($code);
		
		switch ($code) {
			case $codes['USERNAME_REQUIRED']:
				$this->error('error.account.username_required');
				break;
			case $codes['EMAIL_TOO_LONG']:
				$this->error('error.account.email_too_long');
				break;
			case $codes['NICKNAME_REQUIRED']:
				$this->error('error.account.nickname_required');
				break;
			case $codes['EMAIL_INVALID']:
				$this->error('error.account.email_invalid');
				break;
			case $codes['EMAIL_EXISTS']:
				$this->error('error.account.email_exists');
				break;
			case $codes['PASSWORD_INVALID']:
				$this->error('error.account.password_invalid');
				break;
			case $codes['PASSWORD_TOO_LONG']:
				$this->error('error.account.password_too_long');
				break;
			case $codes['PASSWORD_TOO_SHORT']:
				$this->error('error.account.password_too_short');
				break;
			case $codes['PASSWORD_NOT_MATCH']:
				$this->error('error.account.password_not_match');
				break;
			case $codes['SUCCESS']:
				$this->xmlRoot = 'register';
				
				$msg = $this->lang->new->signup->success;
				$msg = str_replace('{EMAIL}', $email, $msg);
				
				$uid = $result['uid'];
				$user = Better_User::getInstance($uid);
				$userInfo = $user->getUserInfo();
				
				$this->data[$this->xmlRoot] = array(
					'message' => $msg,
					'user_concise' => $this->api->getTranslator('user_concise')->translate(array(
						'data' => &$userInfo,
						'userInfo' => &$userInfo
						)),
					);
				if(preg_match('/0015/', $this->post['partner']) && !preg_match('/00015/', $this->post['partner'])){	
					Better_Registry::get('sess')->set('sinapartner',1);				
					$regPartners = array(
						array(
							'id' => 'sina',
							'notice' => $this->lang->reg_partner->sina->notice,
							'follow' => $this->lang->reg_partner->sina->follow
							),
						);
					foreach ($regPartners as $partner) {
						$this->data[$this->xmlRoot]['partners_reg'][] = array(
							'partner_reg' => $this->api->getTranslator('partner_register')->translate(array(
								'data' => &$partner
								)),
							);
					}
				}
				break;
			case $codes['CELL_INVALID']:
				$this->error('error.account.phone_invalid');
				break;
			case $codes['CELL_EXISTS']:
				$this->error('error.account.phone_exists');
				break;
			case $codes['NIKCNAME_TOO_SHORT']:
				$this->error('error.account.nickname_too_short');
				break;
			case $codes['NICKNAME_TOO_LONG']:
				$this->error('error.account.nickname_too_long');
				break;
			case $codes['NICKNAME_FORBIDEN_WORD']:
				$this->error('error.account.nickname_forbiden_word');
				break;
			case $codes['NICKNAME_EXISTS']:
				$this->error('error.account.nickname_exists');
				break;
			case $codes['USERNAME_TOO_LONG']:
				$this->error('error.account.username_too_long');
				break;
			case $codes['USERNAME_FORBIDEN_WORD']:
				$this->error('error.account.username_forbiden_word');
				break;
			case $codes['USERNAME_EXISTS']:
				$this->error('error.account.username_exists');
				break;
			case $codes['USERNAME_TOO_SHORT']:
				$this->error('error.account.username_too_short');
				break;
			case $codes['NICKNAME_TOO_SHORT']:
				$this->error('error.account.nickname_too_short');
				break;
								
			case $codes['FAILED']:
			default:
				$this->serverError();
				Better_Log::getInstance()->logEmerg('Register_Failed:['.json_encode($this->post), 'register');
				break;
		}
		
		$this->output();		
	}

	/**
	 * 短信注册1 -- 请求串号及Modem号码
	 */
	public function smsreg1Action()
	{
		$this->needPost();
		$this->xmlRoot = 'register';

		$kai_partner = $this->getRequest()->getParam('kai_partner', '');
		$secret = $this->getRequest()->getParam('secret', '');
		$version = $this->getRequest()->getParam('version', '');
		$platform = $this->getRequest()->getParam('platform', '');
		$model = $this->getRequest()->getParam('model', '');
		$res = $this->getRequest()->getParam('res', '');

		$modem = Better_Modem_Pool::randomOne();
		$token = '#reg#' . md5(uniqid(rand()));

		if ($kai_partner == '' || strlen($kai_partner) > 50) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.kai_partner.invalid');
		} else if ($modem == '') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.modem.not_available');
		} else {
			$status = array(
				'kai_partner' => $kai_partner,
				'secret' => $secret,
				'version' => $version,
				'platform' => $platform,
				'model' => $model,
				'res' => $res,
				'modem' => $modem,
			);
			Better_DAO_Cell::getInstance()->insert(array(
				'token' => $token,
				'flag' => 1,
				'status' => serialize($status),
				'dateline' => time(),
				));
			$msg = $this->lang->smsreg1;
			$msg = str_replace('{TOKEN}', $token, $msg);
			$msg = str_replace('{MODEM}', $modem, $msg);
			$this->data[$this->xmlRoot] = array(
				'modem' => $modem,
				'token' => $token,
				'message' => $msg,
			);
		}
		$this->output();
	}

	/**
	 * 短信注册3 -- 使用串号轮询取注册结果
	 */
	public function smsreg3Action()
	{
		$this->needPost();
		$this->xmlRoot = 'register';

		$token = $this->getRequest()->getParam('token', '');
		$result = Better_DAO_Cell::getInstance()->get($token);
		$flag = $result['flag'] ? $result['flag'] : 0;
		$cell = $result['cell'] ? $result['cell'] : '';
		$status = $result['status'] ? unserialize($result['status']) : array();

		if (count($result) == 0) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.token.invalid');
		} elseif ($cell == '') { // 尚未收到用户短信
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.cell.not_available');
		} elseif ($result['flag'] != 2) { // 2011-09-14: 限制同时多次请求引起的bug
			$password = $status['password'];
			$msg = '';
	  } else {
			$s = md5(uniqid(rand()));
			$i = rand(0, strlen($s)-6);
			$password = substr($s, $i, 6); 
			$status['password'] = $password;
			$msg = '';
			$user = Better_User::getInstance(10000)->getUserByCell($cell);
			if ($user['uid']) { // 已有账号, 重置密码
				Better_User::getInstance($user['uid'])->updateUser(array(
					'password' => md5($password)
				));
				Better_DAO_Cell::getInstance()->updateByCond(array(
						'uid' => $user['uid'],
						'flag' => 3,
						'status' => serialize($status),
					), array(
						'token' => $token,
					));
				$msg = $this->lang->smsreg3->reset_password;
				$msg = str_replace('{USERNAME}', $user['username'], $msg);
				$msg = str_replace('{CELL}', $cell, $msg);
				$msg = str_replace('{PASSWORD}', $password, $msg);
				$flag = 3;
				// 重置密码发私信
				Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
					'receiver' => $user['uid'],
					'content' => $msg,
					'skip_filter' => 1,
				));
			} else { // 注册新账号
				$nickname = $cell;
				$checkid = Better_User_Signup::getInstance(10000)->validNickname($nickname, 0);
				switch ($checkid['code']) {
					case $checkid['codes']['VALID']:
						break;
					case $checkid['codes']['NICKNAME_EXISTS']:
						$i = 0;
						while($checkid['code'] != $checkid['codes']['VALID']){
							$nicktemp = $nickname . "_" . rand(1, 99);
							$checkid = Better_User_Signup::getInstance(10000)->validNickname($nicktemp, 0);
							$i++;
							if ($i > 100) {
								$nicktemp = '';
								break;
							}
						}
						$nickname = $nicktemp;
						break;
					default:
						$nickname = '';
						break;
				}
				$data = array(
					'nickname' => $nickname,
					'email' => $cell . '@m.k.ai',
					'password' => $password,
					// 'cell_no' => $cell, // 手机号码直接更新, 调用signup会走bindcell逻辑
					'partner' => $status['kai_partner'],
					'secret' => $status['secret'],
				);
				$uid = Better_User_Signup::signup($data);
				if ($uid > 0) {
					Better_DAO_Cell::getInstance()->updateByCond(array(
						'uid' => $uid,
						'flag' => 4,
						'status' => serialize($status),
					), array(
						'token' => $token,
					));
					Better_User::getInstance($uid)->updateUser(array('cell_no' => $cell));
					$user = Better_User::getInstance($uid)->getUser();
					$msg = $this->lang->smsreg3->reg_success;
					$msg = str_replace('{USERNAME}', $user['username'], $msg);
					$msg = str_replace('{CELL}', $cell, $msg);
					$msg = str_replace('{PASSWORD}', $password, $msg);
					$flag = 4;
					// 注册成功发私信
					Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
						'receiver' => $uid,
						'content' => $msg,
						'skip_filter' => 1,
					));
				} else {
					$msg = $this->lang->smsreg3->reg_fail;
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.register_new.fail');
				}
			}
		}
		if (!$this->data[$this->xmlRoot]['error']) {
  		$this->data[$this->xmlRoot] = array(
  				'flag' => $flag,
  				'cell' => $cell,
  				'password' => $password,
  				'message' => $msg,
  		);
		}		
		$this->output();
	}

	public function smsreg4Action()
	{
		$genders = array('male', 'female', 'secret');
		$this->needPost();
		$this->xmlRoot = 'register';
		$token = $this->getRequest()->getParam('token', '');
		$uid = $this->getRequest()->getParam('uid', 0);
		$nickname = $this->getRequest()->getParam('nickname', '');
		$gender = $this->getRequest()->getParam('gender', '');

		$regcell = Better_DAO_Cell::getInstance()->get($token);
		if ($regcell['uid'] != 0 && $uid == 0) $uid = $regcell['uid'];
		$user = Better_User::getInstance($uid)->getUser();

		if ($token == '' && $uid == '') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.token.invalid');
		} else if ($token != '' && count($regcell) == 0 && $uid == 0) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.token.invalid');
		} else if ($uid != '' && $user['uid'] == 0) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.uid.invalid');
		} else if ($nickname == '') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.nickname.invalid');
		} else if (!in_array($gender, $genders)) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.gender.invalid');
		} else {
			if ($user['nickname'] != $nickname) {
				$checkid = Better_User_Signup::getInstance(10000)->validNickname($nickname, 0);
				if($checkid['code'] != $checkid['codes']['VALID']) {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.nickname.exists');
				}
			}
			Better_User::getInstance($uid)->updateUser(array(
				'nickname' => $nickname,
				'gender' => $gender,
			));
			Better_DAO_Cell::getInstance()->updateByCond(array(
				'flag' => 5,
			), array(
				'token' => $token,
			));
			$user['nickname'] = $nickname;
			$user['gender'] = $gender;
			$msg = $this->lang->smsreg4; 
			$this->data[$this->xmlRoot] = array(
				'user' => $this->api->getTranslator('user')->translate(array('data' => &$user)),
				'message' => $msg,
			);
		}
		$this->output();
	}

}