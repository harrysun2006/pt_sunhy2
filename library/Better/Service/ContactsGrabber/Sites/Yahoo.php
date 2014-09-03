<?php

/**
 * 处理使用Yahoo的几个邮箱的通用类
 *
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 *
 */
class Better_Service_ContactsGrabber_Sites_Yahoo extends Better_Service_ContactsGrabber_Abstract
{

	/**
	 *	登录yahoo邮箱成功后重定向的地址
	 */
	private $logined_url = '';
	
	/**
	 *
	 */
	function __construct($params=array())
	{
		$this->protocol = 'yahoo';
		$this->init($params);
		$this->params['login_url'] = 'https://edit.bjs.yahoo.com/config/login?';
		$this->params['login_page'] = 'http://mail.cn.yahoo.com/logout/logout1s.html';
	}

	/**
	 *
	 * @see abstractContactsGrabber::getContacts()
	 */
	public function getContacts()
	{
		if ($this->logined) {
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $this->logined_url,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_USERAGENT => $this->agent,
				CURLOPT_HEADER => 1,
				));
			$html = curl_exec($ch);
			curl_close($ch);
			$this->parseCookie($html);

			$tidy = new tidy();
			$tidy->parseString($html, array(
				'indent' => true,
				'output-xhtml' => true,
				'wrap' => 200
				), 'UTF8');
			$tidy->cleanRepair();
		
			$next_url = preg_replace('#<script(.+)>(.+)</script>#','\2',$tidy);
			$next_url = preg_replace("#([a-z\.=].+)'(.+)';#is",'\2',$next_url);
			$next_url = trim(preg_replace('#<!--(.+)-->#is','',$next_url));
			$next_url = preg_replace('/\s(?=\s)/','',$next_url);
			$next_url = preg_replace('/[\n\r\t]/','',$next_url);

			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $next_url,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_HEADER => 1,
				CURLOPT_AUTOREFERER => 1,
				CURLOPT_USERAGENT => $this->agent,
				CURLOPT_ENCODING => 'deflate',
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				));
			$html = curl_exec($ch);
			curl_close($ch);
			$this->parseCookie($html);
			
			$url = 'http://address.mail.yahoo.com/?_src=&VPC=tools_export';
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_HEADER => 1,
				));
			$html = curl_exec($ch);
			curl_close($ch);
			$this->parseCookie($html);
			
			$tidy = new tidy();
			$tidy->parseString($html, array(
				'indent' => true,
				'output-xhtml' => true,
				'wrap' => 200
				), 'UTF8');
			$tidy->cleanRepair();
						
			$dom = new DOMDocument();
			@$dom->loadHTML($tidy);
			$crumbs = $dom->getElementsByTagName('input');
			$crumb = '';
			for ($i=0;$i<$crumbs->length;$i++) {
				if ($crumbs->item($i)->getAttribute('id')=='crumb1') {
					$crumb = $crumbs->item($i)->getAttribute('value');
					break;
				}
			}
			unset($dom);
			
			$url = 'http://address.mail.yahoo.com/?_src=&VPC=tools_export';
			$request = '.crumb='.$crumb.'&.src=&VPC=import_export&submit[action_export_outlook]='.urlencode('立即导出');
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $request,
				CURLOPT_HEADER => 0,
				CURLOPT_FOLLOWLOCATION => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_REFERER => $url,
				));
			$html = curl_exec($ch);
			curl_close($ch);

			$rows = explode("\n",$html);

			for ($i=1;$i<count($rows)-1;$i++) {
				$items = explode(',',$rows[$i]);
				$first_name = trim($items[1],'"');
				$last_name = trim($items[3],'"');
				$email = trim($items[55],'"');

				if (strlen($email) && preg_match(self::EMAIL_PAT,$email)) {
					$this->contacts[] = array(
						'name' => $last_name.$first_name,
						'email' => $email,
						);
				}
			}
			
			return $this->outputResult();
			
		} else {
			$this->Error('Please login to '.$this->protocol.' first');
		}
	}

	/**
	 *
	 * @see abstractContactsGrabber::login()
	 */
	public function login()
	{
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $this->params['login_page'],
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
			));
		$html = curl_exec($ch);
		curl_close($ch);
		$this->parseCookie($html);

		$html = mb_convert_encoding($html, 'UTF-8', 'GB2312');
		$tidy = new tidy();
		$tidy->parseString($html, array(
			'indent' => true,
			'output-xhtml' => true,
			'wrap' => 200
			), 'UTF8');
		$tidy->cleanRepair();
					
		$dom = new DOMDocument();
		@$dom->loadHTML($tidy);
		$inputs = $dom->getElementsByTagName('input');
		$form_data = array();
		$request = '';
		for ($i=0;$i<$inputs->length;$i++) {
			$key = $inputs->item($i)->getAttribute('name');
			$value = $inputs->item($i)->getAttribute('value');
			$form_data[$key] = $value;
		}
		unset($dom);

		$request = '.intl='.$form_data['.intl'].'&.done='.$form_data['.done'].'&.src='.$form_data['.src'];
		$request .= '&.cnrid='.$form_data['.cnrid'].'&.challenge='.$form_data['.challenge'];
		$request .= '&login='.$this->params['username'].'@'.$this->protocol.'&passwd='.$this->params['password'];
		$request .= '&submit=&.remember=y';

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $this->params['login_url'],
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $request,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_HEADER => 1,
			CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
			CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
			CURLOPT_ENCODING => 'deflate',
			CURLOPT_REFERER => $this->params['login_page'],
			CURLOPT_SSL_VERIFYPEER => false,
			));
		$html = curl_exec($ch);
		curl_close($ch);
		$this->parseCookie($html);

		if ($this->cookies['F']!='' && $this->cookies['Y']!='' && $this->cookies['PH']!='' && $this->cookies['T']!='') {
			
			$this->logined = true;
			
			$tidy = new tidy();
			$tidy->parseString($html, array(
				'indent' => true,
				'output-xhtml' => true,
				'wrap' => 200
				), 'UTF8');
			$tidy->cleanRepair();
			
			$dom = new DOMDocument();
			$dom->loadHTML($tidy);
			$as = $dom->getElementsByTagName('a');
			$this->logined_url = $as->item(0)->getAttribute('href');

			unset($dom);
		}

		return $this->logined;
	}

	/**
	 *
	 */
	function __destruct()
	{
		@unlink($this->params['cookie_jar']);
	}
}