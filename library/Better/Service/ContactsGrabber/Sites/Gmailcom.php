<?php

/**
 * 获取gmail.com邮箱联系人
 *
 * @package ContactsGrabber
 * @subpackage protocols
 * @author pang <leip@peptalk.cn>
 *
 */

class Better_Service_ContactsGrabber_Sites_Gmailcom extends Better_Service_ContactsGrabber_Abstract
{

	/**
	 *
	 */
	
	function __construct($params=array())
	{
		$this->init($params);
		
		$this->protocol = 'gmail.com';
		$this->params['login_url'] = '';
	
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_AuthSub');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Gdata_Query');
		Zend_Loader::loadClass('Zend_Gdata_Feed');

	}

	/**
	 *
	 * @see abstractContactsGrabber::getContacts()
	 */
	public function getContacts()
	{
		$user = $this->params['username']."@".$this->protocol;
		if ($this->logined) {
			// step 2: grab the contact list
			$feed_url = "http://www.google.com/m8/feeds/contacts/$user/full?alt=json&max-results=250";
			 
			$header = array(
				'Authorization: GoogleLogin auth=' . $this->params['Auth'],
			);
			 
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $feed_url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
			 
			$result = curl_exec($curl);
			curl_close($curl);
			 
			$data = json_decode($result);
			 
			$contacts = array();
			 
			foreach ($data->feed->entry as $entry)
			{
				$contact=array();
				$contact['name'] = $entry->title->{'$t'};
				$contact['email'] = $entry->{'gd$email'}[0]->address;
			 
				$contacts[] = $contact;
			}
			$this->contacts = $contacts;
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
			$user = $this->params['username']."@".$this->protocol;
			$password = $this->params['password'];			
			// step 1: login
			$login_url = "https://www.google.com/accounts/ClientLogin";
			$fields = array(
				'Email' => $user,
				'Passwd' => $password,
				'service' => 'cp', // <== contact list service code
				'source' => 'test-google-contact-grabber',
				'accountType' => 'GOOGLE',
			);
			 
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL,$login_url);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS,$fields);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
			$result = curl_exec($curl);
			 
			$returns = array();
			 
			foreach (explode("\n",$result) as $line)
			{
				$line = trim($line);
				if (!$line) continue;
				list($k,$v) = explode("=",$line,2);
			 
				$returns[$k] = $v;
			}
			 
			curl_close($curl);
			if($returns['SID'] && $returns['Auth']) {
				$this->logined = true;
				$this->params['Auth'] = $returns['Auth'];
			}
			return  $this->logined;
	}

	/**
	 *
	 */
	function __destruct()
	{
		@unlink($this->params['cookie_jar']);
	}
}

?>