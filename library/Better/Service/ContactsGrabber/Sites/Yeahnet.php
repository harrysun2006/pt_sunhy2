<?php

/**
 * 获取year.net邮箱联系人
 *
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 *
 */

class Better_Service_ContactsGrabber_Sites_Yeahnet extends Better_Service_ContactsGrabber_Abstract
{
	public function __construct($params = array())
	{
		$this->init($params);
		
		$this->protocol = 'yeah.net';
		$this->params['login_url'] = 'http://reg.163.com/login.jsp?type=1&url=http://entry.mail.yeah.net/cgi/ntesdoor?hid%3D10010102&3Dlightweight%3D1%26verifycookie%3D1%26language%3D-1%26style%3D35';
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

		$request = 'verifycookie=1&username=' . $this->params['username'] . '@yeah.net&password=' . $this->params['password'];
		$request .= '&selType=jy&style=-1&product=mail163&remUser=&secure=&url=';
		$request .= 'http://entry.mail.yeah.net/cgi/ntesdoor?hid%3D10010102%26lightweight%3D1%26';
		$request .= '&type=1&savelogin=&outfoxer=&domains=&' . urlencode('登录邮箱') . '=' . urlencode('登录邮箱');

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_POSTFIELDS => $request,
			CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HEADER => 1,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_POST => 1,
			CURLOPT_URL => $this->params['login_url'],
			CURLOPT_USERAGENT=>$this->agent,
			CURLOPT_REFERER => 'http://email.163.com/',
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
			return $this->outputResult();
			//yeah邮箱要先登录passort

			$url = 'http://passport.yeah.net/crossdomain.jsp?username='.$this->params['username'].'@yeah.net&';
			$url .= 'loginCookie='.$this->cookies['NTES_SESS'].'&sInfoCookie='.urlencode($this->cookies['S_INFO']).'&';
			$url .= 'pInfoCookie='.urlencode($this->cookies['P_INFO']).'&';
			$url .= 'url=http%3A%2F%2Fentry.mail.yeah.net%2Fcgi%2Fntesdoor%3Flightweight%3D1%26verifycookie%3D1%26style%3D9%26username%3D'.$this->params['username'].'%40yeah.net&loginyoudao=0';
						
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_HEADER => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
				CURLOPT_USERAGENT => $this->agent,
				CURLOPT_REFERER => 'http://email.163.com/',
				));
			$html = curl_exec($ch);
			$this->parseCookie($html);
			curl_close($ch);

			$url = 'http://passport.yeah.net/setcookie.jsp?username='.$this->params['username'].'@yeah.net&loginCookie='.$this->cookies['NTES_SESS'].'&sInfoCookie='.$this->cookies['S_INFO'].'&domain=yeah.net';
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_HEADER => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
				));
			$html = curl_exec($ch);
			curl_close($ch);

			$url = 'http://entry.mail.yeah.net/cgi/ntesdoor?lightweight=1&verifycookie=1&style=-1&username=pysche@yeah.net';
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => 'http://entry.mail.yeah.net/cgi/ntesdoor?lightweight=1&verifycookie=1&style=9&username='.$this->params['username'].'@yeah.net',
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => 0,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
				CURLOPT_USERAGENT=>$this->agent
			));
			$html = curl_exec($ch);
			$this->parseCookie($html);
			curl_close($ch);

			$tidy = new tidy();
			$tidy->parseString($html, array(
				'indent' => true,
				'output-xhtml' => true,
				'wrap' => 200
				), 'UTF8');
			$tidy->cleanRepair();

			//获取登录后163邮箱的sid标识
			$dom = new DOMDocument();
			$res = $dom->loadHTML($tidy);

			$iframes = $dom->getElementsByTagName('a');
			$src = $iframes->item(0)->getAttribute('href');

			unset($dom);
			unset($iframes);
			$sess = preg_replace('#(.*)sid=(.+)$#is','\2',$src);
			$host = preg_replace('#(.*)http://([a-z0-9]{3,7})\.(.+)#is','\2',$src);

			$contacts_url = 'http://'.$host.'.mail.yeah.net/jy3/address/addrprint.jsp?sid=' . $sess;
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $contacts_url,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_USERAGENT=>$this->agent,
				CURLOPT_REFERER=>'http://entry.mail.yeah.net/cgi/ntesdoor?hid=10010102&style=9&username='.$this->params['username'].'@yeah.net'
			));
			$html = curl_exec($ch);
			curl_close($ch);

			$html = mb_convert_encoding($html, 'UTF-8', 'GBK');
			$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
			$tidy = new tidy();
			$tidy->parseString($html, array(
				'indent' => true,
				'output-xhtml' => true,
				'wrap' => 200
				));
			$tidy->cleanRepair();

			$dom = new DOMDocument();
			$dom->loadHTML($tidy);
			$this->contacts = array();

			//分析联系人姓名
			$items = $dom->getElementsByTagName('b');
			$j = 0;
			for ($i = 0; $i < $items->length; $i ++) {
				if ($items->item($i)->getAttribute('class') == 'mTT') {
					$this->contacts[$j ++]['name'] = trim($items->item($i)->nodeValue);
				}
			}

			//分析具体的邮件地址
			/**
			 * TODO: 还有更好的方法没？
			 */
			$tbls = $dom->getElementsByTagName('td');
			$j = 0;
			for ($i = 0; $i < $tbls->length; $i ++) {
				if ($tbls->item($i)->hasAttributes == false) {
					$value = trim($tbls->item($i)->nodeValue);
					if (preg_match(self::EMAIL_PAT, $value)) {
						$this->contacts[$j ++]['email'] = $value;
						$i = $i + 3;
					}
				}
			}
			unset($dom);
			
			return $this->outputResult();
		} else {
			$this->Error('Please login to '.$this->protocol.' first');
		}
		
		return false;
	}
}