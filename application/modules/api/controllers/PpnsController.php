<?php

/**
 * PPNS服务
 * 
 * @package Controllers
 * @author leip <leip@peptalk.cn>
 *
 */
class Api_PpnsController extends Better_Controller_Api
{
	protected $xml = '';
	protected $version = '1.0';
	protected $ppns = null;
	
	public function init()
	{
		parent::init();
		self::limitServerIpSource();
		
		$this->ppns = Better_Ppns::getInstance();
		$this->version = $this->ppns->version;
	}		
	
	/**
	 * 2.1 PPNS发起初始化
	 * 
	 * @return
	 */
	public function initAction()
	{
		$this->xml = "<kai version='".$this->version."'>";
		$this->xml .= "<init ret='ok' />";
		$this->xml .= "</kai>";
		
		$this->ppns->init();
		
		$this->output();
	}
	
	/**
	 * 2.3 PPNS发起注销
	 * 
	 * @return
	 */
	public function logoutAction()
	{
		$xml = $this->getRequest()->getRawBody();
		$ppnsXml = Better_Ppns::parseResponse($xml);
		
		$uid = Better_Ppns::sid2uid($ppnsXml['sid']);
		$flag = Better_Ppns_Auth::logout($ppnsXml['sid']);
		$this->xml = "<kai version='".$this->version."' sid='".$ppnsXml['sid']."'>";
		
		if ($flag) {
			$this->xml .= "<logout ret='ok' />";
		} else {
			$this->xml .= "<logout ret='error'></logout>";
		}
		
		$this->xml .= "</kai>";
		
		$this->output();
	}
	
	/**
	 * 2.2 PPNS 发起登录
	 * 
	 * @return
	 */
	public function loginAction()
	{
		$xml = $this->getRequest()->getRawBody();
		$ppnsXml = Better_Ppns::parseResponse($xml);

		try {
			$result = Better_Ppns_Auth::auth($xml, $ppnsXml['sid']);
		} catch (Exception $e) {
			$result = 0;
		}

		$this->xml = "<kai version='".$this->version."' sid='".$ppnsXml['sid']."'>";
		
		if ($result<=0) {
			$this->xml .= "<login ret='error'>".$result."</login>";
		} else {
			$this->xml .= "<login ret='ok' />";
		}
		$this->xml .= "</kai>";
		
		$sessUid = Better_Registry::get('sess')->get('uid');
		
		$this->output();
	}
	
	/**
	 * 2.6 推送应答
	 * 
	 * @return
	 */
	public function pushAction()
	{
		$xml = $this->getRequest()->getRawBody();
		$ppnsXml = Better_Ppns::parseResponse($xml);
		
		$params = array();
		$sid = '';
		
		$this->xml = "<kai version='".$this->version."' sid='".$ppnsXml['sid']."'>";
		
		$result = $this->ppns->push($params);
		
		if ($result['code']==$result['codes']['SUCCESS']) {
			$this->xml .= "<pushack ret='ok' />";
		} else {
			$this->xml .= "<pushack ret='error'>".$result['code']."</pushack>";
		}
		
		$this->xml .= "</kai>";
		
		$this->output();
	}
	
	public function faketerminateAction()
	{
		$sid = $this->getRequest()->getParam('sid', '');
		$return = $this->ppns->terminate($sid);
		
		die("Request Sent");
	}
	
	public function fakepushAction()
	{
		$email = trim(urldecode($this->getRequest()->getParam('email', '')));
		$sid = $this->getRequest()->getParam('sid', '');
		
		if ($email=='') {
			die('Email Required');	
		} else {
			$content = '<notifications><notifcation>This is a fake notification</notifcation></notifications>';
			$return = $this->ppns->push(array(
				'sid' => $sid,
				'content' => $content,
				'email' => $email,
				));
			die('Pushed');
		}
	}
	
	/**
	 * 输出XML
	 * 
	 * @return
	 */
	public function output()
	{
		header('Content-Type: text/xml; charset=utf-8');
		header('Content-Length: '.strlen($this->xml));
		
		echo $this->xml;
		exit;
	}
}
