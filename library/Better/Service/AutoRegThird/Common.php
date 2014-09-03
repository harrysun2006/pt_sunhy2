<?php

/**
 * api与twiiter类似的几个网站的通用处理，如zuosa.com,9911.com等
 *
 * @author leip <leip@peptalk.cn>
 */

class Better_Service_AutoRegThird_Common extends Better_Service_AutoRegThird_Base
{
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
	
	public function __construct($username='',$password='')
	{
		$this->setUserPwd($username,$password);
	}
	
	public function fakeReg()
	{
		$logined = false;
		$client = new Zend_Http_Client($this->_login_url, array(
			'keepalive' => true,
			));
		$client->setAuth($this->_username, $this->_password);
		$client->request();
		$html = $client->getLastResponse()->getBody();

		try {
			$dom = new Zend_Dom_Query();
			$dom->setDocumentXML($html);
			$items = $dom->query($this->_login_find_key);
			foreach($items as $item) {
				$logined = trim($item->nodeValue)!='' && strtolower($item->nodeValue)=='true' ? true : false;
				break;
			}
		} catch(Exception $e) {
			
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
		$str .= "\r\n";
		
		$c_timezone = date_default_timezone_get();
		date_default_timezone_set('Asia/Shanghai');
		$filename = '/home/fj/' . $filename . date('Y-m-d') .'.log';
		$fp = fopen($filename, 'a');
		fwrite($fp, $str);
		fclose($fp);
		
		date_default_timezone_set($c_timezone);
	}

	/**
	 * 向接口提交状态信息
	 * 成功返回true，失败返回false
	 *
	 * @param string $msg
	 * @return bool
	 */
	public function post($msg, $attach='')
	{
		$this->_logined==false && $this->login();
		$flag = false;

		$b_time = time();
		
		if ($this->_logined) {
			$client = new Zend_Http_Client($this->_api_url , array(
				'keepalive' => true,
				));
			$client->setAuth($this->_username, $this->_password);
			$client->setParameterPost($this->_request);

			if ($attach!='' && file_exists($attach)) {
				$client->setFileUpload($attach, $this->_file);
			}
			$client->request(Zend_Http_Client::POST);
			$html = $client->getLastResponse()->getBody();
			
			$exec_time = time() - $b_time;
			$str = date('Y-m-d H:i:s') . '||' . $exec_time . '||' . $this->_username . "||" . $this->_api_url . '||' . serialize($this->_request) . '||'. $client->getLastResponse()->getStatus() . '||' . serialize($client->getLastResponse()->getHeaders()) . '||' . $html; 			
			$this->_log($str, 'sync_zend_http');
			
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
	 * 解析http状态码
	 * 
	 */
	public function checkHttpCode($code, $boby)
	{
		$r = array(true, 'ok');
		switch ($code) {
			case 200:
				$r = array(true, 'ok');
			break;
			
			case 304:
				$r = array(true, 'ok');
			break;
			
			case 400:
				$r = array(true, 'ok');
			break;
			
			case 401:
				$r = array(true, 'ok');
			break;

			case 403:
				$r = array(true, 'ok');
			break;

			case 404:
				$r = array(true, 'ok');
			break;			
			
			case 500:
				$r = array(true, 'ok');
			break;

			case 502:
				$r = array(true, 'ok');
			break;

			case 503:
				$r = array(true, 'ok');
			break;
			
			default:
				$r = array(false, 'error');;
			break;
		}
	}
	
}
