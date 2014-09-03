<?php

/**
 * SNS联系提取
 *
 * @package Better.Service
 * @author fengj <fengj@peptalk.cn>
 *
 */
class Better_Service_SnsContacts
{
	protected $username = '';
	protected $password = '';
	protected $protocol = '';
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
	
	function __construct($username='', $password='', $protocol='', $oauth_token='', $oauth_token_secret='')
	{
		$this->username = $username;
		$this->password = $password;
		$this->protocol = $protocol;
		$this->oauth_token = $oauth_token;
		$this->oauth_token_secret = $oauth_token_secret;
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
	 * 搜索中已经注册了的用户
	 *
	 * @return array
	 */
	public function search()
	{
		$results = false;
		$begin_time = microtime(true);
		$service = Better_Service_PushToOtherSites::factory($this->protocol, $this->username, $this->password, $this->oauth_token, $this->oauth_token_secret);
		$is_msn = false;
		if ($this->protocol == 'msn.com') {
			$is_msn = true;
			$msn_contacts = $service->getFriends();
			
			$_login = $msn_contacts === false ? false : true;
			if ($_login) {
				error_log('msnLogin:' . 'ok');
			} else {
				error_log('msnLogin:' . 'fail');
			}
		} elseif ('sina.com' == $this->protocol && $this->oauth_token ) {
			$_login = $service->verify_credentials();
		} else {
			$_login = $service->fakeLogin();
		}
		
		if ( $_login ) {
			$contacts = $is_msn ? $msn_contacts : $service->getFriends();	
			$end_time = microtime(true);			
			Better_Registry::set('contacts', $contacts);		
			$emails = array();
			if (count($contacts)>0) {
				$userInfo = Better_Registry::get('user')->getUser();
				$users = array();
				if ($is_msn) {
					$exec_time = $end_time - $begin_time;
					error_log('msnTime:' . $exec_time);	
				}
				foreach($contacts as $v) {
					if ($is_msn) {
						error_log('getmsn:' . $v['email'] . ',' . $v['nickname']);
						$tid = $v['email'];
						if (!$tid) continue;
					} else {
						$tid = $v;
					}
					$user_info = Better_DAO_ThirdBinding::getBindUser($this->protocol, $tid);
					if ($user_info['uid'] && $user_info['uid'] != Better_Config::getAppConfig()->user->sys_user_id) {
						$user_info = Better_User::getInstance($user_info['uid'])->getUserInfo();
						if ($this->protocol == 'msn.com') {
							$user_info['msn_nickname'] = $v['nickname'];
						}
						$emails[] = $user_info['email'];
						
						$users[] = $user_info;
					}
					
				}	
				$this->results['rows'] = $users;
				Better_Cache::remote()->set('SNS_contacts_'.Better_Registry::get('sess')->getUid(), $emails, 60);
				define('BETTER_SEARCH_SNS', true);	
				
				if (count($users)>0) {
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
	
}