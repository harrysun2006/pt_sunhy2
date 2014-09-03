<?php

/**
 * 前台用户注册控制器
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class SignupController extends Better_Controller_Front 
{
	
	public function init()
	{
		parent::init();
		$this->commonMeta();
		
	    $this->view->headScript()->appendFile($this->jsUrl.'/controllers/signup.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
   			
	    $this->view->post = $this->getRequest()->isPost() ? $this->getRequest()->getPost() : array();
	}
	
	/**
	 * 注册表单页面显示
	 *
	 * @return null
	 */
	public function indexAction()
	{
		$ref = $this->getRequest()->getParam('ref');
		if ($ref) {
			Better_Registry::get('sess')->set('ref_uid', $ref);
		}	
		$mail_partner = $this->getRequest()->getParam('mailpartner');
     	if($mail_partner){
            Better_Registry::get('sess')->set('mail_partner', $mail_partner);
       	}   
       	$this->view->midhadlogin = 1; 	
	}

	/**
	 * 激活帐号操作
	 *
	 *
	 * @retrn null
	 */
	public function enableAction()
	{
		$h = $this->getRequest()->getParam('h', '');
 
		if ($h) {
			if (preg_match('/^([a-z0-9]){32}$/is', $h)) {
				$row = Better_User_Bind_Email::hasRequest($h);
				$ruid = $row['uid'];
				$email = $row['email'];

				if ($ruid>0) {
					Better_User_Bind_Email::getInstance($ruid)->bind($email);
					$this->_helper->getHelper('Redirector')->gotoUrl('/signup/enabled');
					exit(0);
				} else {
					$this->_helper->getHelper('Redirector')->gotoUrl('/home');
					exit(0);
				}
			} else {
				$this->_helper->getHelper('Redirector')->gotoUrl('/home');
				exit(0);
			}
		} else {
			$this->_helper->getHelper('Redirector')->gotoUrl('/home');
			exit(0);
		}
	}
	
	public function termsAction()
	{
		$this->view->css = 'default';
	}
	public function aboutusAction()
	{
		$this->view->css = 'default';		
	}
	public function enabledAction()
	{
	}
	
	public function resendAction()
	{
		
	}
	
	public function doresendAction()
	{
		$post = &$this->view->post;
		$err = '';
		$email = trim($post['email']);
		$result = '';
		$user = array();
		
		if ($email=='') {
			$err = $this->lang->error->email_invalid;
		} else if (!Better_Functions::checkEmail($email)) {
			$err = $this->lang->error->email_invalid;
		} else {
			$uid = Better_Email_Bind::hasQueue($email);
			if ($uid) {
				$user = Better_User::getInstance($uid)->getUser();
				if (!isset($user['email'])) {
					$err =$this->lang->error->email_not_exists;
				} else if ($user['enabled']=='1') {
					$err = $this->lang->error->email_has_been_actived;
				}
			} else {
				$err = $this->lang->signup->active->email_not_found;
			}
		}

		if ($err=='') {
			$user['email'] = $email;
			Better_Email_Bind::send($user);
			$result = str_replace('{EMAIL}', $email, $this->lang->signup->active->sent);
		}
		
		echo json_encode(array(
					'err' => $err,
					'result' => $result,
					));
		exit(0);
	}
	
	/**
	 * 执行注册操作
	 *
	 * @return null
	 */
	public function doAction()
	{
		$post = &$this->view->post;
		$this->view->post = $post;

		$post['partner'] = $post['partner'] != '' ? $post['partner'] : 'web';
		
		if ($post['partner'] != 'bedo') {
			$err = $this->_check(false);
		}
		
		$invitecode = $post['invitecode'];
		
		$patInvalid = '/([\s\r\t ])/is';
		$post['nickname'] = preg_replace($patInvalid, '', $post['nickname']);
				
		if ($post['partner'] == 'bedo') {
			$err = array();
			$r = Better_Service_BedoBinding::getInstance()->getValidInfo($post['jid'], $post['password'], $post['nickname']);
			$post['email'] = $r['email'];
			$post['nickname'] = $r['username'];
			$post['password'] = $r['password'];
			$post['repassword'] = $r['password'];
			$changeNickname = $r['changeNickname'];
			$changePassword = $r['changePassword'];
		}

		if (count($err)>0) {
			$this->view->err = $err;
			echo $this->view->render('signup/index.phtml');
			exit(0);
		} else {
			if(Better_Registry::get('sess')->get('mail_partner')){
				$post['partner'] = Better_Registry::get('sess')->get('mail_partner');
			}
			$cache = Better_Cache::remote();
			$key = md5('signup_mail_' . $post['email']);
			$r = $cache->add($key, $post['email'], 10);
			if ($r) {
				$uid = Better_User_Signup::signup($post);
			} else {
				Better_Log::getInstance()->log(serialize($post), 'signupError', false);
				throw new Better_Exception($this->lang->error->signup->reg_failed);
			}
			Better_Log::getInstance()->logIno($uid."\n",'third_login');	
			if (!$uid) {
				throw new Better_Exception($this->lang->error->signup->reg_failed);
			}else{
				
			if ($post['partner'] == 'bedo') {
			$return = Better_Service_BedoBinding::getInstance()->bind($uid, $post['jid'], $post['bedopass']);
			switch($return) {
				case 'binded':
					$return = $this->lang->setting->import->err->binded;
					$err['err_email'] = $return;
				break;
				case 'none':
					$return = $this->lang->setting->import->err->none;
					$err['err_email'] = $return;
				break;
				case 'bedobinded':
					$return = $this->lang->setting->import->err->bedobinded;
					$err['err_email'] = $return;
				break;
				case 'errpass':
					$return = $this->lang->setting->import->err->errpass;
					$err['err_password'] = $return;
				break;
				case 'ok':
					Better_Service_BedoBinding::getInstance()->syncInfo($post['jid']);
				break;
			}
			if ($return != 'ok') {
				$this->view->inputemail = $post['email'];
				$this->view->nickname = $post['nickname'];
				$this->view->password = $post['password'];
				$this->view->repassword = $post['repassword'];
				$this->view->jid = $post['jid'];
				$this->view->bedopass = $post['bedopass'];
				
				$this->view->err = $err;
				echo $this->view->render('signup/bedosignup.phtml');
				
				exit(0);
			}
		}
				$signupbythrid = 0;
				if(Better_Registry::get('sess')->get('qqdobind') || Better_Registry::get('sess')->get('163dobind') || Better_Registry::get('sess')->get('renrendobind')){
						Better_Log::getInstance()->logInfo($uid,'step2sync');
						$protocol_site = Better_Registry::get('sess')->get('protocol_site');
						$protocol_username = Better_Registry::get('sess')->get('protocol_username');
						$protocol_callback = Better_Registry::get('sess')->get('protocol_callback');
						$protocol_oauthtoken = Better_Registry::get('sess')->get('protocol_oauthtoken');
						$protocol_oauthtokensecret = Better_Registry::get('sess')->get('protocol_oauthtokensecret');
						$protocol_tid = Better_Registry::get('sess')->get('protocol_tid');
						Better_User_Syncsites::getInstance($uid)->add($protocol_site, $protocol_username, $protocol_callback, $protocol_oauthtoken, $protocol_oauthtokensecret,$protocol_tid);	
						Better_Registry::get('sess')->set('qqdobind','');
						Better_Registry::get('sess')->set('163dobind','');	
						Better_Registry::get('sess')->set('renrendobind','');			
						$signupbythrid = 1;		
					} else if(Better_Registry::get('sess')->get('snstothird')){		
						Better_Log::getInstance()->logInfo($uid,'step2sync');	
						$protocol_site = Better_Registry::get('sess')->get('protocol_site');
						$protocol_username = Better_Registry::get('sess')->get('protocol_email');
						$protocol_password = Better_Registry::get('sess')->get('protocol_password');
						$protocol_tid = Better_Registry::get('sess')->get('protocol_tid');			
						Better_User_Syncsites::getInstance($uid)->add($protocol_site, $protocol_username, $protocol_password,'','',$protocol_tid);	
						Better_Registry::get('sess')->set('snstothird','');		
						$signupbythrid = 1;						
					}
					
				if($invitecode){
					$result=Better_Invitecode::getInstance()->deleteInvitecode($invitecode);
					if(!$result){
						throw new Better_Exception('');
					}
				}
				
				if ($post['partner'] != 'bedo') {
				list($foo, $domain) = explode('@', $post['email']);
				switch (strtolower($domain)) {
					case '163.com':
					case '126.com':
					case 'yeah.net':
					case 'sohu.com':
					case 'sina.com':
					case 'vip.sina.com':
					case 'sina.cn':
					case 'tom.com':
					case 'chinaren.com':
					case 'qq.com':
					case 'foxmail.com':
						$email = '<a href="http://mail.'.strtolower($domain).'" target="_blank">'.$post['email'].'</a>';
						break;
					case 'gmail.com':
						$email = '<a href="http://mail.google.com" target="_blank">'.$post['email'].'</a>';
						break;
					case 'hotmail.com':
					case 'live.com':
					case 'live.cn':
					case 'msn.com':
						$email = '<a href="http://www.hotmail.com" target="_blank">'.$post['email'].'</a>';
						break;
					default:
						$email = '<a href="http://www.'.$domain.'" target="_blank">'.$post['email'].'</a>';
						break;
				}
				$this->view->activeTips = str_replace('{EMAIL}', $email, $this->lang->signup->active->resend);
				}
				
				//注册后自动登录
				Better_Registry::get('sess')->set('uid', $uid);
				$ref_uid = $post['ref_uid'];
				if($ref_uid>0){
					if (APPLICATION_ENV!='production') {
						Better_Log::getInstance()->Loginfo($uid."**".$ref_uid,'xxxqq');
					}
					Better_User_Friends::getInstance($ref_uid)->request($uid);
					Better_User_Friends::getInstance($uid)->request($ref_uid);
				}

				//$_SESSION['authpass'] = $post['password'];
				Better_User::getInstance($uid)->cache()->set('thirdpass',$post['password']);
				//Better_User::getInstance($uid)->cache{}->set('thirdpass',$post['password']);
				//Better_Cache_Handler_Base::getInstance()->set('thirdpass',$post['password']);
				Better_Registry::get('sess')->set('thirdpass', $post['password']);
				Better_User_AutoLogin::getInstance($uid)->putCookie();
				Better_Log::getInstance()->logInfo($uid,'step2sync');
				
				if ($post['partner'] == 'bedo') {
					if ($changePassword || $changeNickname) {
						$bedosign_notice = str_replace('xxxxxx',$changeNickname . '/' . $changePassword,Better_Language::load()->signup->onkey_bedobind_notice);						
						Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
						'content' => $bedosign_notice,
						'receiver' => $uid
						));
					}
					
					$_SESSION['showbedofriends'] = 1;
					$this->_helper->getHelper('Redirector')->gotoUrl('/setting/bedoimport');
					exit(0);
				}
				if($signupbythrid){		
					Better_User::getInstance($uid)->updateUser(array(
					'state' => Better_User_State::ENABLED
					));
					$avatarurl = Better_Registry::get('sess')->get('avatarurl');
					if(strlen($avatarurl)>0){						  		
				   		Better_User::getInstance($uid)->avatar()->upload($avatarurl, 'jpg', $uid);
					}
					try{
					$this->_helper->getHelper('Redirector')->gotoUrl('/home');
						exit(0);
					} catch (Exception $e) {
						Better_Log::getInstance()->logIno("just a test"."\n",'third_login');
						Zend_Debug::dump("hello world");			   
					}			
				}
				Better_Registry::get('sess')->stick();		
				if(isset($post['promotion']) && strlen($post['promotion'])>0){
					Better_Registry::get('sess')->set('promotionurl',"/promotion/".$post['promotion']);
				}
				//setcookie('thridpass',$post['password']);			
			}
		}
		
		$this->view->headScript()->prependScript('
		var BETTER_REGISTED_EMAIL = "'.$post['email'].'";
		');		
	}
	
	/**
	 * 检查用户注册时提交的数据
	 *
	 * @return null
	 */
	public function checkAction()
	{
		$this->_check();
	}
	
	public function step4Action()
	{
		exit(0);
		if ($_SERVER['REQUEST_METHOD']=='POST') {
			$tusers = &$_POST['tusers'];
			if (is_array($tusers) && count($tusers)>0) {
				foreach ($tusers as $uid) {
					$this->user->follow()->request($uid);
				}
				
				$data = Better_DAO_User_Status::getInstance($this->uid)->tinyWebFollowings(array(
					'page' => 1,
					'page_size' => 30,
					'with_self' => true,
					'without_kai' => true
					));
				
				foreach ($data as $row) {
					$bid = $row['bid'];
					$dateline = $row['dateline'];
					Better_DAO_User_Publictimeline::getInstance($this->uid)->replace(array(
						'uid' => $this->uid,
						'bid' => $bid,
						'dateline' => $dateline
						));	
				}				
			}
			
			$this->_helper->getHelper('Redirector')->gotoSimple('','home');
		}
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/signup/step4.js?ver='.BETTER_VER_CODE);

		$userInfo = Better_Registry::get('user')->getUser();
		
		$data = Better_User_Follow::getInstance($userInfo['uid'])->getFollowingsWithDetail(1, 18);
		$this->view->following = $data['rows'];
				
		$lon = $userInfo['lon'];
		$lat = $userInfo['lat'];
		
		if (!Better_LL::isValidLL($lon, $lat)) {
			$tmp = Better_Service_Ip2ll::parse(Better_Functions::getIP());
			$lon = $tmp['lon'];
			$lat = $tmp['lat'];
		}
		
		$results = Better_User_Signup::recUsers(array(
			'lon' => $lon,
			'lat' => $lat,
			'range' => 5000,
			'page' => 1,
			'count' => 10,
			'public' => true,
			'uid' => $this->uid,
			'has_avatar' => true,
			));

		$this->view->aroundUsers = $results['rows'];		
	}	
	
	public function step3Action()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/signup/step3.js?ver='.BETTER_VER_CODE);
		$user = $this->user->getUser();		 			
		$uid = $user['uid'];
		Better_User::getInstance($uid)->cache()->set('thirdpass','');
		if(strlen($_COOKIE['autoregsinasucess'])>0){
			$this->view->autoregsinasucess = str_replace('{EMAIL}',$_COOKIE['autoregsinasucess'],$this->lang->signup->step3->regsinasucess);					
		}			
		$this->view->syncSites = Better_User_Syncsites::getInstance($this->uid)->getSites();		
	}
	
	/**
	 * 新注册流程第二步（完善个人资料)
	 * 
	 * @return
	 */
	public function step2Action()
	{
		$this->needLogin();		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/signup/step2.js?ver='.BETTER_VER_CODE);
		$this->view->err = array();		
		$user = $this->user->getUserInfo();		 			
		$uid = $user['uid'];
		/*
		if(Better_Registry::get('sess')->get('qqdobind') || Better_Registry::get('sess')->get('163dobind')){
			$protocol_site = Better_Registry::get('sess')->get('protocol_site');
			$protocol_username = Better_Registry::get('sess')->get('protocol_username');
			$protocol_callback = Better_Registry::get('sess')->get('protocol_callback');
			$protocol_oauthtoken = Better_Registry::get('sess')->get('protocol_oauthtoken');
			$protocol_oauthtokensecret = Better_Registry::get('sess')->get('protocol_oauthtokensecret');
			$protocol_tid = Better_Registry::get('sess')->get('protocol_tid');
			$this->user->syncsites()->add($protocol_site, $protocol_username, $protocol_callback, $protocol_oauthtoken, $protocol_oauthtokensecret,$protocol_tid);	
			Better_Registry::get('sess')->set('qqdobind','') || Better_Registry::get('sess')->set('163dobind','');		
		} else if(Better_Registry::get('sess')->get('snstothird')){			
			$protocol_site = Better_Registry::get('sess')->get('protocol_site');
			$protocol_username = Better_Registry::get('sess')->get('protocol_email');
			$protocol_password = Better_Registry::get('sess')->get('protocol_password');
			$protocol_tid = Better_Registry::get('sess')->get('protocol_tid');			
			$this->user->syncsites()->add($protocol_site, $protocol_username, $protocol_password,'','',$protocol_tid);	
			Better_Registry::get('sess')->set('snstothird','');		
		}
		*/
		if (is_array($_POST) && count($_POST)>0) {
			$mail_partner = (int)Better_Registry::get('sess')->get('mail_partner');
            if($mail_partner){
                 Better_Log::getInstance()->logInfo($uid.'|'.$mail_partner,'mail_partner_signup', true);
            }		
			$nickname = $this->getRequest()->getParam('nickname','');
			$avatar = $this->getRequest()->getParam('avatar', '');
			$birthday = $this->getRequest()->getParam('birthday', '');
			$selfIntro = $this->getRequest()->getParam('self_intro', '');
			$flag = Better_User_Validator::intro($selfIntro);
			$autosina = $this->getRequest()->getParam('autosina','');	
			$gender = $this->getRequest()->getParam('gender', '');
			$live_province = $this->getRequest()->getParam('live_province','');	
			$live_city = $this->getRequest()->getParam('live_city', '');	
			$nicknameerr = Better_User_Validator::nickname($nickname);				
			if (!$flag) {
				$this->view->err['self_intro'] = $this->lang->signup->selfintro->ban_words;
	 		} else if(Better_Registry::get('sess')->get('step2nickname') && $nicknameerr['code']!=1){
			 	switch ($nicknameerr['code']){			 		
			 		case -5:
			 			$this->view->err['nickname'] = $this->lang->signup->nickname->too_long;
			 			break;
			 		case -6:
			 			$this->view->err['nickname'] = $this->lang->signup->nickname->forbidden_at;
			 			break;
			 		case -7:
			 			$this->view->err['nickname'] = $this->lang->signup->nickname->already_taken;
			 			break;
			 		case -11:
			 			$this->view->err['nickname'] = $this->lang->signup->nickname->too_short;
			 			break;
			 		case -13:
			 			$this->view->err['nickname'] = $this->lang->signup->nickname->empty;
			 			break;
			 	}				 	
	 		} else {
				$this->user->updateUser(array(
					'avatar' => $avatar,
					'birthday' => $birthday,
					'self_intro' => $selfIntro,
					'nickname' => $nickname,
					'gender' => $gender,
					'live_province' => $live_province,
					'live_city' => $live_city,
				));
				
				$checksignthrid['checkreg'] = true;
				$checksignthrid['errorinfo'] = 1;
				if(Better_Config::getAppConfig()->autoregthird->switch){											
					if($autosina==1){
						$checksignthrid['checkreg']= false;						
						$userInfo = $this->user->getUserInfo();
						$params = array(
							'regto' => 'sina.com',
							'nick' => Better_Config::getAppConfig()->autoregthird->interfere.$userInfo['username'],
							'gender' => $gender,
							'password' => Better_User::getInstance($uid)->cache()->get('thirdpass'),
							'email' => Better_Config::getAppConfig()->autoregthird->interfere.$userInfo['username']."@k.ai",
							'ip' => Better_Functions::getIP(),
						);	
										
						$thirdparty = Better_User_Signup::autothirdreg($params);
						$checksignthrid =$thirdparty;
					}
	 			}	
	 		
	 			if($checksignthrid['checkreg']){
	 				if(strlen($checksignthrid['errorinfo'])==0){
		 				$user = $this->user->getUserInfo();		 			
		 				$uid = $user['uid'];
		 				$synclist = Better_User_Syncsites::getInstance($uid)->getSites();
		 				$loginfrom = $params['regto'];
		 				$username= $params['email'];
		 				$password= $params['password'];
		 				
						if(isset($synclist[$loginfrom])){
							if($synclist[$loginfrom]['username']==$username){						
								Better_User_Syncsites::getInstance($uid)->delete($loginfrom);
								Better_User_Syncsites::getInstance($uid)->add($loginfrom, $username, $password);
							}
						} else {
							Better_User_Syncsites::getInstance($uid)->add($loginfrom, $username, $password);
						}
						Better_Registry::get('sess')->set('autoregsinasucess', $params['email']);	
						setcookie("autoregsinasucess", $params['email'], time()+3600);				
	 				}
					$this->_helper->getHelper('Redirector')->gotoSimple('step3','signup');
	 			} else {
	 				
	 				$this->view->err['signtosina'] = $checksignthrid['errorinfo'];
	 			}
			}
			
		}
	}
	
	/**
	 * 检查用户提交的注册数据
	 *
	 * @param $output 是否直接输出结果（json）
	 * @return unknown_type
	 */
	private function _check($output=true)
	{
		$post = $this->getRequest()->getPost();
		
		$patInvalid = '/([\s\r\t ])/is';
		$post['nickname'] = preg_replace($patInvalid, '', $post['nickname']);
		if($post['signupbythird']==1){
			$post['code'] = '8888';	
			Better_Registry::get('sess')->set('authCode','8888');
			Better_Log::getInstance()->logInfo("abc\n",'third_login');	
		}
		Better_Log::getInstance()->logInfo(serialize($post)."--".Better_Registry::get('sess')->get('authCode'),'third_login');
		$err = Better_User_Signup::check($post);
		
		if ($output==true) {
			echo json_encode(array(
					'has_error' => count($err),
					'err' => $err,
					));
			exit(0);
		}
		
		return $err;
	}
	public function partnersignupAction()
	{
			$this->view->midhadlogin = 1; 			
	}
	
	
	public function goAction()
	{
		$err = $this->_check(false);		
		$loged = false;
		$post = &$this->view->post;
		$this->view->post = $post;
		
		if(isset($_SESSION['partner_reg']) && $_SESSION['partner_reg']==1){
			$this->_helper->getHelper('Redirector')->gotoUrl('/home');
		} else {
			$_SESSION['partner_reg']=1;
		}
		
		$invitecode = $post['invitecode'];		
		if(Better_Registry::get('sess')->get('todoreg')){
			$this->_helper->getHelper('Redirector')->gotoUrl('/home');
		}
		$comefrom = Better_Registry::get('sess')->get('protocol_site');
		Better_Registry::get('sess')->set('todoreg',1);
		if($comefrom=='qq.com'){
			$loged && Better_Log::getInstance()->logInfo(Better_Registry::get('sess')->get('protocol_site')."--".time(),'qqreg');
		}
		$protocol_site = Better_Registry::get('sess')->get('protocol_site');
		Better_Registry::get('sess')->set('protocol_site','');
		$comesite = str_replace(".com","",$comefrom);	
		if(strlen($comesite)==0){
			$this->_helper->getHelper('Redirector')->gotoUrl('/signup');
		}		
			
		$used_id = 0;
		$tempuid = Better_Functions::randnumletter();
		//用第三方tid跟账户的Email关联
		$use_id_domain= array('sina.com','qq.com','163.com','douban.com','renren.com', 'qqsns.com');
		if(in_array($protocol_site,$use_id_domain)){
			
			if ($protocol_site == 'qqsns.com') {
				$doublesite = "t.qzone.qq.com";
			} else {
				$doublesite = "t.".$protocol_site;
			}
			
			$used_id = 1;
			$tempuid = Better_Registry::get('sess')->get('protocol_tid');
			$k=0;
			for($i=1;$i<2;$i++){
				$newemail = $tempuid."@".$doublesite;
				$checkemail = Better_User_Validator::email($newemail, 10000);
				if($k==2){
					$used_id =0;
					break;
				}	
				$loged && Better_Log::getInstance()->logInfo(serialize($checkemail)."-<>-".$newemail,'emailcheck');	
				if($checkemail['code']!=1){
					$tempuid = $tempuid.rand(100,999);
					$k++;
					$i=0;
					continue;				
				}				
			}
			$newemail = $tempuid."@".$doublesite;
		}
		$loged && Better_Log::getinstance()->logInfo($newemail."--".$tempuid."--".$used_id,'newid');	
		if($used_id==0){
			$k = 0;
			for($i=1;$i<2;$i++){			
				$newemail = $comesite."_".$tempuid."@k.ai";	
				if($k==2){
					$tempuid = $tempuid.rand(100,999);
					break;
				}			
				$checkemail = Better_User_Validator::email($newemail, 10000);
				$loged && Better_Log::getInstance()->logInfo(serialize($checkemail)."-<>-".$newemail,'emailcheck');	
				if($checkemail['code']!=1){
					$tempuid = Better_Functions::randnumletter();
					$k++;
					$i=0;
					continue;				
				}			
			}
			$newemail = $comesite."_".$tempuid."@k.ai";
		}
		$loged && Better_Log::getinstance()->logInfo($newemail,'newid');			
		$data['email'] = $newemail;
		$temnickname = Better_Registry::get('sess')->get('protocol_nickname');		
		$nickname = strlen($temnickname)>0 ? $temnickname:"kai".$tempuid;		
		$k = 0;
		if(strlen($nickname)>18){
			$len = 18 - strlen($nickname);			
			$nickname = mb_substr($nickname, 0, $len);					
		}
		for($i=1;$i<2;$i++){
			if($k==2){
				$nickname = '';
				break;
			}
			$checkid = Better_User_Signup::getInstance(10000)->validNickname($nickname, 0);
			$loged && Better_Log::getInstance()->logInfo(serialize($checkid)."-<>-".$nickname,'nicknamecheck');	
			if($checkid['code']!=1){
				$nickname = $nickname.rand(10,99);
				$k++;
				$i=0;
				continue;				
			}
		}
			
		//Better_Log::getInstance()->logInfo(serialize($checkid)."--".$nickname."--".$temnickname,'thirdnickname');
		$data['nickname'] = $nickname;
		$data['partner'] = 'web-partnet';
		$data['password'] = rand(100001,999999);	
		$thirdloginreg=1;
		$loged && Better_Log::getinstance()->logInfo(serialize($data),'newid');
		$bindlist = Better_DAO_ThirdBinding::getBindUser($protocol_site, Better_Registry::get('sess')->get('protocol_tid'));
		$loged && Better_Log::getinstance()->logInfo(serialize($bindlist),'newid');
		if($bindlist){	
			$uid = $bindlist['uid'];							
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
			$uid = Better_User_Signup::Signup($data);
		}	
		
		Better_Registry::get('sess')->set('protocol_site','');
		$signupbythrid = 0;
		if(Better_Registry::get('sess')->get('qqdobind') || Better_Registry::get('sess')->get('163dobind') || Better_Registry::get('sess')->get('renrendobind')  || Better_Registry::get('sess')->get('doubandobind') || Better_Registry::get('sess')->get('qqsnsdobind')){
				$loged && Better_Log::getInstance()->logInfo($uid,'step2sync');
				
				$protocol_username = Better_Registry::get('sess')->get('protocol_username');
				$protocol_callback = Better_Registry::get('sess')->get('protocol_callback');
				$protocol_oauthtoken = Better_Registry::get('sess')->get('protocol_oauthtoken');
				$protocol_oauthtokensecret = Better_Registry::get('sess')->get('protocol_oauthtokensecret');
				$protocol_tid = Better_Registry::get('sess')->get('protocol_tid');
				$expires_in = 0;
				if(Better_Registry::get('sess')->get('expires_in')){
					$expires_in = Better_Registry::get('sess')->get('expires_in');
				}	
				$protocol_password = $protocol_callback;
				if ( 'qqsns.com' == $protocol_site ) {
					$protocol_username = 'Qzone';
					$protocol_callback = $protocol_tid;
				} 
				
				Better_User_Syncsites::getInstance($uid)->add($protocol_site, $protocol_username, $protocol_callback, $protocol_oauthtoken, $protocol_oauthtokensecret, $protocol_tid, $expires_in);	
				
				Better_Registry::get('sess')->set('qqdobind','');
				Better_Registry::get('sess')->set('163dobind','');	
				Better_Registry::get('sess')->set('renrendobind','');	
				Better_Registry::get('sess')->set('doubandobind','');		
				Better_Registry::get('sess')->set('qqsnsdobind','');		
				$signupbythrid = 1;		
			} else if(Better_Registry::get('sess')->get('snstothird')){		
				$loged && Better_Log::getInstance()->logInfo($uid,'step2sync');	
				
				$protocol_oauthtoken = '';
				$protocol_oauthtokensecret= '';
				$expires_in = 0;
				if($protocol_site=='sina.com'){
					$protocol_oauthtoken = Better_Registry::get('sess')->get('protocol_oauthtoken');
					$protocol_oauthtokensecret = Better_Registry::get('sess')->get('protocol_oauthtokensecret');
				}
				$protocol_username = Better_Registry::get('sess')->get('protocol_email');
				$protocol_password = Better_Registry::get('sess')->get('protocol_password');
				$protocol_tid = Better_Registry::get('sess')->get('protocol_tid');			
				Better_User_Syncsites::getInstance($uid)->add($protocol_site, $protocol_username, $protocol_password,$protocol_oauthtoken,$protocol_oauthtokensecret,$protocol_tid,$expires_in);	
				Better_Registry::get('sess')->set('snstothird','');		
				$signupbythrid = 1;						
			}
		
		Better_User_AutoLogin::getInstance($uid)->putCookie();
		
		if($signupbythrid){	
			try{
				$service = Better_Service_PushToOtherSites::factory($protocol_site, $protocol_username, $protocol_password, $protocol_oauthtoken, $protocol_oauthtokensecret);						
				//人人
				if ($protocol_site == 'renren.com') {
					$logined = $service->fakeLoginApi();
				} else {
					$logined = $service->fakeLogin();
				} 
				$third_info = $service->getInfo();
				$user = Better_User::getInstance($uid);			
	            $joineduser = $user->friends()->autoAddFriend($uid, $protocol_site, $service, $third_info);
				if($joineduser){
					$maxnum =1 ;
					$msg = Better_Language::load()->global->third_user->autoreg->msg;
					//你的{THIRDPARTNER}好友 {USERLIST}也加入开开了哦，看看他们在哪儿签到吧。
					$sitedomainlist = Better_Service_PushToOtherSites::$openingProtocols;
					$sitenamelist = Better_Service_PushToOtherSites::$shortProtocols;
					$sitekey = array_search($protocol_site,$sitedomainlist);				
					$msg = str_replace("{THIRDPARTNER}",$sitenamelist[$sitekey],$msg);
					$friends_str = "";				
					foreach($joineduser as $row){
						if($maxnum>10){
							break;
						}
						$fuid = $row;
						$tempuser = Better_User::getInstance($fuid)->getUserInfo();		
						$friends_str.="@".$tempuser['nickname']." ";
						$maxnum++;
					}
					$joinstr = Better_Blog::parseBlogAt($friends_str);
					$msg = str_replace("{USERLIST}",$joinstr,$msg);
					Better_Registry::get('sess')->set('partner_sign_str',$msg);
				}
			} catch(Exception $errorjoin){
				Better_Log::getInstance()->logInfo(serialize($data),'autoaddf');
			}
			//$newemail = "kai".$uid."@k.ai";
			$newemail = $data['email'];
			/*
			Better_User::getInstance($uid)->updateUser(array(
				'state' => Better_User_State::ENABLED
			));
			
			Better_User::getInstance($uid)->updateUser(array(				
				'email' => $newemail
			));
			
			Better_Log::getInstance()->logInfo("更新状态结束:".time()."\n",'thirdregtime');
			$content = "Username:".$newemail."\nPassword:".$data['password'];
			Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
				'content' => $content,
				'receiver' => $uid
			));
			*/
			$content = $this->lang->global->thirdreg_message;
			$content = str_replace("{EMAIL}",$newemail,$content);
			$content = str_replace("{PASSWORD}",$data['password'],$content);
			Better_User_Notification_DirectMessage::getInstance(BETTER_SYS_UID)->send(array(
				'content' => $content,
				'receiver' => $uid
			));
			$avatarurl = Better_Registry::get('sess')->get('avatarurl');
			if(strlen($avatarurl)>0){						  		
		   		Better_User::getInstance($uid)->avatar()->upload($avatarurl, 'jpg', $uid);
		   	//	Better_Log::getInstance()->logInfo("更新头像结束:".time()."\n",'thirdregtime');
			}		
			try{
			$this->_helper->getHelper('Redirector')->gotoUrl('/home');
				exit(0);
			} catch (Exception $e) {
				Better_Log::getInstance()->logIno("just a test"."\n",'third_login');
				//Zend_Debug::dump("hello world");			   
			}			
		}				
		Better_Registry::get('sess')->stick();	
	}
	
	public function bedosignupAction()
	{
		if ($_SESSION['bedojid'] == '' || $_SESSION['bedopass'] == '') {
			
		} else {
			$userInfo = Better_Service_BedoBinding::getInstance()->userinfo($_SESSION['bedojid'], $_SESSION['bedopass']);
		}
		if ($userInfo) {
			$this->view->inputemail = $userInfo['jid'] . '@bedo.cn';
			$this->view->nickname = $userInfo['username'];
			$this->view->password = $_SESSION['bedopass'];
			$this->view->repassword = $_SESSION['bedopass'];
			$this->view->jid = $userInfo['jid'];
			$this->view->bedopass = $_SESSION['bedopass'];
			$this->view->bedoHead = $userInfo['icon'] ? Better_Config::getAppConfig()->bedo->avatar_url . 'small/' .$userInfo['icon'] : Better_Config::getAppConfig()->bedo->avatar_url . 'imicon/small/0.png';
		} else {
			//TODO show error
		}
		if ($_SESSION['bedobindretry']) {
			$this->view->headScript()->appendFile($this->jsUrl.'/controllers/login.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
			unset($_SESSION['bedobindretry']);
			$this->view->headScript()->prependScript("
    	var Better_LoginMsg = '".addslashes($this->lang->setting->import->err->binded)."';
    	");
		}
		
		if ($_SESSION['bedobinderrorpass']) {
			$this->view->headScript()->appendFile($this->jsUrl.'/controllers/login.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
			unset($_SESSION['bedobinderrorpass']);
			$this->view->headScript()->prependScript("
    	var Better_LoginMsg = '".addslashes($this->lang->setting->import->err->errorpassword)."';
    	");
		}
	}
	
	
	
}

?>