<?php

/**
 * api与twiiter类似的几个网站的通用处理，如zuosa.com,9911.com等
 *
 * @author leip <leip@peptalk.cn>
 */

class Better_Service_PushToOtherSites_Common extends Better_Service_PushToOtherSites_Base
{
	public $_accecss_token = '';
	public $_accecss_token_secret = '';
	public $_expires_in = 0;
		
	public $_protocol = '';
	public $userinfo_json = '';
	public $tid = '';
	
	/**
	 * 验证用户帐号的url
	 *
	 * @var string
	 */
	protected $_login_url = '';
	
	protected $_login_find_key = 'name';
	
	/**
	 * 提交状态的api地址
	 * 他们的api接口地址都是类似这样的：'http://api.xxxx.com/statuses/update.xml'
	 *
	 * @var string
	 */
	protected $_api_url = '';
	
	/**
	 * 提交给api接口的参数
	 *
	 * @var string
	 */
	protected $_request = '';
	
	protected $_host = '';

	protected $_file = 'file';
	
	public $flag = false;
	
	public $html = '';
	
	public $third_id = 0;
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
	}
	
	public function fakeLogin($uid='')
	{
		if ($uid) return true;
		
		$logined = false;
		
		try {
			$client = new Zend_Http_Client($this->_login_url, array(
						'keepalive' => true,
						));			
			$client->setAuth($this->_username, $this->_password);
			$client->request();
			$html = $client->getLastResponse()->getBody();
			$status = $client->getLastResponse()->getStatus();	
			$r = self::checkHttpCode($status, $html, 1);
			list($logined, $text) = $r;
			
			if ('zuosa.com' == $this->_protocol) {
				$json = json_decode($html);
				$logined = $json->authorized;
			}		
			
			//解除绑定
			if ($status == '401') {
				//$this->unSync($uid, $this->_protocol);	
			}
			if ($logined) {
				$json = json_decode($html);
				$this->userinfo_json = $json;
				$json->id && $this->tid = $json->id;
			}
			
		} catch (Exception $ee) {
			$logined = false;
		}
		
		return $logined;
	}

	/**
	 * api接口不提供单独的登录过程，需要在使用具体接口的同时发送用户名密码
	 * 所以这里只能返回true
	 *
	 * @return bool
	 */
	public function login()
	{
		$this->_logined = true;
	}
	
	/**
	 * log
	 * 
	 */
	
	public function _log($str, $filename)
	{
		$str = str_replace(array("\r", "\n"), '', $str);
		
		$full_filename = APPLICATION_PATH . '/../logs' . '/' . $filename . '.log';		
		Better_Log::getInstance()->logAlert($str, $filename);
		chmod($full_filename, 0666);

	}
	
	/**
	 * 向接口提交状态信息
	 * 成功返回true，失败返回false
	 *
	 * @param string $msg
	 * @return bool
	 */
	public function post($msg, $attach='', $poiId='', $geo='')
	{
		$this->_logined==false && $this->login();
		$flag = false;

		$b_time = time();
		
		if ($this->_logined) {
			$client = new Zend_Http_Client($this->_api_url , array(
				'keepalive' => true,
				//'httpversion' => '1.0',
				));			
			$client->setAuth($this->_username, $this->_password);
			$client->setParameterPost($this->_request);

			if ($attach!='' && file_exists($attach)) {
				$client->setFileUpload($attach, $this->_file);
			}
			$client->request(Zend_Http_Client::POST);
			$status = $client->getLastResponse()->getStatus();
			$html = $client->getLastResponse()->getBody();
			$this->html = $html;
							
			$r = $this->checkHttpCode($status, $html , 1);
			list($flag, $text) = $r;
			
			if (strpos($this->_api_url, 'api.9911.com') !== false) { //对于9911需要判断是返回的内容的					
				$flag = $this->checkPost($html);
			}
			
			$exec_time = time() - $b_time;
			$str = date('Y-m-d H:i:s') . '||' . $exec_time . '||' . $this->_username . "||" . $this->_api_url . '||' . serialize($this->_request) . '||'. $client->getLastResponse()->getStatus() . '||' . serialize($client->getLastResponse()->getHeaders()) . '||' . $html; 			
			$this->_log($str, 'sync_zend_http');
	
		}
		
		$this->flag = $flag;
		
		return $flag;
	}
	
	
	/*
	 * 删除
	 */
	public function delete($id, $mode='p')
	{
		$client = new Zend_Http_Client($this->_api_url , array(
			'keepalive' => true,
			));
			
		$client->setAuth($this->_username, $this->_password);
		$client->setParameterPost($this->_request);	
		if ($mode == 'p') {
			$client->request(Zend_Http_Client::POST);
		} elseif ($mode == 'd') {
			$client->request(Zend_Http_Client::DELETE);
		} else {			
			$client->request(Zend_Http_Client::GET);
		}
		
		$status = $client->getLastResponse()->getStatus();
		$html = $client->getLastResponse()->getBody();
		$this->html = $html;
						
		$r = $this->checkHttpCode($status, $html , 1);
		list($flag, $text) = $r;
		
		if (strpos($this->_api_url, 'api.9911.com') !== false) { //对于9911需要判断是返回的内容的
			$flag = $this->checkPost($html);
		}

		return $flag;
	}
	
	/**
	 * 根据接口返回的xml信息判断状态更新是否成功
	 * 具体实现在各个子类的相应方法
	 *
	 * @param string $return
	 * @return bool
	 *
	 */
	public function checkPost($return)
	{
		return true;
	}
	
	/**
	 * 通过socket5翻墙同步
	 * 
	 * @param string $msg
	 * @return bool
	 */
	public function postViaProxy($msg, $attach='')
	{
		$this->_logined==false && $this->login();
		$flag = false;

		if ($this->_logined) {
			$authCredentials=base64_encode($this->_username.':'.$this->_password);
			$request[] = "POST ".$htis->_api_url."?status=".$msg." HTTP/1.1";
			$request[] = "Host: ".$this->_host;
			$request[] = "Content-Type: application/x-www-form-urlencoded";
			$request[] = "Authorization: Basic {$authCredentials}";
			$request[] = "Connection: Close";
		
			$socket5 = Better_Proxy::getSocket($this->_host, 80);
			if($socket5 instanceof Better_Socket5) {
				$html = $socket5->request(implode("\r\n", $request) . "\r\n\r\n");
				
				$flag = $this->checkPost($html);
				
				unset($socket5);
			}
		}
		
		return $flag;		
	}
	
	/**
	 * 通过socket5翻墙登录
	 * 
	 * @return bool
	 */
	public function fakeLoginViaProxy()
	{
		$authCredentials = base64_encode($this->_username.':'.$this->_password);

		$request = array();
		$request[] = "GET ".$htis->_login_url." HTTP/1.1";
		$request[] = "Host: ".$this->_host;
		$request[] = "Content-Type: application/x-www-form-urlencoded";
		$request[] = "Authorization: Basic {$authCredentials}";
		$request[] = "Connection: Close";

        $logined = false;
        $socket5 = Better_Proxy::getSocket($this->_host, 80);
        
        if ($socket5 instanceof Better_Socket5) {
			$html = $socket5->request(implode("\r\n", $request) . "\r\n\r\n");

			try {
				$dom = new Zend_Dom_Query();
				$dom->setDocumentXML($html);
				$items = $dom->query($this->_login_find_key);
				foreach($items as $item) {
					$logined = trim($item->nodeValue)!='' && strtolower($item->nodeValue)=='true' ? true : false;
					break;
				}
			} catch(Better_Exception $e) {

			}	

			unset($socket5);
        }
        
		return $logined;		
	}
	
	/**
	 * 解析同步返回值
	 * 
	 */
	public function checkHttpCode($code, $boby, $log_info='')
	{			
		$mail = false;
		
		switch ($code) {
			case 200:
			case 201:
				$r = array(true, $boby);
			break;
			
			//304 Not Modified: 没有数据返回. 						
			case 304:
				$r = array(true, 'http304:' . $boby);
			break;
			
			//400 Bad Request: 请求数据不合法，或者超过请求频率限制. 
			//401 Not Authorized: 没有进行身份验证. //不会返回
			//402 Not Start mblog: 没有开通微博 //不会返回
			//403 Forbidden: 没有权限访问对应的资源. 
			//404 Not Found: 请求的资源不存在. 
			
			//认证不对
			//sina 返回403	
			//做啥返回401	sohu返回401 9911返回401	嘀咕返回401
						
			case 400:
			case 401:
			case 403:
			case 404:
				$r = array(false, $boby);
			break;			
			
			//500 Internal Server Error: 服务器内部错误. 
			//502 Bad Gateway: 微博接口API关闭或正在升级 . 
			//503 Service Unavailable: 服务端资源不可用. 			
			case 500:
			case 502:
			case 503:
				$mail = true;
				$r = array(false, $boby);
			break;
			
			default:
				$r = array(false, 'code is:' . $code . '||' . $boby);;
			break;
		}
		 		
		if (!$r[0] && $log_info) {
			$filename = $this->_protocol;
			$boby = $code . '||' . $this->_username . '||' . $this->_password . '||' . $boby;
			self::_log($boby, $filename);
		} 	

	 	if ($mail) {
	 		$msg = $this->_protocol  . ' 服务器异常！' . ',' . $boby;
	 		self::sendMail($msg);	
	 	}
	 	
		return $r;
	}
	
	
	/**
	 * 解除第三方绑定
	 */
	
	public function unSync($uid, $protocol)
	{
		Better_User::getInstance($uid)->syncsites()->delete($protocol);
	}
	
	
	/**
	 * 得到第三方id 不同的解析由各个协议中实现
	 *
	 */
	public function get3rdId()
	{
		$json = json_decode($this->html);	
		$id = $json->id;
		
		$this->third_id = $id;
		
		return $id;
	}
	
	/**
	 * 得到第三方uid
	 */
	public function get3rdUid()
	{
		$json = json_decode($this->html);	
		$uid = $json->id;
		
		$this->tid = $uid;
		
		return $uid;		
	}

	
	/**
	 * 
	 * @param $uid
	 * @param $protocol
	 * @param $username
	 * @return unknown_type
	 */
	public static function checkAccount($uid, $protocol, $username)
	{
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		foreach($sids as $sid) {
			$cs = Better_DAO_Base::assignDbConnection('user_server_' . $sid, true);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();		
			$sql = $rdb->quoteInto("SELECT * FROM `better_3rdbinding` WHERE `protocol`='$protocol' AND `username`=?", $username);
			$result = $rdb->query($sql);			
			$row = $result->fetch();		
			if ($row) {
				return false;
			}

		}
		
		return true;
	}
	
	/**
	 * 
	 * @param $message
	 * @return unknown_type
	 */
	public function parseBlogAt($message)
	{
		
		$result = trim($message);
		$atNames = array();
		if (strlen($result)) {
			$result = ' ' . $result . ' ';
			$pat = '#@([^@\s\n\r\t ]+)([\s\n\r\t ]{1})#is';
			preg_match_all($pat, $result, $all);
			if (is_array($all[0]) && count($all[0])) {
				$ns = array_unique($all[1]);
				foreach ($ns as $nickname) {
					$nickname = $nickname;
					if (isset($atNames[$nickname])) {
						$t_username = $atNames[$nickname];
						$result = preg_replace('#@'.self::__preParseNickname($nickname).'([\s\n\r\t ]{1})#is', "@$t_username ", $result);
					} else {
						if (mb_strlen($nickname) > 20) continue;
						$username = Better_DAO_User::getUsernameByNickname($nickname);
						$objectUser = Better_User::getInstance();
						$objectUser->dispUserInfo = $objectUser->getUserByUsername($username);
						$syncSites = (array)Better_User_Syncsites::getInstance($objectUser->dispUserInfo['uid'])->getSites();
						$sync_keys = array_keys($syncSites);
						if (!in_array($this->_protocol, $sync_keys)) continue;
						//$t_username = $this->getThirdUserName($syncSites[$this->_protocol]['username'], $syncSites[$this->_protocol]['password'], $syncSites[$this->_protocol]['oauth_token'], $syncSites[$this->_protocol]['oauth_token_secret']);
						$t_username = $this->getThirdUserName($syncSites[$this->_protocol]);
						if ($t_username) {
							$atNames[$nickname] = $t_username;					
							$result = preg_replace('#@'.self::__preParseNickname($nickname).'([\s\n\r\t ]{1})#is', "@$t_username ", $result);
						}
					}
				}
				
			}
			
			$result = trim($result);
		}
		
		return $result;
	}	
	/**
	 * 
	 * @param $username
	 * @param $password
	 * @return unknown_type
	 */
	public function getThirdUserName($parms)
	{
		$return_username = '';
		
		$username = $parms['username']; 
		$password = $parms['password']; 
		$client = new Zend_Http_Client($this->_login_url, array(
			'keepalive' => true,
			));
		$client->setAuth($username, $password);
		$client->request();
		$html = $client->getLastResponse()->getBody();
		$status = $client->getLastResponse()->getStatus();
		if ($status == 200) {
			$json = json_decode($html);
			$return_username = $json->name;	
		}	
		return $return_username;
	}
	
	/**
	 * 
	 * @param $nickname
	 * @return unknown_type
	 */
	protected static function __preParseNickname($nickname)
	{
		
		$nickname = str_replace('\\', '\\\\', $nickname);
		$nickname = str_replace('/', '\/', $nickname);
		$nickname = str_replace('(', '\(', $nickname);
		$nickname = str_replace(')', '\)', $nickname);
		$nickname = str_replace('^', '\^', $nickname);
		$nickname = str_replace('[', '\[', $nickname);
		$nickname = str_replace(']', '\]', $nickname);
		$nickname = str_replace('*', '\*', $nickname);
		$nickname = str_replace('|', '\|', $nickname);
					
		return $nickname;
	}
	
	/**
	 * 得到粉丝数
	 */
	public function getFollowers()
	{
		$json = json_decode($this->html);	
		$followers_count = $json->user->followers_count;
		
		return (int)$followers_count;		
	}
	
	/**
	 * 关注的人的列表
	 * @return unknown_type
	 */
	public function getFollowerids()
	{
		return array();
	}
	
	/**
	 * 
	 */
	public function followKai()
	{
		return false;
	}
	
	/**
	 * 
	 * @param $msg
	 * @return unknown_type
	 */
	public function sendMail($msg){
		$receiver = serialize(array(
			//'chenc@peptalk.cn',
			//'hanc@peptalk.cn',
			'fengj@peptalk.cn',
			'gaosj@peptalk.cn',
			//'shiyy@peptalk.cn',
		));
		
		$f = Better_DAO_EmailCommonQueue::getInstance()->insert(array(
				'uid' => 0,
				'receiver' => $receiver,
				'body' => $msg,
				'queue_time' => time(),
				'subject' => '第三方服务器异常！'
		));
		
		return $f;
	}
	
	
	/**
	 * 
	 * @return unknown_type
	 */
	public function sendVipMail($msg)
	{
		$receiver = serialize(array(
					'fengj@peptalk.cn',
					'gaosj@peptalk.cn',
					'luoym@peptalk.cn',
					'chenc@peptalk.cn',
				));								
 		$mail_data = array(
						'uid' => 0,
						'receiver' => $receiver,
						'body' => $msg,
						'queue_time' => time(),
						'subject' => '第三方VIP用户加入开开'
				);
		Better_DAO_EmailCommonQueue::getInstance()->insert($mail_data);								
	}
	
}
