<?php

/**
 * 联系人抓取的抽象类
 * 
 * @package ContactsGrabber
 * @author pang <leip@peptalk.cn>
 * 
 */
abstract class Better_Service_ContactsGrabber_Abstract
{
	/**
	 * 配置参数
	 * 
	 * @var array
	 */
	protected $params = array('username'=>'', 'password'=>'', 'verify_code'=>'', 'login_url'=>'');
	
	/**
	 * 缓存目录，用来保存临时文件/cookie等
	 * 
	 * @var string
	 */
	protected $cache_path = '';

	/**
	 * 联系人的分析结果
	 * 
	 * @var array
	 * 
	 */
	protected $contacts = array();
	
	/**
	 * 标识当前实例所应用的协议
	 * 
	 * @var string
	 */
	protected $protocol = '';
	
	/**
	 * 应用过程中生成的cookie
	 * 
	 * @var array
	 */
	protected $cookies = array();
	
	/**
	 * 要模拟的用户代理字符串
	 * 
	 * @var string
	 */
	protected $agent = 'Mozilla/5.0 (X11; U; Linux i686; zh-CN; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2';
	
	/**
	 * 调用getContacts后输出的数据类型
	 * 
	 * 支持如下方式：xml , json , php
	 * 
	 * @var string
	 */
	public $output_type = 'php';
		
	/*
	 * 是否登录的标识，在正确完成登录过程后变为true
	 * 
	 * @val bool
	 */
	public $logined = false;
	
	/**
	 * 验证email有效性的正则表达式
	 * 
	 * @var string
	 */
	const EMAIL_PAT = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix';
	
	public function __construct($params = array())
	{
		$this->init($params);
	}

	/**
	 * 抽象的登录方法，交给具体子类去实现
	 */
	abstract public function login();

	/**
	 * 抽象的获取联系人的方法，交给具体子类去实现
	 */
	abstract public function getContacts();

	/**
	 * 设置一些配置参数
	 * 
	 * @param array $params
	 * @return void
	 */
	public function init($params)
	{
		$this->cache_path = dirname(__FILE__) . '/cache';
		
		if (is_array($params) && count($params) > 0) {
			foreach ($params as $k=>$v) {
				$this->params[$k] = $v;
			}
		}
		$this->params['cookie_jar'] = tempnam($this->cache_path, 'cookie_'.$this->protocol.'_');
	}

	/**
	 * 输出联系人结果数据
	 * 
	 * @return misc
	 */
	protected function outputResult()
	{
		$return = '';
		
		switch ($this->output_type) {
			case 'php' :
				$return = &$this->contacts;
				break;
			case 'json' :
				$return = json_encode($this->contacts);
				break;
			case 'xml' :
			default :
				$dom = new DOMDocument('1.0', 'utf-8');
				$contacts = $dom->createElement('contacts');
				
				foreach ($this->contacts as $data) {
					$contact = $dom->createElement('contact');
					$name = $dom->createElement('name', $data['name']);
					$email = $dom->createElement('email', $data['email']);
					$contact->appendChild($name);
					$contact->appendChild($email);
					
					$contacts->appendChild($contact);
				}
				
				$dom->appendChild($contacts);
				$return = $dom->saveXML();
				unset($dom);
				break;
		}
		
		return $return;
	}
	
	/**
	 * 分析cookie文件中的cookie数据
	 * 
	 * @param string $file
	 * @return array
	 */
	protected function &parseCookie($headers='')
	{
		preg_match_all('/Set-Cookie: (.+)=(.+)$/m', $headers, $all);
		foreach($all[1] as $k=>$v) {
			$first = explode(';',$all[1][$k]);
			list($key,$value) = explode('=',$first[0]);
			$this->cookies[$key] = $value;
		}
		
		return $this->cookies;
	}
	
	/**
	 * 发起一个curl请求，并返回输出
	 * 
	 * @param array $params
	 * @return string $html
	 */
	protected function curl($params)
	{
		$ch = curl_init();
		curl_setopt_array($ch, $params);
		$html = curl_exec($ch);
		curl_close($ch);

		if (isset($params['CURLOPT_HEADER']) && $params['CURLOPT_HEADER']==1) {
			$this->parseCookie($html);
		}

		return $html;
	}
	
	/**
	 * 抛出异常
	 * 
	 * @param string $error
	 * @return string
	 */
	protected function Error($error) 
	{
		switch($this->output_type) {
			case 'json':
				echo json_encode(array('error'=>$error));
				exit(0);
				break;
			case 'xml';
				$dom = new DOMDocument('1.0', 'utf-8');
				$ele = $dom->createElement('error',$error);
				echo $dom->saveXML();
				unset($dom);
				exit(0);
				break;
			case 'php':
			default:
				throw  new Zend_Exception($error);
				break;
		}
	}
}