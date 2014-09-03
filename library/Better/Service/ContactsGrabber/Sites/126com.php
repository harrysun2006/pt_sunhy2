<?php

/**
 * 获取126.com邮箱联系人
 *
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 *
 */

class Better_Service_ContactsGrabber_Sites_126com extends Better_Service_ContactsGrabber_Abstract
{
	public function __construct($params = array())
	{
		$this->protocol = '126.com';
		$this->init($params);

		$this->params['login_url'] = 'http://reg.163.com/login.jsp?type=1&url=http://entry.mail.126.com/cgi/ntesdoor?hid%3D10010102&3Dlightweight%3D1%26verifycookie%3D1%26language%3D-1%26style%3D35';
	}
	
	public function __destruct()
	{
		@unlink($this->params['cookie_jar']);
	}

	/**
	 * 实现抽象类中的登录方法
	 *
	 * @return void
	 */
	public function login()
	{
		$request = 'verifycookie=1&username=' . $this->params['username'] . '@126.com&password=' . $this->params['password'];
		$request .= '&selType=jy&style=-1&product=mail163&remUser=&secure=&url=';
		$request .= 'http://entry.mail.126.com/cgi/ntesdoor?hid%3D10010102%26lightweight%3D1%26';
		$request .= '&type=1&savelogin=&outfoxer=&domains=&' . urlencode('登录邮箱') . '=' . urlencode('登录邮箱');
		
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_POSTFIELDS => $request, CURLOPT_COOKIEJAR => $this->params['cookie_jar'], CURLOPT_RETURNTRANSFER => 1, CURLOPT_HEADER => 1, CURLOPT_FOLLOWLOCATION => false, CURLOPT_POST => 1, CURLOPT_URL => $this->params['login_url'], CURLOPT_USERAGENT=>$this->agent
		));
		$body = curl_exec($ch);
		
		//解析登录过后生成的cookie
		$this->parseCookie($body);
		curl_close($ch);

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
			//126邮箱要先登录passort
			$url = 'http://passport.126.com/crossdomain.jsp?username='.$this->params['username'].'@126.com';
			$url .= '&loginCookie='.$this->cookies['NTES_SESS'].'&';
			$url .= 'sInfoCookie='.urlencode($this->cookies['S_INFO']).'&';
			$url .= 'pInfoCookie='.$this->params['username'].'%40126.com%7C1250648082%7C0%7Cmail126%7C11%2626%261250648082539';
			$url .= '&url=http%3A%2F%2Fentry.mail.126.com%2Fcgi%2Fntesdoor%3Fhid%3D10010102%26username%3D'.$this->params['username'].'%40126.com&loginyoudao=0';
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
				));
			$html = curl_exec($ch);
			curl_close($ch);

			$ch = curl_init();
			//http://entry.mail.126.com/cgi/ntesdoor?hid=10010102&lightweight=1&verifycookie=1&language=0&style=3&username=pysche@126.com
			curl_setopt_array($ch, array(
				CURLOPT_URL => 'http://entry.mail.126.com/cgi/ntesdoor?hid=10010102&lightweight=1&verifycookie=1&language=0&style=3&username=' . $this->params['username'] . '@126.com',
				CURLOPT_HEADER => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => 0,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
				CURLOPT_USERAGENT=>$this->agent,
				CURLOPT_REFERER => $url,
			));
			$html = curl_exec($ch);
			curl_close($ch);

			//获取登录后126邮箱的sid标识
			$tidy = new tidy();
			$tidy->parseString($html, array(
				'indent' => true,
				'output-xhtml' => true
				), 'UTF8');
			$tidy->cleanRepair();

			$dom = new DOMDocument();
			$res = $dom->loadHTML($tidy);
			$iframes = $dom->getElementsByTagName('a');
			$src = $iframes->item(0)->getAttribute('href');
			unset($dom);
			unset($iframes);
			$sess = preg_replace('#(.*)sid=(.+)$#is','\2',$src);
			$host = preg_replace('#(.*)http://([a-z0-9]{3,7})\.(.+)#is','\2',$src);

			$exportUrl = 'http://tg1b14.mail.126.com/js3/s?sid='.$sess.'&func=pab%3AexportContacts&outformat=8';
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $exportUrl, 
				CURLOPT_HEADER => 0, 
				CURLOPT_RETURNTRANSFER => 1, 
				CURLOPT_COOKIEFILE => $this->params['cookie_jar']
				));
			$html = curl_exec($ch);
			curl_close($ch);
			
			$html = iconv('GB2312', 'UTF-8', $html);
			//$countmail = count(explode("CI", $html));
			$lines = explode("\n", $html);
			//$endmailkey =count(explode("CI", $html));
			//Zend_Debug::dump($lines);
			for($i=1;$i<count($lines);$i++) {
				$row = explode(',', $lines[$i]);
				if(!Better_Functions::checkEmail($row[2])){
					break;
				}			
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