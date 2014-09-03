<?php

/**
 * PPNS服务
 * 
 * @package Better
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Ppns
{
	private static $_instance = null;
	private $config = array();
	public $version = '1.0';
	protected $socket = null;
	
	public $q = 0;
	
	private function __construct()
	{
		$this->config = Better_Config::getAppConfig()->ppns;	
	}
	
	function __destruct()
	{
		if (is_resource($this->socket)) {
			socket_close($this->socket);
		}
	}
	
	public static function getInstance()
	{
		if (self::$_instance==null) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	/**
	 * 连接到PPNS服务
	 * 
	 * @return bool
	 */
	public function connect()
	{
		$flag = false;
		
		if (!is_resource($this->socket)) {
			$this->socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
			$flag = socket_connect($this->socket, $this->config->host, (int)$this->config->port);
				$errorcode = socket_last_error(); 
  				$errormsg = socket_strerror($errorcode);
  				if ($errorcode) {
  					die($this->config->host."\n".$this->config->port."\n".$errorcode."\n".$errormsg."\n");
  				}
		}

		return $flag;
	}
	
	/**
	 * 初始化，清除所有ppns的session
	 * 
	 * @return bool
	 */
	public function init()
	{
		$result = false;
		
		try {
			Better_DAO_Ppns_Session::getInstance()->truncate();
		} catch (Exception $e) {
			
		}
		
		$result = true;
		
		return $result;
	}
	
	/**
	 * 向ppns服务器发起初始化请求
	 * 
	 * @return bool
	 */
	public function initRequest()
	{
		$codes = array(
			'FAILED' => 0,
			'SUCCESS' => 1,
			);
		$code = $codes['FAILED'];

		$xml = "<kai version='".$this->version."'>";
		$xml .= "<init />";
		$xml .= "</kai>";		
		$packet = "HTTP/1.1 \r\n";
		$packet .= "Content-Length: ".strlen($xml)."\r\n";
		$packet .= "\r\n";
		$packet .= $xml;
					
		$streamContext = stream_context_create();
		$apns = stream_socket_client('tcp://' . $this->config->host . ':' . $this->config->port, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
		
		fwrite($apns, $packet);
		$x = fread($apns, 4096);
		
		fclose($apns);		
		
		if ($error=='0') {
			$xml = '';
			
			$ppnsXml = self::parseResponse($xml);
			if ($ppnsXml['ret']=='ok') {
				$code = $codes['SUCCESS'];
			}
		}

		return array(
			'code' => $code,
			'codes' => &$codes
			);		
	}
	
	/**
	 * 终止PPNS链接
	 * 
	 * @return integer
	 */
	public function terminate($sid='')
	{
		$codes = array(
			'CONNECT_FAILED' => 0,
			'SUCCESS' => 1,
			);
		$code = $codes['CONNECT_FAILED'];

		$xml = "<kai version='".$this->version."' sid='".$sid."'>";
		$xml .= "<terminate />";
		$xml .= "</kai>";		
		$packet = "HTTP/1.1 \r\n";
		$packet .= "Content-Length: ".strlen($xml)."\r\n";
		$packet .= "\r\n";
		$packet .= $xml;
					

		$streamContext = stream_context_create();
		$apns = stream_socket_client('tcp://' . $this->config->host . ':' . $this->config->port, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
		
		fwrite($apns, $packet);
		$x = fread($apns, 4096);
		fclose($apns);		
		
		if ($error=='0') {
			$code = $codes['SUCCESS'];
			
			Better_DAO_Ppns_Session::getInstance()->deleteByCond(array(
				'ppns_sid' => $sid,
				));
		}

		return array(
			'code' => $code,
			'codes' => &$codes,
			'output' => $x,
			);
	}
	
	/**
	 * 执行一个简单的push给某用户
	 * 
	 * @param $uid
	 * @return bool
	 */
	public function simplePushToUid($uid, $isGame=false)
	{
		$result = false;
		
		$row = $this->getUserPpnsSession($uid);
		$sid = $row['ppns_sid'];

		if ($sid) {
			$act = $this->push(array(
				'sid' => $sid,
				'is_game' => $isGame,
				'uid' => $uid
				));
			$act['code']==$act['codes']['SUCCESS'] && $result = true;
		} else {
			$this->pushOffline($uid);
		}
		
		return $result;
	}
	
	/**
	 * 推送一个离线消息
	 * 
	 * @return bool
	 */
	public function pushOffline($uid)
	{
		/*
		$flag = false;
		
		if (!defined('BETTER_IN_GAME')) {
			Better_DAO_Ppns_Offline::getInstance()->deleteByCond(array(
				'uid' => $uid,
				));
				
			Better_DAO_Ppns_Offline::getInstance()->insert(array(
				'uid' => $uid,
				'dateline' => time(),
				'ready' => 0,
				));
			$flag = true;
		}
		
		return $flag;*/
	}
	
	/**
	 * 推送push
	 * 
	 * @return array
	 */
	public function push(array $params=array())
	{
		$codes = array(
			'CONNECT_FAILED' => 0,
			'SUCCESS' => 1,
			);
		$code = $codes['CONNECT_FAILED'];
		
		$uid = $params['uid'];
		$sid = $params['sid'];
		$content = $params['content'];
		$email = $params['email'];

		$xml = "<kai version='".$this->version."' sid='".$sid."'>";
		$xml .= '<push />';
		$xml .= "</kai>";		
		$packet = "HTTP/1.1 \r\n";
		$packet .= "Content-Length: ".strlen($xml)."\r\n";
		$packet .= "\r\n";
		$packet .= $xml;
			
		$streamContext = stream_context_create();
		$apns = stream_socket_client('tcp://' . $this->config->host . ':' . $this->config->port, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
		
		fwrite($apns, $packet);
		$x = fread($apns, 4096);
		fclose($apns);		
		
		if (!$error && !strpos($x, 'error')) {
			$code = $codes['SUCCESS'];
			
			Better_DAO_Ppns_Offline::getInstance()->cleanOfflineMsg($uid);
		} else {
			$this->pushOffline($uid);
		}
		
		return array(
			'code' => $code,
			'codes' => &$codes
			);
	}
	
	/**
	 * 推送一个离线消息
	 * 
	 * @return bool
	 */
	public function pushOfflineMsg($uid)
	{
		/*
		$dao = Better_DAO_Ppns_Offline::getInstance();

		if ($dao->hasOfflineMsg($uid)) {
			$dao->updateByCond(array(
				'ready' => '1'
				), array(
					'uid' => $uid
				));
		}*/
	}
	
	/**
	 * 分析ppns server的输出
	 * 
	 * @return array
	 */
	public static function parseResponse($xml, $key='')
	{
		$return = array(
			'version' => '',
			'sid' => '',
			'from' => '',
			'ret' => '',
			'code' => '',
			);
			
		if ($xml) {
			$dom = new DOMDocument();
			$dom->loadXML($xml);

			$root = $dom->childNodes->item(0);
			$attrs = $root->attributes;

			foreach ($attrs as $attr) {
				$return[$attr->name] = $attr->value;
			}

			if ($key) {
				$keyDom = $root->childNodes->item(1);
				$attrs = $keyDom->attributes;
				foreach ($attrs as $attr) {
					if ($attr->name=='ret') {
						$return['ret'] = $attr->value;
					}
				}
				
				$return['code'] = $keyDom->nodeValue;
			}
		}
		
		return $return;
	}
	
	/**
	 * 获取某个用户的PPNS会话id
	 * 
	 * @return string
	 */
	public function &getUserPpnsSession($uid)
	{
		$uid = (int)$uid;
		$row = array();
		
		if ($uid>0) {
			$row = Better_DAO_Ppns_Session::getInstance()->get(array(
				'uid' => $uid
				));
		}
		
		return $row;
	}
	
	/**
	 * 分配PPNS服务器地址
	 * 
	 * @return string
	 */
	public function allocPpns($uid=0)
	{
		$server = $this->config->client->internet;
		
		return $server;
	}
	
	/**
	 * 分配PTS服务器地址
	 * 
	 * @return string
	 */
	public function allocPts($uid=0)
	{
		$config = Better_Config::getAppConfig();
		$server = $config->pts->client->internet;
		
		return $server;
	}
	
	/**
	 * 查找某个ppns会话id对应的用户
	 * 
	 * @return integer
	 */
	public static function sid2uid($sid)
	{
		$row = Better_DAO_Ppns_Session::getInstance()->get(array(
			'ppns_sid' => $sid
			));
		$uid = isset($row['uid']) ? $row['uid'] : 0;
		
		return $uid;
	}
	
	/**
	 * 检查当前ppns会话表中哪些uid还是在线的
	 * 
	 * @return array
	 */
	public function statusCheck()
	{
		$uids = array();
		$sids = array();
		$rows = Better_DAO_Ppns_Session::getInstance()->getAll();
		
		$xml = "<kai version='".$this->version."'>";
		$xml .= "<statuscheck>";
		
		foreach ($rows as $row) {
			$xml .= "<status sid='".$row['ppns_sid']."' type='get' />";
			$sids[$row['ppns_sid']] = $row['uid'];
		}
		
		$xml .= "</statuscheck>";
		$xml .= "</kai>";		
		$packet = "HTTP/1.1 \r\n";
		$packet .= "Content-Length: ".strlen($xml)."\r\n";
		$packet .= "\r\n";
		$packet .= $xml;
			
		$streamContext = stream_context_create();
		$apns = stream_socket_client('tcp://' . $this->config->host . ':' . $this->config->port, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
		
		fwrite($apns, $packet);
		$x = fread($apns, 8192);
		fclose($apns);		
		
		if (!$error && !strpos($x, 'error')) {
			try {
				$dom = new Zend_Dom_Query();
				$xml = "<kai version='1.0'>".preg_replace('/^<kai version=\'1.0\'>(.*)<\/kai>$/i', '\\1', $x).'</kai>';
				$dom->setDocumentXml($xml);				
				
				$items = $dom->query('status');
				if (count($items)>0) {
					foreach ($items as $item) {
						$online = $item->getAttribute('ret');
						if ($online=='online') {
							$sid = $item->getAttribute('sid');
							if ($sid) {
								$uids[] = $sids[$sid];
							}
						}
					}					
				}
				
				unset($items);
				unset($dom);
			} catch (Exception $e) {
				
			}
		}

		return $uids;
	}
	
}
