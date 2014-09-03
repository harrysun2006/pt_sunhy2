<?php

/**
 * 用户设置控制器
 *
 * @package Controllers
 * @author leip  <leip@peptalk.cn>
 */

class SettingController extends Better_Controller_Front 
{

	public function init()
	{
		parent::simpleInit();
		
		$this->needLogin();
		$this->commonMeta();

    	$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer',
    		'charset=' => 'utf-8'
   			));
    	$ac = $this->getRequest()->getParam('action');
    	$ver = $this->getRequest()->getParam('ver', '1');
    	
    	$bedoInfo = Better_Service_BedoBinding::getInstance()->getBindInfo($this->uid);
    	$this->view->bedoUid = $bedoInfo['bedoUid'];
	}
	
	public function karmaAction()
	{
		
	}
	
	public function needenableAction()
	{
		
	}

	/**
	 * 设置首页
	 *
	 * @return unknown_type
	 */
	public function indexAction()
	{
		$this->view->needEmailJs = true;
		
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/basic.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer',
			'charset=' => 'utf-8'
   			));
		
   		$cacher = Better_Cache::remote();
		$cacheKey = md5('kai_setting_email_cnt_'.$this->uid);	
		$todayCnt = intval($cacher->get($cacheKey));
//		$this->view->isValidating = $this->user->needValidate() ? 1 : 0;
		$this->view->isValidating = $todayCnt > 2 ? 1 : 0;
		$this->view->headScript()->prependScript('
			var BETTER_USER_VALIDATING = '.($this->view->isValidating ? '1' : '0').';
			');		
		$this->view->lastRequestEmail = $this->view->isValidating ? Better_User_Bind_Email::getInstance($this->uid)->lastRequestEmail() : '';
	}
	
	public function avatarAction()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/avatar.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
	}
	
	public function passwordAction()
	{
		$h = $this->getRequest()->getParam('h', '');
		
		$this->view->headScript()->prependScript('
			var BETTER_RESETPWD_FROM_HASH = '.($h ? '1' : '0').';
			');
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/password.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer',
			'charset=' => 'utf-8'
   			));
		
		$this->view->fromHash = $h ? true : false;
	}
	
	public function privacyAction()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/privacy.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer',
			'charset=' => 'utf-8'
   			));
	}
	
	public function cellAction()
	{
		$this->view->needCellJs = true;
		
                $this->view->robot = $this->config->cell->robot;
		$this->view->headScript()->prependScript('
		cell_robot = "'.Better_Config::getAppConfig()->cell->robot.'";
		');
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/cell.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer',
			'charset=' => 'utf-8'
   			));
	}
	
	public function imAction()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/im.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer'
   			));
	}
	
	public function rssAction()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/rss.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer',
			'charset=' => 'utf-8'
   			));
	}
	
	/**
	 * 同步设置使用说明
	 *
	 * @return unknown_type
	 */
	public function syncAction()
	{	
		$target = $this->getRequest()->getParam('target', '');
		
		if ($target == 'douban') {
			$WB_AKEY = Better_Config::getAppConfig()->oauth->key->douban_akey;
			$WB_SKEY = Better_Config::getAppConfig()->oauth->key->douban_skey;
		} elseif ($target == '163')	{
			$WB_AKEY = Better_Config::getAppConfig()->oauth->key->t163_akey;
			$WB_SKEY = Better_Config::getAppConfig()->oauth->key->t163_skey;
		} elseif ($target == 'qq')	{
			$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qq_akey;
			$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qq_skey;
		} elseif ($target == 'qqsns')	{
			$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qqsns_akey;
			$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qqsns_skey;
		} elseif ($target == 'renren')	{
			$WB_AKEY = Better_Config::getAppConfig()->oauth->key->renren_akey;
			$WB_SKEY = Better_Config::getAppConfig()->oauth->key->renren_skey;
		} elseif($target == '4sq') {
			$WB_AKEY = Better_Config::getAppConfig()->oauth->key->foursq_akey;
			$WB_SKEY = Better_Config::getAppConfig()->oauth->key->foursq_skey;	
		} else{
			$WB_AKEY = Better_Config::getAppConfig()->oauth->key->twitter_akey;
			$WB_SKEY = Better_Config::getAppConfig()->oauth->key->twitter_skey;	
		} 			
		if ($target == 'douban') {
						
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$o->protocol = $target;
			
			$callback = Better_Config::getAppConfig()->base_url . '/setting/sync';
			if (APPLICATION_ENV!='production') {
				//$o->proxy = 'http://10.10.1.254:808';
			}
			
			$keys = $o->getRequestToken();
				
			$_SESSION['oauth_keys'] = $keys;
			
			$callback .= "?callback=$target";
			$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
			
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);
		} elseif($target == '163') {
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$o->protocol = $target;
			
			$callback = Better_Config::getAppConfig()->base_url . '/setting/sync';
			if (APPLICATION_ENV!='production') {
				//$o->proxy = 'http://10.10.1.254:808';
			}
			$keys = $o->getRequestToken();
			$_SESSION['oauth_keys'] = $keys;
			$callback .= "?callback=$target";
			$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);
		} elseif($target == 'qq') {
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$o->protocol = $target;
			$callback = Better_Config::getAppConfig()->base_url . '/setting/sync';
			$callback .= "?callback=$target";
			//$callback = "http://k.ai/setting/sync?callback=$target";
			if (APPLICATION_ENV!='production') {
				//$o->proxy = 'http://10.10.1.254:808';
			}
			$keys = $o->getRequestToken($callback);
			$_SESSION['oauth_keys'] = $keys;
			$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);
		} elseif($target == 'qqsns') {
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$o->protocol = $target;
			$callback = Better_Config::getAppConfig()->base_url . '/setting/sync';
			$callback .= "?callback=$target";
$callback = "http://k.ai/setting/sync?callback=$target";
			if (APPLICATION_ENV!='production') {
				$o->proxy = 'http://10.10.1.254:1080';
				$o->proxy_type = CURLPROXY_SOCKS5;
			}
			$keys = $o->getRequestToken($callback);
			$_SESSION['oauth_keys'] = $keys;
			$aurl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $callback);
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);
		} elseif($target == 'renren') {
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$o->protocol = $target;
			$callback = Better_Config::getAppConfig()->base_url . '/setting/sync';
			$callback .= "?callback=$target";
			
			//$callback = "http://k.ai/setting/sync?callback=$target";
			if (APPLICATION_ENV!='production') {
				//$o->proxy = 'http://10.10.1.254:808';
			}
			$aurl = "https://graph.renren.com/oauth/authorize?client_id={$WB_AKEY}&response_type=code&scope=publish_checkin+publish_feed+photo_upload+status_update&redirect_uri={$callback}";
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);								
		} elseif($target == 'facebook') {
			$callback = Better_Config::getAppConfig()->base_url . '/setting/sync?callback=facebook';
			//$callback = 'http://k.ai/callback=facebook&fcode=';
			$app_id = Better_Config::getAppConfig()->oauth->key->facebook_appid;
			$scope = Better_Config::getAppConfig()->oauth->key->facebook_scope;
			
			$querys['redirect_uri'] = $callback;
			$querys['client_id'] = $app_id;
			$querys['scope'] = $scope;
			
			$redirect_url = "https://graph.facebook.com/oauth/authorize?" . http_build_query($querys);
			$this->_helper->getHelper('Redirector')->gotoUrl($redirect_url);
		} elseif($target == 'kaixin001') {	
			$redirect_url = 'http://www.kaixin001.com/login/connect.php?appkey=624121267795ca6700507845dacb15a6&re=/kx001_receiver.php&t=' . rand(1, 50);
			$this->_helper->getHelper('Redirector')->gotoUrl($redirect_url);
		} elseif($target == '4sq'){
			$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY  );
			$o->protocol = $target;
			$callback = Better_Config::getAppConfig()->base_url . '/setting/sync';
			$callback .= "?callback=".$target."&".$target;
			
			$aurl = "https://foursquare.com/oauth2/authenticate?client_id={$WB_AKEY}&response_type=code&redirect_uri={$callback}";	
			$_SESSION['4sq'] = 1;
			$this->_helper->getHelper('Redirector')->gotoUrl($aurl);
		} else {
			$callback = $this->getRequest()->getParam('callback', '');
			
			if ($callback == 'douban') {
				$WB_AKEY = Better_Config::getAppConfig()->oauth->key->douban_akey;
				$WB_SKEY = Better_Config::getAppConfig()->oauth->key->douban_skey;	
			} elseif ($callback == '163')	{
				$WB_AKEY = Better_Config::getAppConfig()->oauth->key->t163_akey;
				$WB_SKEY = Better_Config::getAppConfig()->oauth->key->t163_skey;
			} elseif ($callback == 'qq')	{
				$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qq_akey;
				$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qq_skey;
			} elseif ($callback == 'qqsns')	{
				$WB_AKEY = Better_Config::getAppConfig()->oauth->key->qqsns_akey;
				$WB_SKEY = Better_Config::getAppConfig()->oauth->key->qqsns_skey;
			} elseif ($callback == 'renren')	{
				$WB_AKEY = Better_Config::getAppConfig()->oauth->key->renren_akey;
				$WB_SKEY = Better_Config::getAppConfig()->oauth->key->renren_skey;
			} elseif ($callback == '4sq')	{
				$WB_AKEY = Better_Config::getAppConfig()->oauth->key->foursq_akey;
				$WB_SKEY = Better_Config::getAppConfig()->oauth->key->foursq_skey;
			} else {
				$WB_AKEY = Better_Config::getAppConfig()->oauth->key->twitter_akey;
				$WB_SKEY = Better_Config::getAppConfig()->oauth->key->twitter_skey;	
			}			
			
			if ($callback == 'douban') {
				$keys = $_SESSION['oauth_keys'];
				$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );
				$o->protocol = $callback;
				
				if (APPLICATION_ENV!='production') {
					//$o->proxy = 'http://10.10.1.254:808';
				}
				
				$last_key = $o->getAccessToken();
				$oauth_token = $last_key['oauth_token'];
				$oauth_token_secret = $last_key['oauth_token_secret'];
				$douban_user_id = $last_key['douban_user_id'];
				if ($oauth_token && $oauth_token_secret) {
					$this->user->syncsites()->add( $callback . '.com', ucfirst($callback), $callback, $oauth_token, $oauth_token_secret, $douban_user_id);
				}	
			} elseif ($callback == '163') {
				$keys = $_SESSION['oauth_keys'];
				$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );
				$o->protocol = $callback;
				
				if (APPLICATION_ENV!='production') {
					//$o->proxy = 'http://10.10.1.254:808';
				}
				$last_key = $o->getAccessToken();
				$oauth_token = $last_key['oauth_token'];
				$oauth_token_secret = $last_key['oauth_token_secret'];
				if ($oauth_token && $oauth_token_secret) {
					$service = Better_Service_PushToOtherSites::factory($callback . '.com', '', '', $oauth_token, $oauth_token_secret);
					$third_info = $service->getInfo();
					$t163_id = $third_info['id'];					
					$this->user->syncsites()->add( $callback . '.com', ucfirst($callback), $callback, $oauth_token, $oauth_token_secret, $t163_id);
				}				
			} elseif ($callback == 'qq') {
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
				$_name = $last_key['name'];
				if ($oauth_token && $oauth_token_secret) {
					$this->user->syncsites()->add( $callback . '.com', $_name, $callback, $oauth_token, $oauth_token_secret, $_name);
					$service = Better_Service_PushToOtherSites::factory('qq.com', $_name, $callback, $oauth_token, $oauth_token_secret);
					$service->friendsAdd();
				}				
			} elseif ($callback == 'qqsns') {
				$keys = $_SESSION['oauth_keys'];
				$o = new Better_Oauth_Weibo( $WB_AKEY , $WB_SKEY , $keys['oauth_token'] , $keys['oauth_token_secret']  );				
				$o->protocol = $callback;
				
				if (APPLICATION_ENV!='production') {
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
					$this->user->syncsites()->add( $callback . '.com', 'Qzone', $openid, $oauth_token, $oauth_token_secret, $openid);
				}
								
			} elseif ($callback == 'facebook') {
				$code = $this->getRequest()->getParam('code', '');
				if ($code) {
					$app_id = Better_Config::getAppConfig()->oauth->key->facebook_appid;
					//$app_secret = Better_Config::getAppConfig()->oauth->key->facebook_secret;
					$app_secret = '24ced1a94b3e33d4c5b514314ec8a8dc';
					$scope = Better_Config::getAppConfig()->oauth->key->facebook_scope;
					
					$callback = Better_Config::getAppConfig()->base_url . '/setting/sync?callback=facebook';
					
					$querys['redirect_uri'] = $callback;
					$querys['client_id'] = $app_id;
					$querys['client_secret'] = $app_secret;
					$querys['scope'] = $scope;
					//$querys['code'] = $code;
					
					$redirect_url = "https://graph.facebook.com/oauth/access_token?" . http_build_query($querys) . '&code=' . $code;
					$tmpfname = tempnam("/tmp", "FOO");
					$hosts = explode('|', Better_Config::getAppConfig()->ssh->proxy->hosts);
					$proxy = $hosts[0];
					$ch = curl_init($redirect_url);	
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
					$text = curl_exec($ch);
					parse_str($text, $output);
					$token = $output['access_token'];
				}
								
				if ($token) {
					$callback = 'facebook';
					$this->user->syncsites()->add( $callback . '.com', ucfirst($callback), $callback, $token, 'facebook');				
				}
			
			
			} elseif ($callback == '139') {				
				$token = $this->getRequest()->getParam('token', '');
				$userid = $this->getRequest()->getParam('userid', '');
				$at = $this->getRequest()->getParam('at', 'bind');
	
				$app_key = Better_Config::getAppConfig()->shuoke_api->key;;
				$app_secret = Better_Config::getAppConfig()->shuoke_api->secret;
				
				$miop = new Better_Miop_Main($app_key, $app_secret, $token);			
			
				$app_account = $this->uid;

				if ($at == 'unbind') {
					$bindresult = $miop->api_client->connect_unRegister($app_account);
				} else {
					$bindresult = $miop->api_client->connect_register($userid, $app_account);
				}
				
				if ($bindresult && $bindresult['error'] == 0) {
					//$info = $miop->api_client->users_getInfo($userid, array());
					$username = $userid; //$info[0]['username'];
					
					if ($at == 'unbind') {
						Better_User_Syncsites::getInstance($this->uid)->delete($callback . '.com');
					} else {
						$this->user->syncsites()->add( $callback . '.com', $username, $userid, '', '', $userid);
					}				
				} else {
					$log_str = '139' . '||' . $at . '||' . serialize($bindresult);
					Better_Log::getInstance()->logAlert($log_str, 'bindsns_fail');
				}
			} elseif($callback == 'kaixin001') {
				$sessin_key = $this->getRequest()->getParam('id', '');
				//var_dump($sessin_key);exit;
				$this->user->syncsites()->add( $callback . '.com', ucfirst($callback), $callback, $sessin_key, $callback);
			} elseif($callback == 'renren') {
				$code = $this->getRequest()->getParam('code', '');
				$type = $this->getRequest()->getParam('type', '');
				
				if ($code) {
					$callback_url = Better_Config::getAppConfig()->base_url . '/setting/sync';
					$callback_url .= "?callback=$callback&type=token";
					//$callback_url = "http://k.ai/setting/sync?callback=$callback&type=token";
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
						$this->user->syncsites()->add( $callback . '.com', ucfirst($callback), $callback, $access_token, $refresh_token, $tid, $expires_in);
						$flag = Better_DAO_ThirdBinding::getInstance($this->uid)->setSyncBadge('renren.com', 0);
					}
					
				}
				
			} 
			if(isset($_SESSION['4sq']) && $_SESSION['4sq']==1){
				$code = $this->getRequest()->getParam('code', '');
				
				if ($code) {
					
					$callback = Better_Config::getAppConfig()->base_url . '/setting/sync';
					Better_Log::getInstance()->logInfo($code,'4sq');
					$access_token = Better_Service_PushToOtherSites_Sites_4sqcom::gottoken($callback,$code);
					Better_Log::getInstance()->logInfo($access_token,'4sq');
					/*
					$params = array("client_id"=>$WB_AKEY,
						"client_secret"=>$WB_SKEY,
						"grant_type"=>"authorization_code",
						"redirect_uri"=>$callback,
						"code"=>$code);
					
					$url = "https://foursquare.com/oauth2/access_token";
					if(!empty($params) && $params){
						foreach($params as $k=>$v) $kv[] = "$k=$v";
						$url_params = str_replace(" ","+",implode('&',$kv));
						$url = trim($url) . '?' . $url_params;
					}	
				
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,$url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
						curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
					}else {						
						curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.X.Y.Z Safari/525.13.');
					}
					curl_setopt($ch , CURLOPT_TIMEOUT, 30);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);			
					$result=curl_exec($ch);					
					$info=curl_getinfo($ch);
					$json = json_decode($result);
					$access_token = $json->access_token;
					curl_close($ch);	          
					*/
					if ($access_token) {
						$callback = '4sq';
						$expires_in = '';
						$refresh_token = '';
						$scope = '';
						$tid='';					
						$oauth_token = $access_token;
						$oauth_token_secret = $refresh_token;					
						$this->user->syncsites()->add( $callback.'.com', ucfirst($callback), $callback, $access_token, $refresh_token, $tid, $expires_in);
					}
					
				}
			}
			
			$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/sync.js?ver='.BETTER_VER_CODE);
			$this->view->syncSites = Better_User_Syncsites::getInstance($this->uid)->getSites();			
		} 
		

	}
	
	/**
	 * Email设置
	 */
	public function emailAction()
	{
		$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/email.js?ver='.BETTER_VER_CODE);
	}

	/**
	 * 更新用户资料
	 * 根据post的todo选项，进行具体的资料更新操作
	 *
	 * @return unknown_type
	 */
	public function updateAction()
	{
		$return = array(
			'has_err' => 1,
			'err' => array(),
			'data' => array(),
			'uid' => $this->uid,
			);
		$post = $this->getRequest()->getPost();
		if ($this->getRequest()->isPost()) {
			switch($post['todo']) {
				case 'basic' :	//	更新基本资料
					$return = $this->_updateBasicInfo();
					break;
				case 'avatar':	//	更新头像
					$return = $this->_updateAvatar();
					break;
				case 'del_avatar':	//	删除头像
					$return = $this->_delAvatar();
					break;
				case 'password':		//	修改密码
					$return = $this->_updatePwd();
					break;
				case 'privacy':			//	修改隐私策略
					$return = $this->_updatePrivacy();
					break;
				case 'email':          //更新邮件设置
					$return = $this->_updateEmail();
					break;
				case 'bedoimport':
					$return = $this->_updateBedoimport();
					break;
				case 'bedobind':
					$return = $this->_updateBedobind();
					break;
			}
		}
		
		echo json_encode($return);
		exit(0);
	}
	
	/*private function _updatePrivacy()
	{
		$post = $this->getRequest()->getPost();
		$now = time();
		$update_priv_blog_times = Better_User::getInstance($this->uid)->cache()->get('update_priv_blog_times');
		if($now-$update_priv_blog_times<Better_Config::getAppConfig()->update_priv_blog_time_limit){			
			return array('has_err'=>1, 'err'=>$this->lang->setting->privacy->priv_blog_time_limit);
		}				
		if($now-$update_priv_blog_times>=Better_Config::getAppConfig()->update_priv_blog_time_limit){
					$rows['priv_blog'] = $post['priv_blog'];
					Better_User::getInstance($this->uid)->cache()->set('update_priv_blog_times',$now);
				} else {
					$rows['priv_blog'] = $this->userInfo['priv_blog'];
					$checked = 0;
				}		
		$data = array(
			'priv_blog' => $post['priv_blog'],
			);
		Better_Registry::get('user')->updateUser($data);
		
		return array('has_err'=>0, 'err'=>'');
	}*/
	
	private function _updatePrivacy()
	{
		$post = $this->getRequest()->getPost();
		$now = time();
		$update_priv_blog_times = Better_User::getInstance($this->uid)->cache()->get('update_priv_blog_times');
		if($now-$update_priv_blog_times<Better_Config::getAppConfig()->update_priv_blog_time_limit){			
			return array('has_err'=>1, 'err'=>$this->lang->setting->privacy->priv_blog_time_limit);
		}			
		if($now-$update_priv_blog_times>=Better_Config::getAppConfig()->update_priv_blog_time_limit){
					$rows['allow_rt'] = $post['allow_rt'];
					$rows['friend_sent_msg'] = $post['friend_sent_msg'];
					Better_User::getInstance($this->uid)->cache()->set('update_priv_blog_times',$now);
		} else {
					$rows['allow_rt'] = $this->userInfo['allow_rt'];
					$rows['friend_sent_msg'] = $this->userInfo['friend_sent_msg'];
					$checked = 0;
		}
		$data = array(
			'allow_rt' => $post['allow_rt'],
			'friend_sent_msg'=> $post['friend_sent_msg'],
			'sync_badge'=> $post['sync_badge']
			);
		Better_Registry::get('user')->updateUser($data);
		
		return array('has_err'=>0, 'err'=>'');
	}
	
	
	private function _updatePwd()
	{
		$post = $this->getRequest()->getPost();
		$oldPass = $post['oldpass'];
		$pass = $post['pass'];
		$repass = $post['repass'];
		$fromHash = $post['from_hash'] ? true : false;
		$return = array('has_err'=>1, 'err'=>'');
		
		$user = $this->user->getUser();
		
		if($oldPass=='' && !$fromHash) {
			$return['err'] = $this->lang->setting->password->old_password_required;
		} else if(!$fromHash && md5($oldPass.$user['salt'])!=$user['password']){
			$return['err'] = $this->lang->setting->password->old_password_wrong;
		} else if(strlen($pass)<6){
			$return['err'] = $this->lang->setting->password->new->tips;
		} else {
			if ($pass!=$repass) {
				$return['err'] = $this->lang->setting->password->mismatch;
			} else {
				$d = array();
				$d['password'] = $pass;
				$this->user->updateUser($d);
				$return['has_err'] = 0;
			}
		}
		
		return $return;
	}
	
	private function _updateBedoimport()
	{
		$post = $this->getRequest()->getPost();
		
		$syncInfo = Better_DAO_Bedo::getInstance()->getSyncDataInfo($this->uid);
		
		Better_Service_BedoBinding::getInstance()->setBind($this->uid, $post['importSet']);
//		switch ($post['importSet']) {
//		}
		if ($syncInfo['status'] != 0) {
			return 'importing';
		}
		if ($syncInfo['blog'] + $syncInfo['photo'] + $syncInfo['miniBlog'] == 0) {
			return 'noneimport';
		}
		return '';
//		echo 'here';exit;
	}
	private function _delAvatar()
	{
		$d = array();
		$post = $this->getRequest()->getPost();
		$d['avatar'] = '';
		$return = array(
			'has_err' => 1,
			'err' => '',
			);
		
		if ($post['avatar']) {
			list($uid,$tmp) = explode('.', $post['avatar']);
			
			if ($uid==$this->uid) {
				Better_User::getInstance($this->uid)->avatar()->delete();
				$return['has_err'] = 0;
			} else {
				$return['err'] = $this->lang->upload->error->wrong_right;
			}

		} else {
			$return['err'] = $this->lang->upload->error->unknown;
		}

		return $return;
	}
	
	private function _updateAvatar()
	{
		$d = array();
		$post = $this->getRequest()->getPost();
		$avatar = $post['avatar'];

        $id = Better_User_Avatar::getInstance($this->uid)->upload();
        $return = array('has_err'=>1);
        
        if (is_array($id) && isset($id['url'])) {
        	$return['data']['url'] = $id['url'];
        	$return['data']['thumb'] = $id['url'];
        	$return['data']['tiny'] = $id['url'];
        	$return['data']['file_id'] = $id['file_id'];
        	$return['has_err'] = 0;
        } else {
        	$return['err'] = $id;
        }

       	return $return;
	}
	
	private function _updateBasicInfo()
	{
		$return = array(
			'has_err' => 1,
			'err' => array(),
			);
		$err = array();
		$post = $this->getRequest()->getPost();
		$post['uid'] = $this->uid;
		$post['passby_pass'] = true;
		$post['passby_cell'] = true;
		$post['email'] || $post['email'] = $this->userInfo['email'];
		
		$result = Better_User_Signup::quickCheck($post);
		
		$code = $result['code'];
		$codes = &$result['codes'];
		
		switch ($code) {
			case $codes['USERNAME_REQUIRED']:
				$err['err_username'] = $this->lang->setting->basic->username->empty;
				break;
			case $codes['NICKNAME_REQUIRED']:
				$err['err_nickname'] = $this->lang->signup->nickname->empty;
				break;
			case $codes['EMAIL_INVALID']:
				$err['err_email'] = $this->lang->error->email_invalid;
				break;
			case $codes['EMAIL_EXISTS']:
				$err['err_email'] = $this->lang->error->email_exists;
				break;
			case $codes['NICKNAME_TOO_SHORT']:
				$err['err_nickname'] = $this->lang->signup->nickname->too_short;
				break;
				
			case $codes['NICKNAME_TOO_LONG']:
				$err['err_nickname'] = $this->lang->signup->nickname->too_long;
				break;
			case $codes['NICKNAME_FORBIDEN_WORD']:
				$err['err_nickname'] = $this->lang->signup->nickname->forbidden_words;
				break;
			case $codes['NICKNAME_EXISTS']:
				$err['err_nickname'] = $this->lang->signup->nickname->already_taken;
				break;
				
			case $codes['USERNAME_TOO_LONG']:
				$err['err_username'] = $this->lang->signup->username->too_long;
				break;
			case $codes['USERNAME_FORBIDEN_WORD']:
				$err['err_username'] = $this->lang->signup->username->forbidden_specialChar;
				break;
			case $codes['USERNAME_EXISTS']:
				$err['err_username'] = $this->lang->setting->basic->username->already_taken;
				break;
			case $codes['USERNAME_TOO_SHORT']:
				$err['err_username'] = $this->lang->signup->username->too_short;
				break;
			case $codes['BAN_WORDS']:
				$err['err_intro'] = $this->lang->signup->selfintro->ban_words;
				break;
			case $codes['PASSWORD_INVALID']:
			case $codes['CELL_INVALID']:
			case $codes['CELL_EXISTS']:
			case $codes['FAILED']:
			case $codes['SUCCESS']:				
			default:
				break;
		}		
			
		if (count($err)>0) {
			$return['err'] = $err;
		} else {
			if (Better_Registry::get('user')->updateUser($post)) {
				$return['has_err'] = '0';
			}
		}
		
		return $return;
	}
	
	
	/**
	 * 更新邮件设置
	 */
	private function _updateEmail(){
		$post = $this->getRequest()->getPost();
		$data = array(
			'email4person' => $post['email_person'],
			'email4community' => $post['email_community'],
			'email4product' => $post['email_product'],
			);
		
		Better_Registry::get('user')->updateUser($data);
		
		return array('has_err'=>0, 'err'=>'');
	}
	
	private function _updateBedobind()
	{
		$return = '';
		$post = $this->getRequest()->getPost();
		$code = Better_Service_BedoBinding::getInstance()->bind($this->uid, $post['jid'], $post['password']);
		switch($code) {
			case 'binded':
				$return = $this->lang->setting->import->err->binded;
			break;
			case 'none':
				$return = $this->lang->setting->import->err->none;
			break;
			case 'bedobinded':
				$return = $this->lang->setting->import->err->bedobinded;
			break;
			case 'errpass':
				$return = $this->lang->setting->import->err->errpass;
			break;
			case 'ok':
				$_SESSION['showbedofriends'] = 1;
				//$return['err'] = $this->lang->setting->import->err->binded;
			break;
		}
		return $return;
	}
	
	public function bedoimportAction()
	{
			$syncInfo = Better_DAO_Bedo::getInstance()->getSyncDataInfo($this->uid);
			
			if (!$syncInfo) {
				$this->_helper->getHelper('Redirector')->gotoSimple('bedobind','setting');
        		exit(0);
			} else {
				$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/bedoimport.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
		   			'defer' => 'defer',
					'charset=' => 'utf-8'
	   			));
				$this->view->blogCnt = $syncInfo['blog'] ? $syncInfo['blog'] : 0;
				$this->view->photoCnt = $syncInfo['photo'] ? $syncInfo['photo'] : 0;
				$this->view->miniBlogCnt = $syncInfo['miniBlog'] ? $syncInfo['miniBlog'] : 0;
				$this->view->totalNum = $this->view->blogCnt + $this->view->photoCnt + $this->view->miniBlogCnt;
				
				$this->view->importChooseStyle = 'normal';
				$this->view->importingStyle = 'hide';
				$this->view->noneImportStyle = 'hide';
				
				if ($this->view->totalNum == 0) {
					$this->view->importChooseStyle = 'hide';
					$this->view->noneImportStyle = 'normal';
				} 
				
				if ($syncInfo['status'] != 0) {
					$this->view->importChooseStyle = 'hide';
					$this->view->importingStyle = 'normal';
				}
				if ($_SESSION['showbedofriends']) {
					unset($_SESSION['showbedofriends']);
					$friends = Better_DAO_Bedo::getInstance()->getKaiFriends($syncInfo['jid']);
					$maxCnt = min(7, count($friends));
					for($i = 0; $i < $maxCnt; $i++) {
						$tUid = $friends[$i]['uid'];
						$tUser = Better_User::getInstance($tUid);
						$tUserInfo = $tUser->getUser();
						$friendsInfo[] = array('link'=>Better_Config::getAppConfig()->base_url.'/' . $tUserInfo['username'],'img'=>$tUserInfo['avatar_small'],'txt'=>$tUserInfo['nickname']);
					}
					$this->view->friendlist = $friendsInfo;
					$this->view->firstImport = 1;
				}
			}
	}
	
	public function bedobindAction()
	{
		$syncInfo = Better_DAO_Bedo::getInstance()->getSyncDataInfo($this->uid);
		if ($syncInfo) {
			$this->_helper->getHelper('Redirector')->gotoSimple('bedoimport','setting');
        	exit(0);
		} else {
			$this->view->headScript()->appendFile($this->jsUrl.'/controllers/setting/bedobind.js?ver='.BETTER_VER_CODE, 'text/javascript', array(
   			'defer' => 'defer',
			'charset=' => 'utf-8'
   			));
		}
	}
}

?>