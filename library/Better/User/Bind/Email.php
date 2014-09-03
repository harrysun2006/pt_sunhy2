<?php

/**
 * 绑定Email
 * 
 * @package Better.User.Bind
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Bind_Email extends Better_User_Bind_Base
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
	 * 检测用户是否有绑定请求
	 * 
	 * @param $uid
	 * @param $email
	 * @return integer
	 */
	public static function hasRequest($hash)
	{
		$dao = Better_DAO_BindEmail::getInstance();
		$row = $dao->get(array(
						'hash' => $hash
						));
		
		return $row;		
	}		
	
	public function lastRequestEmail()
	{
		$row = Better_DAO_BindEmail::getInstance()->get(array(
			'uid' => $this->uid,
			'order' => 'dateline DESC'
			));
		return $row['email'] ? $row['email'] : '';
	}
	
	public function bind($email)
	{
		$userInfo = $this->user->getUserInfo();
		$this->user->updateUser(array(
			'enabled' => '1',
			'email' => $email,
			'state' => Better_User_State::ENABLED,
			'emailBind' => 1
		), true);
		
		Better_Registry::get('sess')->set('uid', $this->uid);
		
		Better_DAO_BindEmail::getInstance()->deleteByCond(array(
			'uid' => $this->uid
		));		

		Better_Hook::factory(array(
			'User', 'Badge', 'Secret'
			))->invoke('EmailBinded', array(
				'uid' => $this->uid,
				'before_state' => $userInfo['state']
			));
	}
	
	public function request($email, $hash)
	{
		$ruid = 0;
		$dao = Better_DAO_BindEmail::getInstance();
		$row = $dao->get(array(
						'uid' => $this->uid,
						'email' => $email,
						'hash' => $hash,
						));

		if (isset($row['uid'])) {
			$user = $this->user;
			$info = $user->getUser();
			if (isset($info['uid'])) {
				$ruid = $info['uid'];

				$s = array();
				$s['enabled'] = '1';
				$s['email'] = $row['email'];

				$user->updateUser($s);
				
				Better_Registry::get('sess')->set('uid', $ruid);
				Better_Registry::get('sess')->set('user', $info);

				$dao->deleteUid($ruid);
			}
		}

		return $ruid;
	}	
}