<?php

/**
 * 获取tom.com邮箱联系人
 *
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 *
 */

class Better_Service_ContactsGrabber_Sites_Tomcom extends Better_Service_ContactsGrabber_Abstract
{

	/**
	 * 从tom取得的session id
	 *
	 * @var string
	 */
	private $sid = '';
	
	/**
	 *
	 */
	function __construct($params=array())
	{
		$this->protocol = 'tom.com';
		$this->init($params);

		$this->params['login_url'] = 'http://login.mail.tom.com/cgi/login';
	}

	/**
	 *
	 */
	function __destruct()
	{
		@unlink($this->params['cookie_jar']);
	}
	

	/**
	 *
	 * @see abstractContactsGrabber::login()
	 */
	public function login()
	{
		$request = 'user='.$this->params['username'].'&pass='.$this->params['password'];
		$request .= '&style=21&type=0&verifycookie=y';
		
		$ch = curl_init($ch);
		curl_setopt_array($ch, array(
			CURLOPT_URL => $this->params['login_url'],
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $request,
			CURLOPT_COOKIEJAR => $this->params['cookie_jar'],
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HEADER => 1,
			CURLOPT_FOLLOWLOCATION => 1,
			));
		$html = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		$this->parseCookie($html);

		if (!preg_match('#AlarmPageWeb#is',$html)) {
			$this->logined = true;
			
			$url = $info['url'];
			$this->sid = preg_replace('#(.*)sid=(.+)$#is','\2',$url);
		}

		return $this->logined;
	}


	/**
	 *
	 * @see abstractContactsGrabber::getContacts()
	 */
	public function getContacts()
	{

		if ($this->logined==true) {
			$url = 'http://bjapp6.mail.tom.com/cgi/ldvcapp?funcid=xportadd&sid='.$this->sid.'&ifirstv=&showlist=all';
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HEADER => 0,
				));
			$info = curl_getinfo($ch);
			$html = curl_exec($ch);
			curl_close($ch);

			$postid = '';
			$html = mb_convert_encoding($html, 'UTF-8', 'GBK');
			$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
			$dom = new DOMDocument();
			$dom->loadHTML($html);
			$ips = $dom->getElementsByTagName('input');
			for ($i=0;$i<$ips->length;$i++) {
				if ($ips->item($i)->getAttribute('name')=='postid') {
					$postid = $ips->item($i)->getAttribute('value');
					break;
				}
			}
			unset($ips);
			unset($dom);

			$url = 'http://bjapp6.mail.tom.com/cgi/ldvcapp';
			$request = 'sid='.$this->sid.'&funcid=xportadd&ifirstv=&group=&oFormAction2='.urlencode('$');
			$request .= '&outformat=8&postid='.$postid.'&outport.x=1';

			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_COOKIEFILE => $this->params['cookie_jar'],
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HEADER => 0,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $request,
				));
			$html = curl_exec($ch);
			curl_close($ch);

			$lines = explode("\n",mb_convert_encoding($html,'UTF-8','GBK'));

			for ($i=1;$i<count($lines)-1;$i++) {
				$line = trim($lines[$i]);

				if (strlen($line)) {
					$csv = explode(',',$line);
					$name = trim($csv[6],'"');
					$email = trim($csv[3],'"');
					$name = trim($name, ';');
					$email = trim($email, ';');

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
}