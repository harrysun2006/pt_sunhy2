<?php

/**
 * 用户帐号
 * 
 * @package 
 * @author 
 *
 */
class Public_AccountController extends Better_Controller_Public
{
	public function init()
	{
		parent::init();
		
		//$this->auth();
		$this->xmlRoot = 'user';
	}	
	
	/**
	 * 验证账号
	 * @return unknown_type
	 */
	public function verifycredentialsAction()
	{
		$this->xmlRoot = 'login';
		$partner = $this->getRequest()->getParam('kai_partner', '');
		define('BETTER_PARTNER', $partner);
		define('BETTER_API_AUTH', true);
		
		$this->auth();	
		
		$output = array(
			'user' => $this->api->getTranslator('user')->translate(array(
								'data' => &$this->userInfo,
								'from_login' => true,
								)),		
			);
		
		$this->data[$this->xmlRoot] = $output;
		
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
						$user = Better_User::getInstance($id, 'email');					
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
										
							$data['email'] = $id;
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
							$user = Better_User::getInstance($username, 'email');
							//设置头像
							$avatarurl = $third_info['image_url'];
							if ($avatarurl) {
								$user->avatar()->upload($avatarurl, 'jpg', $uid);
							}							
							$userInfo = $user->getUserInfo();
							$uid = (int)$user->uid;
							Better_User_Syncsites::getInstance($uid)->add($partner, $id, $password, $service->_accecss_token, $service->_accecss_token_secret, $service->tid, $service->_expires_in);	
							Better_Log::getInstance()->logInfo(serialize($data)."RegFrom".$partner,'thirdtokai');										
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
	

}