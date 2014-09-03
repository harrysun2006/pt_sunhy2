<?php

/**
 * 获取qq.com邮箱联系人
 * 
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 * 
 */

class Better_Service_ContactsGrabber_Sites_Qqcom extends Better_Service_ContactsGrabber_Abstract
{

	/**
	 * 
	 */
	function __construct($params=array())
	{
		$this->protocol = 'qq.com';
		parent::__construct($params);
		
	}

	/**
	 * 
	 * @see abstractContactsGrabber::getContacts()
	 */
	public function getContacts()
	{
	
	}

	/**
	 * 
	 * @see abstractContactsGrabber::login()
	 */
	public function login()
	{
		$login_page = 'http://mail.qq.com/cgi-bin/loginpage';
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL=>$login_page,
			CURLOPT_RETURNTRANSFER=>1,
			CURLOPT_FOLLOWLOCATION=>1,
			));
		$html = curl_exec($ch);
		curl_close($ch);
		
		preg_match('/name="ts"\svalue="(\d+)"/',$html,$tspre);		
		$ts = $tspre[1];
		preg_match('/action="http:\/\/(m\d+)\.mail\.qq\.com/', $html, $server);
		$server_no = $server[1];
		
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL=>'http://ptlogin2.qq.com/getimage?aid=23000101&t='.rand(100000,999999),
			CURLOPT_HEADER=>1,
			CURLOPT_RETURNTRANSFER=>1,
			CURLOPT_FOLLOWLOCATION=>1,
			CURLOPT_COOKIEJAR=>$this->params['cookie_jar'],
			));
		$html = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if ($http_code!=200) {
			$this->Error('QQ Server downed');
		}
		
		$this->parseCookie($html);
		$vcode = $this->cookies['verifysession'];
		
		$public_key = 'CF87D7B4C864F4842F1D337491A48FFF54B73A17300E8E42FA365420393AC0346AE55D8AFAD975DFA175FAF0106CBA81AF1DDE4ACEC284DAC6ED9A0D8FEB1CC070733C58213EFFED46529C54CEA06D774E3CC7E073346AEBD6C66FC973F299EB74738E400B22B1E7CDC54E71AED059D228DFEB5B29C530FF341502AE56DDCFE9';
		$hash = '10001';
		$pkey = new Zend_Crypt_Rsa_Key_Private($public_key);
		$rsa = new Zend_Crypt_Rsa(array(
			'certificateString'	=>	$public_key,
			));
		$p = $rsa->encrypt($this->params['password']."\n".$ts."\n",$pkey);	
		
		$request = 'sid=0,2,zh_CN&firstlogin=false&starttime=&redirecturl=&f=html&p='.$p.'&delegate_url=&s=&ts='.$ts;
		$request .= '&from=&uin='.$this->params['username'].'&aliastype=selqq&pp='.$this->params['password'];
		$request .= '&verifycode='.$vcode;
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL=>'http://'.$server_no.'.mail.qq.com/cgi-bin/login?sid=0,2,zh_CN',
			CURLOPT_HEADER=>1,
			CURLOPT_RETURNTRANSFER=>1,
			CURLOPT_FOLLOWLOCATION=>1,
			CURLOPT_COOKIEJAR=>$this->params['cookie_jar'],
			CURLOPT_COOKIEFILE=>$this->params['cookie_jar'],
			CURLOPT_POST=>1,
			CURLOPT_POSTFIELDS=>$request,
			));
		$html = curl_exec($ch);
		curl_close();
		$this->parseCookie($html);
		
		echo '<HR>'.$request.'<HR>';
		die(nl2br(htmlspecialchars($html)));
	}
	
	/**
	 * 
	 */
	function __destruct()
	{
		@unlink($this->params['cookie_jar']);
	}
}
