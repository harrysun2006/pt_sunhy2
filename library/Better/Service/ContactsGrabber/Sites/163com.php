<?php

/**
 * 获取163.com邮箱联系人
 *
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 *
 */

class Better_Service_ContactsGrabber_Sites_163com extends Better_Service_ContactsGrabber_Abstract
{

	public function __construct($params = array())
	{
		$this->protocol = '163.com';
		
		$this->init($params);

		$this->params['login_url'] = 'http://reg.163.com/login.jsp?type=1&url=http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight%3D1%26verifycookie%3D1%26language%3D-1%26style%3D35';
		$this->params['cookie_jar2'] = tempnam($this->cache_path, 'cookie2_' . $this->protocol . '_');
	}
	
	public function __destruct()
	{
		@unlink($this->params['cookie_jar']);
		@unlink($this->params['cookie_jar2']);
	}

	/**
	 * 实现抽象类中的登录方法
	 *
	 * @return void
	 */
	public function login()
	{
		$request = 'verifycookie=1&username=' . $this->params['username'] . '@163.com&password=' . $this->params['password'];
		$request .= '&selType=jy&style=-1&product=mail163&remUser=&secure=&url=';
		$request .= 'http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight%3D1%26verifycookie%3D1%26language%3D-1%26style%3D35';
		$request .= '&type=1&savelogin=&outfoxer=&domains=&' . urlencode('登录邮箱') . '=' . urlencode('登录邮箱');
		
		$ch = curl_init();
		curl_setopt_array($ch, array(CURLOPT_POSTFIELDS=>$request, CURLOPT_COOKIEJAR=>$this->params['cookie_jar'], CURLOPT_RETURNTRANSFER=>1, CURLOPT_HEADER=>1, CURLOPT_POST=>1, CURLOPT_URL=>$this->params['login_url'], CURLOPT_USERAGENT=>$this->agent));
		$body = curl_exec($ch);
		curl_close($ch);
		$this->parseCookie($body);

		if ($this->cookies['NTES_SESS']!='' && $this->cookies['S_INFO']) {
			$this->logined = true;
		}

		return $this->logined;
	}

	/**
	 * 实现抽象类中的获取联系人的方法
	 *
	 * @return misc
	 */
	public function getContacts()
	{
		if ($this->logined) {
			$ch = curl_init();
			curl_setopt_array($ch, array(CURLOPT_URL=>'http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight=1&verifycookie=1&language=-1&style=-1&username=' . $this->params['username'], CURLOPT_HEADER=>0, CURLOPT_RETURNTRANSFER=>1, CURLOPT_FOLLOWLOCATION=>1, CURLOPT_COOKIEFILE=>$this->params['cookie_jar'], CURLOPT_COOKIEJAR=>$this->params['cookie_jar2'], CURLOPT_USERAGENT=>$this->agent));
			$html = curl_exec($ch);
			curl_close($ch);
			
			//获取登录后163邮箱的sid标识
			$dom = new DOMDocument();
			$res = $dom->loadHTML($html);
			$iframes = $dom->getElementsByTagName('iframe');
			$src = $iframes->item(0)->getAttribute('src');
			unset($dom);
			unset($iframes);
			$sess = str_replace('index.jsp?sid=', '', $src);
			
			$exportUrl = 'http://tg3a64.mail.163.com/js3/s?sid='.$sess.'&func=pab%3AexportContacts&outformat=8';
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $exportUrl, 
				CURLOPT_HEADER => 0, 
				CURLOPT_RETURNTRANSFER => 1, 
				CURLOPT_COOKIEFILE => $this->params['cookie_jar2']
				));
			$html = curl_exec($ch);
			curl_close($ch);
			
			$html = iconv('GB2312', 'UTF-8', $html);
			$lines = explode("\n", $html);
			
			$j = 0;
			for($i=1;$i<count($lines)-5;$i++) {
				$row = explode(',', $lines[$i]);
				
				$this->contacts[$j++] = array(
					'name' => $row[1],
					'email' => $row[2],
					);
			}
		
			return $this->outputResult();
		} else {
			$this->Error('Please login to '.$this->protocol.' first');
		}
		
		return false;
	}
}