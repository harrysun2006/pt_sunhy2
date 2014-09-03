<?php

/**
 * 获取sina.com邮箱联系人
 *
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 *
 */

class Better_Service_ContactsGrabber_Sites_Sinacom extends Better_Service_ContactsGrabber_Abstract
{

	protected $sid = '';
	
	/**
	 *
	 */
	function __construct($params=array())
	{
		$this->protocol = 'sina.com';
		$this->init($params);
		$this->params['login_url'] = 'http://mail.sina.com.cn/cgi-bin/login.cgi';
	}
	
	/**
	 *
	 *
	 */
	function __destruct()
	{
		@unlink($this->params['cookie_jar']);
	}

	/**
	 *
	 * @see abstractContactsGrabber::getContacts()
	 */
	public function getContacts()
	{
		if ($this->logined) {			
			$url = $this->sid.'.sinamail.sina.com.cn/classic/addr_member.php';			
			$request = 'act=list&sort_item=letter&sort_type=desc';
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $request,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HEADER => 0,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_REFERER => 'http://mail2-234.sinamail.sina.com.cn',
				));
			$json = curl_exec($ch);
			curl_close($ch);
			
			$rows = Zend_Json::decode($json);

			if (isset($rows['result']) && $rows['result']!=false && isset($rows['data']['contact'])) {
				foreach($rows['data']['contact'] as $row) {
					if (preg_match(self::EMAIL_PAT,$row['email'])) {
						$this->contacts[] = array(
							'name' => $row['name'],
							'email' => $row['email'],
							);
					}
				}
			}
			//Zend_Debug::dump($rows['data']['contact']);
			return $this->outputResult();
		} else {
			$this->Error('Please login to '.$this->protocol.' first');
		}
	}

	/**
	 *logintype	uid
	 * @see abstractContactsGrabber::login()
	 */
	public function login()
	{
		$request = 'u='.$this->params['username'].'&psw='.$this->params['password'];
		$request .= '&logintype=uid&product=mail&%B5%C7%C2%BC=%B5%C7+%C2%BC';
		
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $this->params['login_url'],
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $request,
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
			));
		$html = curl_exec($ch);
		//Zend_Debug::dump(iconv('utf-8','gb2312',$html));
		curl_close($ch);
		$this->parseCookie($html);
		//Zend_Debug::dump($this->cookies['SU']);
		if ($this->cookies['SUE']!='') {
			$this->logined = true;
			$pat = '#http://mail([0-9]{1,5})-([0-9]{1,5})#';
			preg_match($pat, $html, $all);
			$this->sid = $all[0] ? $all[0] : '2-234';
		}

		return $this->logined;
	}
}
