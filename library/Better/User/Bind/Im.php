<?php

/**
 * 绑定即时通讯软件
 * 
 * @package Better.User.Bind
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Bind_Im extends Better_User_Bind_Base
{
	protected static $instance = array();
	public static $allowedProtocols = array('msn', 'gtalk');

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
	 * @param $im
	 * @return integer
	 */
	public static function hasRequest($im)
	{
		$uid = 0;
		
		$dao = Better_DAO_BindIm::getInstance();
		$row = $dao->get(array(
						'im' => $im,
						'binded' => '0',
						));
		if (isset($row['uid'])) {
			$uid = $row['uid'];
		}
		
		return $uid;		
	}	
	
	/**
	 * 请求绑定Im帐号
	 * 
	 * @param $protocol
	 * @param $im
	 * @return unknown_type
	 */
	public function request($protocol, $im)
	{
		$return = 0;

		if (in_array($protocol, self::$allowedProtocols)) {
			
			if ($protocol=='msn') {
				$userInfo = Better_User::getInstance()->getUserByMsn($im);
			}

			if (!isset($userInfo['username']) || $userInfo['uid']==$this->uid) {

				$r = Better_DAO_MsnRobots::getInstance()->rand(60*5);
				$robot = $r['robot'];
								
				if ($robot) {
					$dao = Better_DAO_BindIm::getInstance();
					$dao->deleteByCond(array(
						'uid' => $this->uid,
						'protocol' => $protocol,
						'binded' => '0',
						));
					$flag = $dao->insert(array(
									'uid' => $this->uid,
									'protocol' => $protocol,
									'im' => $im,
									'dateline' => time(),
									'bot' => $robot,
									'binded' => '0',
									));
					if ($flag) {
						$return = $robot;
					}
				} else {
					$return = 'ROBOT_UNAVAILABLE';
					Better_Log::getInstance()->logAlert('MSN Robots Not Available', 'robots');
				}
				
			} else {
				$return = 'HAS_BINDED';
			}

		} else {
			$return = 'PROTOCOL_NOT_ALLOWED';
		}

		return $return;
	}	
	
	/**
	 * 解除绑定
	 * 
	 * @param unknown_type $im
	 */
	public function unbind($im, $protocol)
	{
		$result = false;
		$dao = Better_DAO_BindIm::getInstance();
		$row = $dao->get(array(
			'im' => $im,
			'uid' => $this->uid,
			'protocol' => $protocol,
			'binded' => '1',
			));
			
		if (isset($row['uid'])) {
			$dao->deleteByCond(array(
				'uid' => $row['uid'],
				'protocol' => $row['protocol'],
				'binded' => '1',
				));
				
			$this->user->updateUser(array(
				$row['protocol'] => '',
				));
			
			$result = true;
		}
		
		return $result;
	}
	
	/**
	 * 执行绑定
	 * 
	 * @param $im
	 * @return unknown_type
	 */
	public function bind($im)
	{
		$user = null;
		$dao = Better_DAO_BindIm::getInstance();
		$row = $dao->get(array(
						'im' => $im,
						'uid' => $this->uid,
						'binded' => '0',
						));
		if (isset($row['uid'])) {
			$user = $this->user;
			
			//	先删除旧绑定
			Better_DAO_BindIm::getInstance()->deleteByCond(array(
				'uid' => $row['uid'],
				'protocol' => $row['protocol'],
				'binded' => '1',
				));
				
			//	再更新新绑定信息
			Better_DAO_BindIm::getInstance()->update(array(
				'binded' => '1',
			), array(
				'uid' => $row['uid'],
				'protocol' => $row['protocol'],
				'im' => $row['im'],
			));
			
			$this->user->updateUser(array(
				$row['protocol'] => $row['im'],
				));
				
			Better_User::getInstance(BETTER_SYS_UID)->notification()->directMessage()->send(array(
				'receiver' => $this->uid,
				'content' => str_replace('{MSN}', $cell, Better_Language::load()->global->msn_binded),
				));				
		}
		
		return $user;		
	}
	
	/**
	 * 获取绑定的im机器人
	 * 
	 * @param $protocol
	 * @return array
	 */
	public function getBindedIm($protocol)
	{
		$data = Better_DAO_BindIm::getInstance()->get(array(
			'uid' => $this->uid,
			'protocol' => $protocol,
			'binded' => '1',
			));
		
		return isset($data['bot']) ? $data['bot'] : '';
	}
	
	
}