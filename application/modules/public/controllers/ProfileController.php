<?php

/**
 * 设置API
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Public_ProfileController extends Better_Controller_Public
{
	public function init()
	{
		parent::init();
		
		$this->auth();
	}		
	
	/**
	 * 14.11 提示语言设置
	 * 
	 * @return
	 */
	public function languageAction()
	{
		
		$this->xmlRoot = 'lang';
		$this->needPost();
		
		$lang = $this->getRequest()->getParam('lang', 'zh-cn');
		
		$data = array(
			'language' => $lang,
			);
		$this->user->updateUser($data);
		
		$this->data[$this->xmlRoot] = $lang;
		$this->output();
	}
	
	/**
	 * 14.9 更新用户隐私设置
	 * 
	 * @return
	 */
	public function updateprivacyAction()
	{		
		$this->xmlRoot = 'user_concise';
		$this->needPost();
		$data = array();
		
		if (isset($this->post['protected'])) {
			$data['priv_blog'] = $this->post['protected']=='true' ? 1 : 0;
		}
		
		$now = time();
		$update_priv_blog_times = Better_User::getInstance($this->uid)->cache()->get('update_priv_blog_times');
		if($now-$update_priv_blog_times<Better_Config::getAppConfig()->update_priv_blog_time_limit){			
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.priv_blog_time_limit');
		} else {
			Better_User::getInstance($this->uid)->cache()->set('update_priv_blog_times',$now);
			if (count($data)>0) {
				$this->user->updateUser($data);
				$showUserInfo = array_merge($this->userInfo, $data);
				$this->data[$this->xmlRoot] = $this->api->getTranslator('user_concise')->translate(array(
					'data' => &$showUserInfo
					));
			}	else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.profile.invalid_request');
			}	
		}
		
		$this->output();		
	}
	

	/**
	 * 14.10 修改密码
	 * 
	 * @return 
	 */
	public function updatepasswordAction()
	{		
		$this->xmlRoot = 'message';
		$this->needPost();
		
		$oldPass = $this->post['oldpassword'];
		$password = $this->post['password'];
		
		if (strlen($password)==0) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.account.password_invalid');
		} else if ($this->userInfo['password']!=md5(md5($oldPass).$this->userInfo['salt'])) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.invalid_old_password');
		} else {
			Better_User::getInstance($this->uid)->updateUser(array(
				'password' => md5($password)
				));
			$this->data[$this->xmlRoot] = $this->lang->account->password_modified;
		}
		
		$this->output();
	}	
	
	/**
	 * 14.8 绑定第三方微博账号
	 * 
	 * @return
	 */
	public function bindsnsAction()
	{
		$this->auth();
		$this->xmlRoot = 'message';
		$this->needPost();
		
		$sns = $this->post['sns'] ?  $this->post['sns'] : $this->post['domain'];
		$sns = trim($sns);
		$id = trim($this->post['id']);
		$password = trim($this->post['password']);
		$bind = (bool)($this->getRequest()->getParam('bind', 'true')=='false' ? false : true);
		$followkai = (bool)($this->getRequest()->getParam('followkai', 'false') == 'false' ? false : true);
		$key = '';
		
		switch($sns) {
			case 'msn':
			case 'msn.com':
				$key = 'msn.com';
				break;
			case '9911':
			case '9911.com':
				$key = '';
				break;
			case 'zuosa':
			case 'zuosa.com':
				$key = 'zuosa.com';
				break;
			case '51':
			case '51.com':
				$key = '51.com';
				break;
			case 'follow5':
			case 'follow5.com':
				$key = 'follow5.com';
				break;
				
			case '139':
			case '139.com':
				$key = '139.com';
				break;				
				
			case 'kaixin':
			case 'kaixin.com':
				$key = 'kaixin.com';
				break;
			case 'kaixin001':
			case 'kaixin001.com':
				$key = 'kaixin001.com';
				break;
			case 'renren':
			case 'renren.com':
				$key = 'renren.com';
				break;
			case 'sina':
			case 'sina.com':
				$key = 'sina.com';
				break;
			case 'digu':
			case 'digu.com':
				$key = 'digu.com';
				break;
			case 'tongxue':
			case 'tongxue.com':
				$key = 'tongxue.com';
				break;
			case 'douban':
			case 'douban.com':
				$key = 'douban.com';
				break;
			case 'twitter':
			case 'twitter.com':
				$key = 'twitter.com';
				break;
			
			case 'facebook':
			case 'facebook.com':
				$key = 'facebook.com';
				break;				
				
			case 'sohu':
			case 'sohu.com':
				$key = 'sohu.com';
				break;
			case '4sq':
			case 'foursqaure':
			case '4sq.com':
			case 'foursqaure.com':
				$key = '4sq.com';
				break;
			case 'fanfou':
			case 'fanfou.com':
				$key = 'fanfou.com';
				break;
			case '163':
			case '163.com':
				$key = '163.com';
				break;
			case 'qq':
			case 'qq.com':
				$key = 'qq.com';
				break;				
			case 'bedo':
			case 'bedo.cn':
				$key = '';				
				break;
		}
		
		if ($key!='') {
			if ($bind===false) {
				
				if ($key=='139.com') {
					$this->error('error.profile.139_error');
				}
								
				$this->user->syncsites()->delete($key);
				$this->data[$this->xmlRoot] = $this->lang->profile->bindsns->unbinded;
			} else {
				
				if ($key=='twitter.com') {
					$this->error('error.profile.twitter_error');
				} else if ($key=='douban.com') {
					$this->error('error.profile.douban_error');		
				} else if ($key=='facebook.com') {
					$this->error('error.profile.facebook_error');
				} else if ($key=='139.com') {
					$this->error('error.profile.139_error');														
				} else if ($key=='qq.com') {
					$this->error('error.profile.qq_error');														
				} else if ($key=='163.com') {
					$this->error('error.profile.163_error');														
				} else {
					$service = Better_Service_PushToOtherSites::factory($key, $id, $password, '', '');
					
					if ($key != 'renren.com') {
						$ck = $service->checkAccount($this->user->uid, $key, $id);
						if ($ck) {
							$logined = $service->fakeLogin();
							if ( $logined && $key == 'sina.com') {
								$bind_user = Better_DAO_ThirdBinding::getBindUser($key, $service->tid);
								$bind_user && $logined = 2;
							}
						} else {
							$logined = 2;//重复绑定
						}
					} else {
						$logined = $service->fakeLoginApi();
						if ($logined) {
							$logined = true;
							$service->tid && $bind_user = Better_DAO_ThirdBinding::getBindUser($key, $service->tid);
							if ($bind_user) {
								$logined = 2;//重复绑定
							}
						}
					}
				}
				
				if ($logined === true) {
					$_accecss_token = $_accecss_token_secret = '';
					$_expires_in = 0;
					if ( in_array( $key, array( 'sina.com', 'renren.com' ) ) ) {
						$_accecss_token = $service->_accecss_token;	
						$_accecss_token_secret = $service->_accecss_token_secret;

						$_expires_in = $service->_expires_in + time(); 
					}
					if ($key =='douban.com') exit(); //豆瓣这不是你可以来的地方哦
						
					$this->user->syncsites()->add($key, $id, $password, $_accecss_token, $_accecss_token_secret, $service->tid, $_expires_in);
					if ($followkai) { //关注开开
						$service->followKai();
					}
						
					$ac = $this->parseAchievements();
					trim($ac)=='' || $ac = ' '.$this->langAll->javascript->sketch->noshowaction->delta->action.$ac;
					$this->data[$this->xmlRoot] = $this->lang->profile->bindsns->success.$ac;
					
					Better_Controller::sendSquidHeaderC(1);
				} elseif ($logined === 2){
					Better_Controller::sendSquidHeaderC(-3);
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.bindsns.login_failed_2');					
				} else {
					Better_Controller::sendSquidHeaderC(-1);
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.bindsns.login_failed');
				}
			}
		} else {
			Better_Controller::sendSquidHeaderC(-2);
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.bindsns.sns_not_valid');	
		}				
		
		$this->output();
	}

	/**
	 * 14.7 绑定im
	 * 
	 * @note 该api需要一个protocol参数
	 * 
	 * @return
	 */
	public function bindimAction()
	{
		
		$this->xmlRoot = 'message';
		$this->needPost();

		$id = trim($this->getRequest()->getParam('id', ''));
		$password = $this->getRequest()->getParam('password', '');
		$partner = strtolower($this->getRequest()->getParam('partner', 'msn'));
		$bind = (bool)($this->getRequest()->getParam('bind', 'true')=='false' ? false : true);

		if (in_array($partner, Better_User_Bind_Im::$allowedProtocols) && Better_Functions::checkEmail($id)) {
			if ($bind===false) {
				$message = $this->user->bind_Im()->unbind($id,$partner) ? $this->lang->profile->bindim->unbind->success : $this->lang->profile->bindim->unbind->failed;
				$this->data[$this->xmlRoot] = $message;
			} else {
				$bot = $this->user->bind_Im()->request($partner, $id);

				if (Better_Functions::checkEmail($bot)) {
					$message = str_replace('{ROBOT}', $bot, $this->lang->profile->bindim->msn->success);
					$this->data[$this->xmlRoot] = $message;
				} else if ($bot=='ROBOT_UNAVAILABLE') {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.bindim.robot_unvailable');
				} else if ($bot=='HAS_BINDED') {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.bindim.binded');
				} else if ($bot=='PROTOCOL_NOT_ALLOWED') {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.bindim.invalid_protocol');
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->serverError();
				}
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.bindim.protocol_not_valid');
		}
		
		$this->output();
	}
	
	/**
	 * 14.6 绑定iphone
	 * 
	 * @return
	 */
	public function bindiphoneAction()
	{
		
		$this->xmlRoot = 'message';
		$this->needPost();
		
		$token = trim($this->getRequest()->getParam('token', ''));
		$allow = trim($this->getRequest()->getParam('allow', ''));
		
		switch ($allow) {
			case 'true':
				if ($token) {
					if (Better_Phone_Apple::bind($this->uid, $token)) {
						$this->data[$this->xmlRoot] = $this->lang->bindiphone->success;
					} else {
						$this->serverError();
					}						
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.bindiphone.invalid_token');
				}

				break;
			case 'false':
			case 'off':
				if (Better_Phone_Apple::unbind($this->uid, $token)) {
					$this->data[$this->xmlRoot] = $this->lang->unbindiphone->success;
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->serverError();
				}
				break;
			default:
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.profile.bindiphone.invalid_allow_param');
				break;
		}			

		$this->output();
	}
	
	/**
	 * 14.5 绑定手机
	 * 
	 * @return
	 */
	public function bindphoneAction()
	{
		$this->needPost();
		
		$this->xmlRoot = 'validate';
		$phone = trim($this->getRequest()->getParam('phone', ''));

		if (preg_match(Better_User::CELL_PAT, $phone)) {
			$phone = '86'.$phone;
		}
		
		if ($this->user->bind_Cell()->request($phone)) {
			$message = str_replace('{UID}', $this->uid, $this->lang->profile->bindphone->tips);
			$message = str_replace('{CELL}', $this->config->cell->robot, $message);
			
			$this->data[$this->xmlRoot] = array(
				'phone' => $phone,
				'mobile' => $this->config->cell->robot,
				'code' => 'bd'.$this->uid,
				'message' => $message,
				);
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.phone_exists');
		}
		
		$this->output();
		
	}
	
	/**
	 * 14.4删除头像
	 * 
	 * @return
	 */
	public function removeimageAction()
	{
		$this->xmlRoot = 'message';
		
		Better_User::getInstance($this->uid)->avatar()->delete();
		
		$this->data[$this->xmlRoot] = $this->lang->profile->avatar->deleted;
		
		$this->output();
	}
	
	/**
	 * 14.3更新头像
	 * 
	 * @return
	 */
	public function uploadimageAction()
	{		
		$this->xmlRoot = 'message';
		
		if (is_array($_FILES) && isset($_FILES['photo'])) {
			$_FILES['myfile'] = &$_FILES['photo'];
			$result = $this->user->avatar()->upload();
			
			if (is_array($result) && isset($result['url'])) {
				$this->data[$this->xmlRoot] = $this->lang->profile->avatar->updated;
			} else {	
				switch ($result) {
					case Better_Attachment_Save::$FILE_TO_LARGE:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.image.file_too_large');
						break;
					case Better_Attachment_Save::$EXT_NOT_PERMITTED:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.image.ext_not_permitted');
						break;
					case Better_Attachment_Save::$MIME_NOT_PERMITTED:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.image.mime_not_permitted');
						break;
					default:
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.profile.upload.code_'.$result);
						break;
					
				}
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.avatar.invalid_file');
		}
		
		$this->output();
	}
	
	/**
	 * 14.1 获得帐号信息
	 * 
	 * @return
	 */
	public function retrieveAction()
	{
		$this->xmlRoot = 'profile';
		$id = (int)$this->getRequest()->getParam('id', $this->uid);
		
		if ($id>0) {
			$user = Better_User::getInstance($id);
			$user->getUser();
			$userInfo = $user->parseUserFull();
			
			if ($userInfo['uid']) {
				$this->data[$this->xmlRoot] = $this->api->getTranslator('profile')->translate(array(
					'data' => &$userInfo,
					'userInfo' => &$this->userInfo,
					));
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.profile.invalid_user');
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.invalid_user');
		}
		
		$this->output();
	}
	
	/**
	 * 更新基本信息
	 * 
	 * @return
	 */
	public function updatebasicsAction()
	{
		$ver = $this->getRequest()->getParam('ver', '1');
		
		switch ($ver) {
			case '2':
				$this->_updatebasicsVer2();
				break;
			default:
				$this->_updatebasicsVer1();
				break;
		}
	}
	
	/**
	 * 
	 */
	protected function needValidate($inReg=false)
	{
		if ($this->user->needValidate($inReg)) {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.update_validating');
		}
	}
	
	private function _updatebasicsVer2()
	{
		$this->xmlRoot = 'message';
		$this->needPost();
		
		$username = trim(urldecode($this->getRequest()->getParam('username', '')));
		$gender = trim($this->getRequest()->getParam('gender', ''));
		$birthday = trim(urldecode($this->getRequest()->getParam('birthday', '')));
		$description = trim(urldecode($this->getRequest()->getParam('description', '')));
		$return = $this->getRequest()->getParam('return', 'msg')=='user' ? 'user' : 'msg';
		
		$patInvalid = '/([\s\r\t ])/is';

		if ($username!='' && $username!=$this->userInfo['username']) {
			$result = Better_User_Signup::validUsername($username, $this->uid);
			$codes = &$result['codes'];

			switch ($result['code']) {
				case $codes['TOO_SHORT']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_too_short');
					break;
				case $codes['TOO_LONG']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_too_long');
					break;
				case $codes['ILLEGAL_CHARACTER']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_illegal_character');
					break;
				case $codes['ONLY_NUMBER']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_only_number');
					break;
				case $codes['ONLY_NUMBER_CHARACTER']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_only_number_character');
					break;
				case $codes['EXISTS']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_exists');
					break;
				case $codes['VALID']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					break;
				case $codes['INVALID']:
				default:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_unknown');
					break;
			}
		}
		
		$params = array();
		($username!='' && $username!=$this->userInfo['username']) && $params['username'] = $username;
		$gender!='' && $params['gender'] = $gender;
		$birthday!='' && $params['birthday'] = $birthday;
		$params['self_intro'] = $description;
		
		if($description && Better_Filter::getInstance()->filterBanwords($description)){
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.account.selfintro.ban_words');
		}

		if (count($params)>0) {
			$result = $this->user->updateUser($params);
			if ($result) {
				if ($return=='msg') {
					$this->data[$this->xmlRoot] = $this->lang->profile->update_success;
				} else {
					$userInfo = $this->user->getUserInfo();
					$this->data['user'] = $this->api->getTranslator('user')->translate(array(
						'data' => $userInfo
						));
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->serverError();
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.param_required');
		}
		
		$this->output();
	}
	
	private function _updatebasicsVer1()
	{
		$this->xmlRoot = 'message';
		$this->needPost();
		
		$email = trim(urldecode($this->getRequest()->getParam('email', '')));
		$username = trim(urldecode($this->getRequest()->getParam('username', '')));
		$screenName = trim(urldecode($this->getRequest()->getParam('nickname', '')));
		$nickname = &$screenName;
		$gender = trim($this->getRequest()->getParam('gender', ''));
		$province = trim(urldecode($this->getRequest()->getParam('province', '')));
		$city = trim(urldecode($this->getRequest()->getParam('city', '')));
		
		$birthday = trim(urldecode($this->getRequest()->getParam('birthday', '')));
		$description = trim(urldecode($this->getRequest()->getParam('description', '')));
		$return = $this->getRequest()->getParam('return', 'msg')=='user' ? 'user' : 'msg';
		
		$patInvalid = '/([\s\r\t ])/is';
		$screenName = preg_replace($patInvalid, '', $screenName);				

		if ($email!='' && $email!=strtolower($this->userInfo['email'])) {
			if ($this->user->needValidate()) {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->error('error.profile.update_validating');
			} else {
				if (Better_Functions::checkEmail($email)) {
					if ($this->user->exists()->email($email)) {
						$this->errorDetail = __METHOD__.':'.__LINE__;
						$this->error('error.profile.email_duplicated');
					}
				} else {
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.invalid_email');
				}
			}
		} else if ($email=='') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.invalid_email');
		}

		if ($username!='' && $username!=$this->userInfo['username']) {
			$result = Better_User_Signup::validUsername($username, $this->uid);
			$codes = &$result['codes'];

			switch ($result['code']) {
				case $codes['TOO_SHORT']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_too_short');
					break;
				case $codes['TOO_LONG']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_too_long');
					break;
				case $codes['ILLEGAL_CHARACTER']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_illegal_character');
					break;
				case $codes['ONLY_NUMBER']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_only_number');
					break;
				case $codes['ONLY_NUMBER_CHARACTER']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_only_number_character');
					break;
				case $codes['EXISTS']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_exists');
					break;
				case $codes['VALID']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					break;
				case $codes['INVALID']:
				default:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.profile.username_unknown');
					break;
			}
		} else if ($username=='') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.username_too_short');
		}
		
		if ($screenName!='' && $screenName!=$this->userInfo['nickname']) {
			$result = Better_User_Signup::validNickname($screenName, $this->uid);
			$codes = &$result['codes'];		

			switch ($result['code']) {
				case $codes['NICKNAME_TOO_LONG']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.account.nickname_too_long');
					break;
				case $codes['NICKNAME_FORBIDEN_WORD']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.account.nickname_forbiden_word');
					break;
				case $codes['NICKNAME_EXISTS']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.account.nickname_exists');
					break;
				case $codes['NICKNAME_TOO_SHORT']:
					$this->errorDetail = __METHOD__.':'.__LINE__;
					$this->error('error.account.nickname_too_short');
					break;
				case $codes['VALID']:
					break;
				default:
					break;				
			}
		} else if ($screenName=='') {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.account.nickname_too_short');
		}
		
		$params = array();
		($email!='' && $email!=$this->userInfo['email']) && $params['email'] = $email;
		($username!='' && $username!=$this->userInfo['username']) && $params['username'] = $username;
		$gender!='' && $params['gender'] = $gender;
		
		if ($province != '') {
			$params['live_province'] = $params['province'] = $province;
		}
		
		if ( $city != '' ) {
			$params['live_city'] = $params['city'] = $city;
		} 
	
		$birthday!='' && $params['birthday'] = $birthday;
		$params['self_intro'] = $description;
		$screenName!='' && $params['nickname'] = $screenName;
		
		if($description && Better_Filter::getInstance()->filterBanwords($description)){
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.account.selfintro.ban_words');
		}

		if (count($params)>0) {
			$result = $this->user->updateUser($params);
			if ($result) {
				if ($return=='msg') {
					if ($params['email'] && $params['email']!=$this->userInfo['email']) {
						$addMsg .= ' '.$this->lang->profile->email_changed;
					}

					$this->data[$this->xmlRoot] = $this->lang->profile->update_success.$addMsg;
				} else {
					$userInfo = $this->user->getUserInfo();
					$params['email'] && $userInfo['email'] = $this->userInfo['email'];
					
					$this->data['user'] = $this->api->getTranslator('user')->translate(array(
						'data' => $userInfo
						));
				}
			} else {
				$this->errorDetail = __METHOD__.':'.__LINE__;
				$this->serverError();
			}
		} else {
			$this->errorDetail = __METHOD__.':'.__LINE__;
			$this->error('error.profile.param_required');
		}
		
		$this->output();		
	}
	
}