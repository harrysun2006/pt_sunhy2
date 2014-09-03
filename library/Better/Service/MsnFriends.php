<?php

/**
 * MSN好友搜索
 *
 * @package Better.Service
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Service_MsnFriends
{
	protected $msn = '';
	protected $password = '';
	protected $code = '';
	protected $results = array();
	
	public function __construct($msn, $password)
	{
		$this->msn = $msn;
		$this->password = $password;
	}
	
	public function getResultCode()
	{
		return $this->code;
	}
	
	public function getResults()
	{
		return $this->results;
	}
	
	/**
	 * 查找已经注册了的
	 *
	 * @return array
	 */
	public function search()
	{
		$results = false;
		list($name, $domain) = explode('@', $this->msn);
		$obj = Better_Service_ContactsGrabber::factory($domain, array(
						'username' => $name,
						'password' => $this->password,
						));
		if ($obj->login()) {
			$contacts = $obj->getContacts();
			$emails = array();
			
			if (count($contacts)>0) {
				$userInfo = Better_Registry::get('user')->getUser();
				
				foreach($contacts as $row) {
					if ($row['email']!=$userInfo['email']) {
						$emails[] = $row['email'];
					}
				}
				
				Better_Cache::remote()->set('msn_emails_'.Better_Registry::get('sess')->getUid(), $emails, 60);
				define('BETTER_SEARCH_EMAIL', true);
				
				$this->results = Better_Search::factory(array(
					'what' => 'user',
					'keyword' => $emails,
					'page' => '1',
					'count' => 1000
					))->searchEmail();

				if (is_array($this->results['rows']) && count($this->results['rows'])>0) {
					$this->code = 1;
				} else {
					$this->code = 4;
				}
			} else {
				$this->code = 3;
			}
		} else {
			$this->code = 2;
		}
		
		return $this->code;
	}
	
	/**
	 * 查找还没有注册的
	 *
	 * @return array
	 */
	public function revertSearch()
	{
		$results = false;
		$emails = array();
		list($name, $domain) = explode('@', $this->msn);
		$obj = Better_Service_ContactsGrabber::factory($domain, array(
						'username' => $name,
						'password' => $this->password,
						));
		if ($obj->login()) {
			$contacts = $obj->getContacts();
			
			if (count($contacts)>0) {
				$userInfo = Better_Registry::get('user')->getUser();
				
				foreach($contacts as $row) {
					if ($row['email']!=$userInfo['email']) {
						$emails[$row['email']] = array(
							'email' => $row['email'],
							'name' => '',
							);
					}
				}
				
				$results = Better_Search::factory(array(
					'what' => 'user',
					'keyword' => array_keys($emails),
					'page' => '1',
					'count' => 1000
					))->searchEmail();
			
				foreach($results['rows'] as $user) {
					unset($emails[$user['email']]);
				}

				if (count($emails)>0) {
					$this->results = $emails;
					$this->code = 1;
				} else {
					$this->code = 4;
				}
			} else {
				$this->code = 3;
			}
		} else {
			$this->code = 2;
		}
		
		return $this->code;
	}
}