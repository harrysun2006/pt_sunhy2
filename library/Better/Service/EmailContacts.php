<?php

/**
 * Email联系提取
 *
 * @package Better.Service
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Service_EmailContacts
{
	protected $username = '';
	protected $password = '';
	protected $domain = '';
	protected $results = array();
	
	/**
	 * 0 : 未知错误
	 * 1 : 密码正确，且找到了有效的结果集
	 * 2 : 密码不正确
	 * 3 : 联系人为空
	 * 4 : 联系人不为空，但是没有找到有效的结果集
	 *
	 * @var integer
	 */
	protected $code = 0;
	
	function __construct($username='', $password='', $domain='')
	{
		$this->username = $username;
		$this->password = $password;
		$this->domain = $domain;
	}
	
	/**
	 * 获取结果代码
	 *
	 * @return string
	 */
	public function getResultCode()
	{
		return $this->code;
	}
	
	/**
	 * 获取结果
	 *
	 * @return array
	 */
	public function getResults()
	{
		return $this->results;
	}
	
	/**
	 * 搜索email联系人中已经注册了的用户
	 *
	 * @return array
	 */
	public function search()
	{
		$results = false;
		$obj = Better_Service_ContactsGrabber::factory($this->domain, array(
						'username' => $this->username,
						'password' => $this->password,
						));
		if ($obj->login()) {
			$contacts = $obj->getContacts();
			$emails = array();
			if (count($contacts)>0) {
				$userInfo = Better_Registry::get('user')->getUser();
				
				foreach($contacts as $row) {
					if (strtolower($row['email'])!=strtolower($userInfo['email'])) {
						$emails[] = $row['email'];
					}
				}
				
				Better_Cache::remote()->set('email_contacts_'.Better_Registry::get('sess')->getUid(), $emails, 60);
				Better_Cache::remote()->set('email_contacts_with_name'.Better_Registry::get('sess')->getUid(),$contacts,60);
				define('BETTER_SEARCH_EMAIL', true);
	
				if (count($emails)>0) {
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
	 * 搜索Email联系人中没有注册的用户
	 *
	 * @return array
	 */
	public function revertSearch()
	{
		$results = false;
		$obj = Better_Service_ContactsGrabber::factory($this->domain, array(
						'username' => $this->username,
						'password' => $this->password,
						));
		$emails = array();
		
		if ($obj->login()) {
			$contacts = $obj->getContacts();
			
			if (count($contacts)>0) {
				$userInfo = Better_Registry::get('user')->getUser();
	
				foreach($contacts as $row) {
					if (strtolower($row['email'])!=strtolower($userInfo['email'])) {
						$emails[$row['email']] = array(
							'email' => $row['email'],
							'name' => $row['name'],
							);
					}
				}
	
				!defined('BETTER_SEARCH_EMAIL') && define('BETTER_SEARCH_EMAIL', true);
				if (count($emails)>0) {
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
	 * 搜索email LIST联系人中已经注册了的用户
	 *
	 * @return array
	 */
	public function searchMaillist($mailliststr,$nameliststr)
	{
		$results = false;
		
		$maillist = explode("{*}",$mailliststr);
		$namelist = explode("{*}",$nameliststr);
		$contacts = array();
		for($i=0;$i<count($maillist);$i++)
		{
			$contacts[$i]['name']=$namelist[$i];
			$contacts[$i]['email']=$maillist[$i];			
		}
		$emails = array();
		if (count($contacts)>0) {
			$userInfo = Better_Registry::get('user')->getUser();
			
			foreach($contacts as $row) {
				if (strtolower($row['email'])!=strtolower($userInfo['email'])) {
					$emails[] = $row['email'];
				}
			}
			
			Better_Cache::remote()->set('email_contacts_'.Better_Registry::get('sess')->getUid(), $emails, 60);
			define('BETTER_SEARCH_EMAIL', true);

			if (count($emails)>0) {
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
				$this->code = 4;
			}
		} else {
			$this->code = 3;
		}
		return $this->code;
	}
	
	/**
	 * 搜索Email LIST联系人中没有注册的用户
	 *
	 * @return array
	 */
	public function revertSearchMaillist($mailliststr,$nameliststr)
	{
		$results = false;
		$contacts = array();
		$emails = array();
		$maillist = explode("{*}",$mailliststr);
		$namelist = explode("{*}",$nameliststr);		
		for($i=0;$i<count($maillist);$i++)
		{
			$contacts[$i]['name']=$namelist[$i];
			$contacts[$i]['email']=$maillist[$i];			
		}			
		if (count($contacts)>0) {
			$userInfo = Better_Registry::get('user')->getUser();

			foreach($contacts as $row) {
				if (strtolower($row['email'])!=strtolower($userInfo['email'])) {
					$emails[$row['email']] = array(
						'email' => $row['email'],
						'name' => $row['name'],
						);
				}
			}

			!defined('BETTER_SEARCH_EMAIL') && define('BETTER_SEARCH_EMAIL', true);
			if (count($emails)>0) {
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
				$this->code = 4;
			}
		} else {
			$this->code = 3;
		}

		

		return $this->code;
	}
}