<?php

/**
 * 获取sina.com邮箱联系人
 * 
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 * 
 */

class Better_Service_ContactsGrabber_Sites_Sohucom extends Better_Service_ContactsGrabber_Abstract
{

	/**
	 * 
	 */
	function __construct($params=array())
	{
		$this->protocol = 'sohu.com';
		
		$this->init($params);
		$this->params['login_url'] = 'http://passport.sohu.com/sso/login.jsp';
	}

	/**
	 * 
	 * @see abstractContactsGrabber::getContacts()
	 */
	public function getContacts()
	{
		if ($this->logined==true) {
			$url = 'http://mail.sohu.com/address/export';
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HEADER => 0,
				));
			$html = curl_exec($ch);
			curl_close($ch);
			
			$lines = explode("\n",mb_convert_encoding($html,'UTF-8','GBK'));

			for ($i=1;$i<count($lines)-1;$i++) {
				$line = trim($lines[$i]);
				if (strlen($line)) {
					$csv = explode(',',$line);
					$name = trim($csv[0]);
					$email = trim($csv[1]);
					if (preg_match(self::EMAIL_PAT,$email)) {
						$this->contacts[] = array(
							'name' => $name,
							'email' => $email,
							);
					}
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
		$request = 'userid='.urlencode($this->params['username'].'@sohu.com').'&password='.md5($this->params['password']);
		$request .= '&persistentcookie=0&s='.time().'&b=2&w=1024&isSLogin=1&pwdtype=1&appid=1000&persistentcookie=0';
		
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $this->params['login_url'],
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $request,
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
			CURLOPT_REFERER => 'http://mail.sohu.com',
			));
		$html = curl_exec($ch);
		curl_close($ch);	
		$this->parseCookie($html);
		if ($this->cookies['passport']!='' || $this->cookies['pprdig']) {
			$this->logined = true;
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
