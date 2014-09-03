<?php

/**
 * api控制器基类
 * 
 * @package Better.Controller
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Controller_Api extends Better_Controller
{
	protected $format = 'json';
	protected $id = '';
	protected $todo = '';
	protected $xmlRoot = '';
	protected $suppress_response_codes = false;
	protected $api = null;
	protected $post = array();
	protected $data = array();
	protected $error = array();
	protected $addLogMsg = '';
	protected $page = 1;
	protected $count = 20;
	protected $userInfo = array();
	protected $lang = null;
	protected $user = null;
	protected $langAll = null;
	protected $errorDetail = '';
	protected $langKey = 'zh-cn';
	protected $needLbsLog = false;
	protected $needSpecLog = false;
	
	public $outputed = false;
	
	public function __call($method, $params)
	{
		$this->error('error.request.not_found');
	}
	
	public function init()
	{
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		define('IN_API', true);
		
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 1);
		parent::init();
		
		$sess = Better_Session::factory();
		$sess->init();

		//将用户数据注册到View变量
		$this->uid = Better_Registry::get('sess')->get('uid');

		$this->format = $this->getRequest()->getParam('format', 'xml');
		$this->todo = $this->getRequest()->getParam('todo', 'default');
		$this->page = $this->getRequest()->getParam('page', 1);
		($this->page<=0) && $this->page = 1;
		$this->count = $this->getRequest()->getParam('count', BETTER_PAGE_SIZE);
		$this->count<1 && $this->count = BETTER_PAGE_SIZE;
		$this->count>50 && $this->count = 50;

		$this->suppress_response_codes = $this->getRequest()->getParam('suppress_response_codes', '')!='' ? true : false;
		$this->api = new Better_Api();

		//$this->post = $this->getRequest()->isPost() ? $this->getRequest()->getPost() : array();
		
		$this->post = array();
		if ($this->getRequest()->isPost()) {
			$__post = $this->getRequest()->getPost();
			$_decode = $this->getRequest()->getParam('decode2', 'true');
			if ($_decode != 'true') {
				$this->post = $__post;
			} else {
				if ( is_array($__post) ) {
					$this->post = @array_map("urldecode", $__post);
				}
			}
		} 
		
		$this->id = isset($this->post['id']) ? $this->post['id'] : ($this->getRequest()->getQuery('id', '') ? $this->getRequest()->getQuery('id', '') : $this->getRequest()->getParam('id', ''));
		
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		$this->checkInput();
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);

		$this->api->setParam('format', $this->format);
		
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		$this->validIpRequest();
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		
		$language = $this->getRequest()->getParam('language', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		list($language, $foobar) = explode(',', $language);
		
		if (preg_match('/^en(.*)/i', $language)) {
			$language = 'en';
		} else if ($language=='SC') {
			$language = 'zh-cn';
		}

		$lang = Better_Language::load(in_array($language, Better_Language::$supportedLanguage) ? $language : 'zh-cn');
		$this->langAll = &$lang;
		$this->lang = $lang->api;
		$this->langKey = $language;
		
		Better_Registry::set('api_lang', $language);
	}	

	protected function checkInput()
	{
		!in_array($this->format, $this->api->formats) && $this->format = 'xml';
	}
	
	/**
	 * 限制api请求的方式为POST
	 * @return 
	 */
	protected function needPost()
	{
		if (!$this->getRequest()->isPost()) {
			$this->error('error.request.post_only');
		}
	}
	
	/**
	 * 
	 * 简化的登录验证
	 */
	protected function &simpleAuth($stopOnError=true)
	{
		$userInfo = array();

		while(false===$this->api->authValidate()) {
			@header('WWW-Authenticate: Basic realm="Kai API Auth"');

			if (preg_match('/firefox/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/opera/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/namoroka/i', $_SERVER['HTTP_USER_AGENT'])) {
				exit(0);
			} else if ($stopOnError==true) {
				if (Better_Registry::get('lang')->api->error->users->account_banned && $this->api->authMsg==Better_Registry::get('lang')->api->error->users->account_banned) {
					Better_Controller::sendSquidHeaderC(-1);
					$this->error('error.users.account_banned');
				} else if ($this->api->authMsg) {
					Better_Controller::sendSquidHeaderC(-2);
					$this->error('error.user.force_validating', 200);
				} else {
					Better_Controller::sendSquidHeaderC(-99);
					$this->error('error.user.auth_failed', 200);
				}
			}			
		}

		$this->uid = Better_Registry::get('user')->getUid();
		$this->user = Better_User::getInstance($this->uid);
		$this->userInfo = &$this->user->getUserInfo();

		return $this->userInfo;		
	}
	
	/**
	 * 发送 HTTP Basic Auth 验证用户
	 * @param $stopOnError
	 * @return unknown_type
	 */
	protected function &auth($stopOnError=true)
	{
		$userInfo = array();
		//$this->error('error.users.403', 200);
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		while(false===$this->api->authValidate()) {
			@header('WWW-Authenticate: Basic realm="Kai API Auth"');

      // 除了对有限的几个 user-agent 返回 HTTP 401，从而允许用户交互地输入用户名和密码，
      // 其余都返回 HTTP 200。
			if (preg_match('/firefox/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/opera/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/namoroka/i', $_SERVER['HTTP_USER_AGENT'])) {
				exit(0);
			} else if ($stopOnError==true) {
				if (Better_Registry::get('lang')->api->error->users->account_banned && $this->api->authMsg==Better_Registry::get('lang')->api->error->users->account_banned) {
					Better_Controller::sendSquidHeaderC(-1);
					$this->error('error.users.account_banned');
				} else if ($this->api->authMsg) {
					Better_Controller::sendSquidHeaderC(-2);
					$this->error('error.user.force_validating', 200);
				} else {
					Better_Controller::sendSquidHeaderC(-99);
					$this->error('error.user.auth_failed', 200);
				}
			}			
		}
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);

		$this->uid = Better_Registry::get('user')->getUid();
		$this->user = Better_User::getInstance($this->uid);
		$this->userInfo = &$this->user->getUserInfo();

		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		if ($this->uid) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$this->validUserRequest($this->uid);
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->follow()->getFollowings();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->follow()->getFollowers();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->friends()->getFriends();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->block()->getBlocks();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->block()->getBlockedBy();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			$this->user->favorites()->getAllBids();
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);		
			
			$apiLang = $this->user->cache()->get('api_lang');
			
			$regLang = Better_Registry::get('api_lang');
			if ($apiLang!=$regLang && $regLang) {
				$this->user->cache()->set('api_lang', $regLang);
			}			
			
			$apiLastAuth = (int)$this->user->cache()->get('api_last_auth');
			if(!$apiLastAuth){
				$apiLastAuth =0;
			}
			if ($this->config->fiestaBadge &&  (time()-$apiLastAuth>3600)) {
				Better_Hook::factory(array(
					'Badge'
					))->invoke('UserLogin', array(
						'uid' => $this->uid,
						'partner' => '',
						'secret' => '',
						'loginbycellno' => false,
					));
				$this->user->cache()->set('api_last_auth', time());
			}
			
			
		}
				
		return $this->userInfo;
	}
	
	/**
	 * 检测某些操作需要用户Karma大于零
	 * 
	 * @return 
	 */
	protected function needSufficientKarma()
	{
		if ($this->userInfo['karma']<0) {
			$this->error('error.user.insufficient_karma');
		}
	}
	
	/**
	 * 输出错误
	 * 
	 * @param $msg
	 * @param $code
	 * @param $e
	 * @return 
	 */
	public function error($msg='', $code='200', $e=null)
	{
		$this->data = array();
		
		$this->xmlRoot = 'hash';

		$_nickname = Better_Registry::get('new_nickname');
		if (!$_nickname) {
		$this->data[$this->xmlRoot] = array(
			'error' => $msg,
			'request' => $_SERVER['REQUEST_URI'],
			'message' => $this->api->getTranslator('error')->translate(array(
				'msg' => $msg
				)),
			);
		} else {
			$this->data[$this->xmlRoot] = array(
				'error' => $msg,
				'request' => $_SERVER['REQUEST_URI'],
				'message' => $this->api->getTranslator('error')->translate(array(
					'msg' => $msg
					)),
				'nickname' => $_nickname,	
				);
		}
		$msg = ($e instanceof Exception) ? 'API_EXCEPTION:['.$e->getMessage().']' : 'API_ERROR:['.$msg.($this->addLogMsg!='' ? ' - '.$this->addLogMsg : '').']';
		$this->errorDetail!='' && $msg .= ', Detail:['.$this->errorDetail.']';
		
		if ($code==500) {
			Better_Log::getInstance()->logEmerg($msg, 'api');
		} else {
			Better_Log::getInstance()->logAlert($msg, 'api');
		}

		$this->_output();
	}
	
	/**
	 * 抛500的server error
	 * 
	 * @return
	 */
	protected function serverError()
	{
		$this->error('error.request.server_error', 500);
	}
	
	/**
	 * 限制同一ip的api请求
	 * 
	 * @return 
	 */
	protected function validIpRequest()
	{
		$ip = Better_Functions::getIP();
		if (!Better_Api::validIpRequest($ip)) {
			$this->error('error.request.ip_proceeded_limit');
		}
	}
	
	/**
	 * 限制某些api请求的来源ip
	 * 
	 * @return 
	 */
	protected function limitIpRequest()
	{
		if (!Better_Api::limitIpRequest(ip2long('10.10.0.0'), 255*255)) {
			$this->error('error.request.ip_not_permitted');
		}
	}
	
	/**
	 * 检查用户请求限制
	 * 
	 * @param $uid
	 * @return 
	 */
	protected function validUserRequest($uid)
	{
		!Better_Api::validUserRequest($uid) && $this->error('error.request.user_proceeded_limit');
	}
	
	/**
	 * 检查消息更新限制
	 * 
	 * @param $uid
	 * @return misc
	 */
	protected function validStatusUpdateRequest($uid, $return=false)
	{
		$flag = !Better_Api::validStatusUpdateRequest($uid);
		
		if ($flag) {
			if ($return===false) {
				$this->error('error.request.status_update_proceeded_limit');
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	
	/**
	 * 获取经纬度
	 * 
	 * @return array
	 */
	protected function mixLL()
	{
		$lon = $this->getRequest()->getParam('lon', '');
		$lat = $this->getRequest()->getParam('lat', '');
		$lbs = $this->getRequest()->getParam('lbs', '');
		$gotRange = $this->getRequest()->getParam('range', 5000);
		
		$locage = $this->getRequest()->getParam('locage', $this->getRequest()->getParam('locAge', ''));
		$accuracy = $this->getRequest()->getParam('accuracy', '');
		$ver = $this->getRequest()->getParam('lbs_ver', '0.1');
		if ($ver!='0.2') {
//			$ver = $this->user->cache()->get('lbs_last_ver');
//			$ver!='0.2' && $ver = '0.1';
		}
		
		if ($this->getRequest()->getParam('rawgps', '')!='') {
			$rawgps = (bool)($this->getRequest()->getParam('rawgps', 'false')=='true' ? true : false);
		} else if ($this->getRequest()->getParam('rowgps', '')!='') {
			$rawgps = (bool)($this->getRequest()->getParam('rowgps', 'false')=='true' ? true : false);
		}
		
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		$tmp = $this->user->ll()->parse(array(
			'lon' => $lon,
			'lat' => $lat,
			'range' => $gotRange,
			'lbs' => $lbs,
			'ver' => $ver,
			'rawgps' => $rawgps,
			'accuracy' => $accuracy,
			'locage' => $locage
			));
		$gotLon = $tmp['lon'];
		$gotLat = $tmp['lat'];
		$gotRange = $tmp['range'];
		$gotAccuracy = $this->getRequest()->getParam('accuracy', $gotRange);
		$gotAccuracy==-1 && $gotAccuracy = 1000;
		
		/**
		 * 1月13日 根据付老师要求注释掉此处精度运算
		 */
		//$gotAccuracy = $gotAccuracy/1000*0.0000005;
		Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		
		return array(
			$gotLon,
			$gotLat,
			$gotRange,
			$gotAccuracy
			);
	}
	
	/**
	 * 输出数据
	 * 
	 * @return 
	 */
	public function output()
	{
		$this->api->logRequest(array(
			'request' => $_SERVER['REQUEST_URI'],
			'dateline' => time(),
			'uid' => $this->uid,
			'ip' => Better_Functions::getIP(),
			));

		$this->_output();
	}	
	
	/**
	 * 取得某个操作过后的个人成就变化（勋章／karma／掌门）
	 * 
	 * @return string
	 */
	protected function parseAchievements($prefix='')
	{
		$ac = '';
		
		if ($this->uid) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			$as = $this->user->achievement()->parse($prefix);
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			
			if (count($as)) {
				$ac = implode(', ', $as);
			}
		}
		
		$checkinMsg = Better_Registry::get('checkin_msg');
		if (trim($checkinMsg)!='') {
			$return = trim($checkinMsg);
		} else {
			$return = trim($ac)!='' ? $this->langAll->javascript->sketch->noshowaction->delta->action.$ac : '';
		}
		
		return $return;
	}
	
	protected function _output()
	{
		$this->api->setParam('xml_root', $this->xmlRoot);
		$this->api->setParam('data', $this->data);
		$output = $this->api->output();

		if ($this->needLbsLog) {
			$this->saveLbsLog($output);
		}
		
		if ($this->needSpecLog) {
			$this->saveSpecLog($output);
		}
		
		$language = Better_Registry::get('api_lang');
		if ($this->uid && $language) {
			Better_User::getInstance($this->uid)->updateUser(array(
				'language' => $language
				));
		}
		
		header('HTTP/1.1 200 OK');
		header('Kai: '.BETTER_VER_CODE);

		switch ($this->format) {
			case 'json':
				header('Content-Type: application/json');
				break;
			case 'xml':
			default:
				header('Content-Type: text/xml; charset=utf-8');
				break;
		}			

		if ($this->acceptGz()) {
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
			header('Content-Encoding: gzip');
			$output = gzencode($output, 2, FORCE_GZIP);
			header('Content-Length: '.strlen($output));
			Better_Log::getInstance()->prepare(__FILE__.':'.__METHOD__.':'.__LINE__);
		}

		$this->outputed = true;
		$this->getResponse()->sendHeaders();
		$this->sendSquidHeader((int)$this->uid);
		$this->sendSpecHeader();
		
		if ($this->getRequest()->getParam('charset', '')=='gbk') {
			echo iconv('utf-8', 'gbk', $output);
		} else {
			echo $output;
		} 

		exit;		
	}
	
	/**
	 * 保存Lbs需要的Log
	 * 
	 * @return null
	 */
	protected function saveLbsLog($xml='')
	{
		//[时间][id][发往lbs的定位请求xml][lbs返回定位结果xml][开开决策的用户经纬度和类型(是lbs的结果还是用cache等)][开开返回给用户的结果xml?]
		$rLbs = Better_Registry::get('lbs_last');
		$c = '['.date('Y-m-d H:i:s').']'.'['.$this->uid.']'.'['.$rLbs['body'].']'.'['.$rLbs['response'].']'.'['.$rLbs['lon'].'|'.$rLbs['lat'].'|'.($rLbs['use_cache'] ? 'cache' : 'lbs').']'.'[]'."\n";
		$logFile = APPLICATION_PATH.'/../logs/new_mixll.log';
		
		error_log($c, 3, $logFile);
				
	}
	
	protected function saveSpecLog($xml='')
	{
		Better_Log::getInstance()->putData(array(
			'output' => $xml
			), 'user_poi_trace');
		Better_Log::getInstance()->writeData('user_poi_trace');
	}
	
	/**
	 * 限制Server组api的ip来源
	 * 
	 * @return null
	 */	
	protected function limitServerIpSource()
	{
		/*
		$ips = explode('|', $this->config->server_api->source);
		$ip = Better_Functions::getIP();
		if (!in_array($ip, $ips)) {
			$this->error('error.server.ip_not_permitted');
		}*/
	}	
}
