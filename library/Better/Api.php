<?php

/**
 * Better的API处理
 *
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api
{
	const VERSION = '20091021.1135';
	
	public $formats = array('xml', 'json');
	protected $params = array(
											'xml_root' => 'place',
											'format' => 'xml',
											'data' => array(),
											);
											
	public $authMsg = '';
	protected $token = '';
	protected static $instance = null;
	
	public function __construct($params=array())
	{
		Better_Registry::get('sess')->set('uid', 0);
		Better_Registry::get('sess')->set('user', array());
		
		$this->setParam($params);
		
		$this->token = Better_Config::getAppConfig()->api->token;
	}
	
	function __destruct()
	{
		$this->clean();
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * 将数据格式化返回
	 *
	 * @return string
	 */
	public function output()
	{
		switch($this->params['format']) {
			case 'json':
				$output = json_encode((array)$this->params['data']);
				break;
			case 'xml':
			default:
				$xml = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
				$xml .= '<'.$this->params['xml_root'].'>';
		
				$xml .= self::array2XML($this->params['data'][$this->params['xml_root']]);
				$xml .= '</'.$this->params['xml_root'].'>';
				$output = &$xml;
				break;
		}

		return $output;
	}
	
	/**
	 * 设置参数
	 *
	 * @param misc $key
	 * @param misc $val
	 * @return null
	 */
	public function setParam($key, $val=null)
	{
		if (is_array($key)) {
			foreach ($key as $k=>$v) {
				$this->params[$k] = $v;
			}
		} else if ($val==null && array_key_exists($key, $this->params)) {
			unset($this->params[$key]);
		} else {
			$this->params[$key] = $val;
		}

	}
	
	/**
	 * 生成一个登录Token
	 * 
	 * @param unknown_type $uid
	 * @return array
	 */
	public function loginToken($uid)
	{
		$result = array();
		
		$result['username'] = 'pt';
		$result['token'] = $this->encToken($uid);
		
		return $result;
	}
	
	public function decToken($token)
	{
		$result = 0;
		
		$key = md5($this->token);
		$bin = Better_Functions::hex2bin($token);
		$decUid = trim(mcrypt_ecb(MCRYPT_RIJNDAEL_128, $key, $bin, MCRYPT_DECRYPT));
		
		if ($decUid) {
			$user = Better_User::getInstance($decUid);
			if ($token==$user->cache()->get('api_token')) {
				$result = $decUid;
			}
		}		

		return $result;
	}
	
	protected function encToken($uid)
	{
		$token = '';
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUser();
		
		if ($userInfo['uid']) {
			$key = md5($this->token);
			$token = bin2hex(mcrypt_ecb(MCRYPT_RIJNDAEL_128, $key, $userInfo['uid'], MCRYPT_ENCRYPT));
			$user->cache()->set('api_token', $token);
		}
		
		return $token;
	}
	

	/**
	 * 
	 * @param $s
	 * @return unknown_type
	 */
	public static function decodeAuthkai($s)
	{
		$temp = explode(',', $s);
		$s = array_pop($temp);
		$s = trim($s);
		
		$r = array();
		$b = base64_decode($s);
		list($username, $password) = explode(':', $b);
		
		if ($username) {
			$r = array($username, $password);
		}
		
		return $r;
	}
	

	/**
	 * 判断HTTP Basic验证过程
	 *
	 * @return bool
	 */
	public function authValidate($partner='')
	{
		$valid = false;
		
		$this->fcgiFix();

		if ( isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['HTTP_AUTH_KAI'] ) {
			$username = trim($_SERVER['PHP_AUTH_USER']);
			$username = Better_Functions::fixEmail($username);
			$password = $_SERVER['PHP_AUTH_PW'];
			
			if ( $_SERVER['HTTP_AUTH_KAI'] ) {
				list($username, $password) = self::decodeAuthkai(trim($_SERVER['HTTP_AUTH_KAI']));
				$username = Better_Functions::fixEmail($username);
				//error_log('Authkai:' . $username . ':' . $password . ':' . $_SERVER['HTTP_AUTH_KAI']);
			}
			$cacher = Better_Cache::remote();
			$cacheKey = md5('kai_api_auth_'.$username.'_'.$password);
			$cachedUid = $cacher->get($cacheKey);
			if (!$cachedUid) {
				if ($username=='pt') {
					$loginResult = Better_User_Login::tokenLogin($username, $password, false, false, $partner);
				} else {
					$loginResult = Better_User_Login::login($username, $password, false, false, $partner);
				}
			} else {
				$loginResult = Better_User_Login::LOGINED;
				Better_Registry::set('user', Better_User::getInstance($cachedUid));
				Better_Registry::get('sess')->set('uid', $cachedUid);
			}

			if ($loginResult==Better_User_Login::FORCE_VALIDATING) {
				$this->authMsg = Better_Registry::get('lang')->error->login->force_validating;
				Better_Log::getInstance()->logInfo('User:['.$_SERVER['PHP_AUTH_USER'].'], Pwd:['.$_SERVER['PHP_AUTH_PW'].'], Msg:['.$this->authMsg.']', 'api_auth_failed');
			} else if ($loginResult==Better_User_Login::ACCOUNT_BANNED) {
				$this->authMsg = Better_Registry::get('lang')->api->error->users->account_banned;
				Better_Log::getInstance()->logInfo('User:['.$_SERVER['PHP_AUTH_USER'].'], Pwd:['.$_SERVER['PHP_AUTH_PW'].'], Msg:['.$this->authMsg.']', 'api_auth_failed');
			} else if ($loginResult==Better_User_Login::LOGINED) {
				Better_Registry::get('sess')->init(false);
				$data = Better_Registry::get('user')->getUser();
				
				if ((int)$data['uid']>0) {
					Better_Registry::get('sess')->set('uid', $data['uid']);
					Better_Registry::get('sess')->set('user', $data);
					$valid = true;
					
					$cacher->set($cacheKey, $data['uid'], 30);
				}
			} else {
				Better_Log::getInstance()->logInfo('User:['.$_SERVER['PHP_AUTH_USER'].'], Pwd:['.$_SERVER['PHP_AUTH_PW'].'], Msg:['.$this->authMsg.'], LoginResult:['.$loginResult.']', 'api_auth_failed');
			}
		}
		
		return $valid;
	}
	
	/**
	 * 记录Api请求
	 *
	 * @param array $data
	 * @return misc
	 */
	public function logRequest($data)
	{
		//return Better_DAO_ApiLog::getInstance()->insert($data);
		return true;
	}
	
	/**
	 * 消息更新限制
	 *
	 * @return bool
	 */
	public static function validStatusUpdateRequest($uid)
	{
		return true;
	}
	
	/**
	 * API的ip限制
	 *
	 * @param string $ip
	 * @return bool
	 */
	public static function validIpRequest($ip)
	{
		return true;
	}
	
	/**
	 * 限制某些api只允许特定的ip访问
	 * 
	 * @param $ip
	 * @return bool
	 */
	public static function limitIpRequest($from, $to=0)
	{
		return true;
	}
	
	/**
	 * api的用户限制
	 *
	 * @param integer $uid
	 * @return bool
	 */
	public static function validUserRequest($uid)
	{
		$memcache = Better_Cache::remote();
		//$limit_times = Better_Config::getAppConfig()->api->times;
		$limit_times = 450;
		$key = 'uid' . '_' .  $uid;
		$var = $memcache->get($key);
	
		if ($var) {
			$new_var = $memcache->increment($key, 1);
			header("limit: $new_var");
			if ($new_var > $limit_times) {
				return false;
			}	
		} else {
			$a = $memcache->set($key, 1, 3600);
		}			
		
		return true;
	}
	
	/**
	 * 获取翻译数据的对象
	 * 
	 * @param $what
	 * @return Better_Api_Translator_Base
	 */
	public static function getTranslator($what)
	{
		return Better_Api_Translator::getInstance($what);
	}
		
	/**
	 * 将数组转换成XML
	 *
	 * @param array $data
	 * @return string
	 */
	protected static function array2XML(&$data)
	{
		$xml = '';

		if (is_array($data)) {
			foreach ($data as $key=>$value) {
				if (!is_numeric($key)) {
					if (!is_array($value) && trim($value)=='') {
						$xml .= '<'.$key.' />';	
					} else {
						$xml .= '<'.$key.'>';
						$xml .= is_array($value) ? self::array2XML($value) : self::wrapXMLData($value);
						$xml .= '</'.$key.'>';
					}
				} else {
					$xml .= self::array2XML($value);
				}
			}
		} else {
			$xml .= $data;
		}

		return $xml;
	}
	
	/**
	 * 封装XML数据
	 *
	 * @param string $value
	 * @return string
	 */
	protected static function wrapXMLData($value)
	{
		$value = preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$value);
		if ($value!='') {
			if (strpos($value, ']]>')===false) {
				$value = "<![CDATA[".$value."]]>";
			} else {
				$value = '<![CDATA['.str_replace(']]>', '] ] >', $value).']]>';
			}			
		}
		
		return $value;
	}
	
	/**
	 * 清理API请求
	 * 1、删除session
	 *
	 * @return null
	 */
	protected function clean()
	{
		try {
			Better_Registry::get('sess')->set('uid');
			Better_Registry::get('sess')->set('user');			
		} catch(Better_Exception $e) {
			Better_Log::getInstance()->logInfo('Api clean failed', 'api');
		}
	}
	
	/**
	 * 修正apache fastcgi下basic认证问题
	 */
	protected function fcgiFix()
	{
		if (APPLICATION_ENV=='home') {
			//list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		}
	}

}
