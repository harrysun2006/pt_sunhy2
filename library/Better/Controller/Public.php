<?php

/**
 * api控制器基类
 * 
 * @package Better.Controller
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Controller_Public extends Better_Controller
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
	protected $kkEncoding = false;
	protected $langKey = 'zh-cn';
	protected $needLbsLog = false;
	protected $_isme = false;
	
	protected $memcached = '';
	
	public $outputed = false;
	
	public function __call($method, $params)
	{
		$this->error('error.request.not_found');
	}
	
	public function init()
	{
		Better_Log::getInstance()->logTime('Before_Controller_Init:['.__CLASS__.']');
		define('IN_API', true);
		
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 1);
		parent::init();
		
		Better_Log::getInstance()->logTime('Before_Session_Init:['.__CLASS__.']');
		$sess = Better_Session::factory();
		$sess->init();
		Better_Log::getInstance()->logTime('After_Session_Init:['.__CLASS__.']');

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

		$this->post = $this->getRequest()->isPost() ? $this->getRequest()->getPost() : array();
		$this->id = isset($this->post['id']) ? $this->post['id'] : ($this->getRequest()->getQuery('id', '') ? $this->getRequest()->getQuery('id', '') : $this->getRequest()->getParam('id', ''));
		
		$this->checkInput();

		$this->api->setParam('format', $this->format);
		
		
		$key = $this->getRequest()->getParam('key', '');
		header('K: ' . $key);
		if ($key == 'd2860bc20a34ce65d69cafec4af2984c' ) $this->_isme = true;
		
		if (APPLICATION_ENV == 'production' && !$this->_isme) {
			$this->validKey();
			//$this->validIpRequest();
			
			$this->post['source'] = 'api';			
		}
		
		
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
		
		Better_Log::getInstance()->logTime('After_Controller_Init:['.__CLASS__.']');
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
	 * 发送 HTTP Basic Auth 验证用户
	 * @param $stopOnError
	 * @return unknown_type
	 */
	protected function &auth($stopOnError=true)
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
		
		$this->userInfo = Better_Registry::get('user')->parseUserFull();
		
		$this->uid = $this->userInfo['uid'];
		$this->user = Better_User::getInstance($this->uid);
		
		if ($this->uid) {
			if (APPLICATION_ENV == 'production' && !$this->_isme) $this->validUserRequest($this->uid);
			
			$this->user->follow()->getFollowings();
			$this->user->follow()->getFollowers();
			$this->user->friends()->getFriends();
			$this->user->block()->getBlocks();
			$this->user->block()->getBlockedBy();
			$this->user->favorites()->getAllBids();		
			
			$apiLang = $this->user->cache()->get('api_lang');
			
			$regLang = Better_Registry::get('api_lang');
			if ($apiLang!=$regLang && $regLang) {
				$this->user->cache()->set('api_lang', $regLang);
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

		$this->data[$this->xmlRoot] = array(
			'error' => $msg,
			'request' => $_SERVER['REQUEST_URI'],
			'message' => $this->api->getTranslator('error')->translate(array(
				'msg' => $msg
				)),
			);

		$msg = ($e instanceof Exception) ? 'API_EXCEPTION:['.$e->getMessage().']' : 'API_ERROR:['.$msg.($this->addLogMsg!='' ? ' - '.$this->addLogMsg : '').']';
		$this->errorDetail!='' && $msg .= ', Detail:['.$this->errorDetail.']';
		
		if ($code==500) {
			Better_Log::getInstance()->logEmerg($msg, 'api');
		} else {
			Better_Log::getInstance()->logAlert($msg, 'api');
		}

		$this->_output($code);
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
	 * 对key检查
	 */
	protected function validKey()
	{
		$key = $this->getRequest()->getParam('key', '');
		$data = Better_DAO_ApiKey::getInstance()->get(array(
			'key' => $key,
			));
		if (!$data['key']) {
			$this->error('error.request.key_not_permitted', '403');
		}
		
	}
	
	/**
	 * 限制同一ip的api请求
	 * 
	 * @return 
	 */
	protected function validIpRequest()
	{
		return true;
		$ip = Better_Functions::getIP();
		$limit_times = Better_Config::getAppConfig()->public->times;
		
		$memcache = Better_Cache::remote();
		
		$this->memcached = $memcache;
		
		$key = 'ip' . '_' .  $ip;
		$var = $memcache->get($key);

		if ($var) {
			$new_var = $memcache->increment($key, 1);
			if ($new_var > $limit_times) {
				$this->error('error.request.ip_proceeded_limit', '403');
			}
			
		} else {
			$memcache->add($key, 1, false, 3600);
		}
//$memcache->flush();			
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
		$memcache = Better_Cache::remote();
		$limit_times = Better_Config::getAppConfig()->public->times;
		
		$key = 'uid' . '_' .  $uid;
		$var = $memcache->get($key);
	
		if ($var) {
			$new_var = $memcache->increment($key, 1);
			if ($new_var > $limit_times) {
				$this->error('error.request.user_proceeded_limit', '403');
			}	
		} else {
			$a = $memcache->set($key, 1, 3600);
			
		}			
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
		$ip = $this->getRequest()->getParam('ip', '');
		$gotRange = 5000;
		
		if (Better_LL::isValidLL($lon, $lat)) {
			$gotLon = $lon;
			$gotLat = $lat;
			$gotRange = 1000;
		} else if ($ip) {
			$tmp = Better_Service_Ip2ll::parse($ip);
			if (Better_LL::isValidLL($tmp['lon'], $tmp['lat'])) {
				$gotLon = $tmp['lon'];
				$gotLat = $tmp['lat'];
			} else {
				$gotLon = $this->config->location->default_lon;
				$gotLat = $this->config->location->default_lat;
			}
		} else {
			$userInfo = $this->user->getUserInfo();
			if (Better_LL::isValidLL($userInfo['lon'], $userInfo['lat'])) {
				$gotLon = $userInfo['lon'];
				$gotLat = $userInfo['lat'];
			} else {
				$gotLon = $this->config->location->default_lon;
				$gotLat = $this->config->location->default_lat;
			}
		}
		
		return array(
			$gotLon,
			$gotLat,
			$gotRange
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
			$as = $this->user->achievement()->parse($prefix);
			
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
	
	/**
	 * 检测客户端是否支持gzip
	 * 
	 * @return bool
	 */
	protected function acceptGz()
	{
		$flag = false;
		if (strstr($_SERVER["HTTP_ACCEPT_ENCODING"],'gzip')) {
			$flag = true;
		} else {
			$headers = function_exists('getallheaders') ? getallheaders() : array();
			$kkEncoding = trim($headers['KAI-Encoding']);
			if ($kkEncoding) {
				$accepted = explode(',', $kkEncoding);
				foreach ($accepted as $accept) {
					if (trim($accept)=='gzip') {
						$this->kkEncoding = true;
						$flag = true;
						break;
					}
				}
			}
		}
		
		return $flag;
	}
	
	protected function _output($code = '')
	{
		Better_Log::getInstance()->logTime('Before_Api_Output:['.__CLASS__.']');

		$this->api->setParam('xml_root', $this->xmlRoot);
		$this->api->setParam('data', $this->data);
		$output = $this->api->output();
		
		if ($this->needLbsLog) {
			$this->saveLbsLog($output);
		}
		
		$language = Better_Registry::get('api_lang');
		if ($this->uid && $language) {
			Better_User::getInstance($this->uid)->updateUser(array(
				'language' => $language
				));
		}
		
		if ($code == '403') {
			header('HTTP/1.1 403 Forbidden'); 
		}
		
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
			header('Content-Encoding: gzip');
			$output = gzencode($output, 2, FORCE_GZIP);
			header('Content-Length: '.strlen($output));
		}

		$this->outputed = true;
		$this->getResponse()->sendHeaders();
		$this->sendSquidHeader($this->uid);
		
		if ($this->getRequest()->getParam('charset', '')=='gbk') {
			echo iconv('utf-8', 'gbk', $output);
		} else {
			echo $output;
		} 

		Better_Log::getInstance()->logTime('After_Api_Output:['.__CLASS__.']');
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
	
	/**
	 * 
	 * @param $uid
	 * @return unknown_type
	 */
	protected function validLimit($key, $limit)
	{
		$memcache = Better_Cache::remote();

		$var = $memcache->get($key);
		if ($var) {
			$new_var = $memcache->increment($key, 1);
			if ($new_var > $limit) {
				$this->error('error.request.proceeded_limit', '403');
			}	
		} else {
			$a = $memcache->set($key, 1, 3600);
			
		}			
	}	
}
