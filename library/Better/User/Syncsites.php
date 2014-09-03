<?php

/**
 * 抄送第三方
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Syncsites extends Better_User_Base
{
	protected static $instance = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}		
	
	/**
	 * 获取所有绑定过的协议
	 * 
	 * @return array
	 */
	public function getSites()
	{
		$sites = array();
		
		if (!$this->uid) {
			return $sites;
		}
		
		$rows = Better_DAO_ThirdBinding::getInstance($this->uid)->getAll(array(
						'uid' => $this->uid,
						));
		
		
		foreach($rows as $row) {
			$sites[$row['protocol']] = array(
				'username' => $row['username'],
				'password' => $row['password'],
				'oauth_token' => $row['oauth_token'],
				'oauth_token_secret' => $row['oauth_token_secret'],
				'tid' => $row['tid'],
				'sync_badge'=> $row['sync_badge']
				);
		}
		
		return $sites;		
	}
	
	/**
	 * 新增一个绑定
	 * 
	 * @param $protocol
	 * @param $username
	 * @param $password
	 * @return unknown_type
	 */
	public function add($protocol, $username, $password, $oauth_token='', $oauth_token_secret='', $tid='', $expires_in=0)
	{
		$_data = array(
						'uid' => $this->uid,
						'protocol' => $protocol,
						'username' => $username,
						'password' => $password,
						'dateline' => time(),		
						'oauth_token' => $oauth_token,
						'oauth_token_secret' => $oauth_token_secret,
						'tid' => $tid,
						'expires_in' => $expires_in,
						);
	
		$flag = Better_DAO_ThirdBinding::getInstance($this->uid)->replace($_data);
		
		if ($flag) {
			Better_Hook::factory(array(
				'Badge'
			))->invoke('NewSyncSites', array(
				'protocol' => $protocol,
				'uid' => $this->uid,
			));
		}

		unset($_data['tid']);
		unset($_data['expires_in']);
		Better_DAO_NewBind::getInstance($this->uid)->insert($_data);
		
		//set to bedo
		Better_DAO_Bedo::getInstance()->addThirdBind($this->uid, $protocol == 'qq.com');
		
		return $flag;
	}	
	
	/**
	 * 删除一个绑定
	 * 
	 * @param $protocol
	 * @return unknown_type
	 */
	public function delete($protocol)
	{
		//set to bedo
		$sites = $this->getSites();
		$all = count($sites) <= 1;
		Better_DAO_Bedo::getInstance()->delThirdBind($this->uid, $protocol == 'qq.com', $all);
		
		return Better_DAO_ThirdBinding::getInstance($this->uid)->deleteByCond(array(
						'uid' => $this->uid,
						'protocol' => $protocol,
						));
	}	
	
	/*
	 * 非用户主动的更新绑定，发送的私信提醒
	 */
	public function updatesms($protocol)
	{
		$protocol = $protocol;		
		$appConfig = Better_Config::getAppConfig();
		$sys_user_id = $appConfig->user->sys_user_id;
		$content = Better_Language::load()->global->thirdbind->update_sms;
		$protocols_site = split(",",$appConfig->badge->had_specialsync);
		$protocols_name = split(",",$appConfig->badge->had_syncname);		
		$no = array_search($protocol,$protocols_site);			
		$content = str_replace('{THIRDPARTY}',$protocols_name[$no], $content);
		$msginfo = array(
			'content' => $content,
			'receiver' => $this->uid
		);
		Better_User_Notification_DirectMessage::getInstance($sys_user_id)->send($msginfo);			
	}
}