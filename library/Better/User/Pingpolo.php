<?php

/**
 * 用户间相互ping
 * 
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Pingpolo extends Better_User_Base
{
	protected static $instance = array();
	protected $pings = array();
	protected $pingers = array();
	protected $settings = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
	/**
	 * 获取全局设置
	 * 
	 * @return array
	 */
	public function getGlobalSettings()
	{
		if (count($this->settings)==0) {
			$this->settings = Better_DAO_User_ApnSettings::getInstance($this->uid)->get($this->uid);	
			
			if (!$this->settings) {
				$this->settings['game'] = 1;
				$this->settings['direct_message'] = 1;
				$this->settings['request'] = 1;
				$this->settings['friends_shout'] = 1;
				$this->settings['friends_checkin'] = 1;
				
				Better_DAO_User_ApnSettings::getInstance($this->uid)->insert(array(
					'uid' => $this->uid,
					'direct_message' => 1,
					'request' => 1,
					'game' => 1,
					'friends_shout' => 1,
					'friends_checkin' => 1
					));
			}
		} 
		
		return $this->settings;
	}
	
	/**
	 * 更新全局设置
	 * 
	 * @param array $params
	 * @return bool
	 */
	public function updateGlobalSettings(array $params)
	{
		return Better_DAO_User_ApnSettings::getInstance($this->uid)->updateByCond($params, array(
			'uid' => $this->uid
			));
	}
	
	/**
	 * 所有Ping On的人
	 * 
	 * @return array
	 */
	public function pingOns()
	{
		if (count($this->pings)==0) {
			
			$rows = Better_DAO_User_Ping::getInstance($this->uid)->getAll(array(
				'uid' => $this->uid
				));
			foreach ($rows as $row) {
				$this->pings[] = $row['ping_uid'];
			}
		}
		
		return $this->pings;
	}
	
	/**
	 * 获取所有Ping我的人
	 * 
	 * @return array
	 */
	public function pingers()
	{
		if (count($this->pingers)==0) {
			$rows = Better_DAO_User_Pinger::getInstance($this->uid)->getAll(array(
				'uid' => $this->uid
				));
			foreach ($rows as $row) {
				$this->pingers[] = $row['pinger_uid'];
			}
		}
		
		return $this->pingers;
	}
	
	/**
	 * 将内容加入队列
	 * 
	 * @return
	 */
	public function addQueue(array $params, $type='')
	{
		$content = $params['content'];
		if (APPLICATION_ENV!='production') {
			$content = '['.APPLICATION_ENV.']'.$content;
		}
		
		if ($type=='' && isset($params['type'])) {
			$type = $params['type'];
		}
						
		$this->pingers();		
		$tokens = Better_Phone_Apple::getTokens($this->pingers, $type, true);
		$pingeds = array();

		if (count($tokens)>0) {
			$minHour = (int)Better_Config::getAppConfig()->ping_min_hour;
			$maxHour = (int)Better_Config::getAppConfig()->ping_max_hour;
			
			foreach ($tokens as $row) {
				if (!in_array($row['token'], $pingeds) && $row['uid']) {
					$receiver = Better_User::getInstance($row['uid']);
					$receiverUserInfo = $receiver->getUser();
					
					$count = (int)$receiver->cache()->get('apns_polo_count');
					$receiver->cache()->set('apns_polo_count', $count+1);
					
					$hour = date('H', time()+(3600*(int)$receiverUserInfo['timezone']));
					
					if ($hour>=$minHour && $hour<=$maxHour) {
						Better_DAO_PingQueuepolo::getInstance()->insert(array(
							'queue_time' => time(),
							'token' => $row['token'],
							'timezone' => $row['timezone'],
							'content' => $content,
							'uid' => $row['uid']
							));
					}
					$pingeds[] = $row['token'];
				}
			}
		}
	}
	
	/**
	 * 给特定的人发ping
	 * 
	 * @return
	 */
	public function addQueueForSomebody($receiver, $content, $type='')
	{
		$this->pingers();
		
		$flag = false;
		if ($type=='request' || $type=='game') {
			$flag = true;
		} else {
			$flag = in_array($receiver, $this->pingers);
		}
		
		if ($type && $flag) {
			$receiverUser = Better_User::getInstance($receiver);
			$pingSettings = $receiverUser->ping()->getGlobalSettings();
			
			if (isset($pingSettings[$type]) && $pingSettings[$type]) {
				$tokens = Better_Phone_Apple::getTokens((array)$receiver, '', true);
				
				if (count($tokens)>0) {
					$count = (int)$receiverUser->cache()->get('apns_polo_count');
					$receiverUser->cache()->set('apns_polo_count', $count+1);
					$receiverUserInfo = $receiverUser->getUser();
					
					$minHour = (int)Better_Config::getAppConfig()->ping_min_hour;
					$maxHour = (int)Better_Config::getAppConfig()->ping_max_hour;
					$hour = date('H', time()+(3600*(int)$receiverUserInfo['timezone']));
					
					if ($hour>=$minHour && $hour<=$maxHour) {
						foreach ($tokens as $row) {
							Better_DAO_PingQueuepolo::getInstance()->insert(array(
								'queue_time' => time(),
								'token' => $row['token'],
								'timezone' => $row['timezone'],
								'content' => $content,
								'uid' => $receiver
								));
						}
					}
				}
			}
		}
	}
	
	/**
	 * ping on某人
	 * 
	 * @param integer $uid
	 * @return bool
	 */
	public function pingOn($uid)
	{
		$flag = false;
		
		Better_DAO_User_Ping::getInstance($this->uid)->deleteByCond(array(
			'uid' => $this->uid,
			'ping_uid' => $uid,
			));
		Better_DAO_User_Ping::getInstance($this->uid)->insert(array(
			'uid' => $this->uid,
			'ping_uid' => $uid,
			'dateline' => time(),
			));
			
		Better_DAO_User_Pinger::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'pinger_uid' => $this->uid,
			));
		Better_DAO_User_Pinger::getInstance($uid)->insert(array(
			'uid' => $uid,
			'pinger_uid' => $this->uid,
			'dateline' => time(),
			));
		
		$flag = true;
		
		return $flag;
	}
	
	/**
	 * 取消ping某人
	 * 
	 * @param integer $uid
	 * @return bool
	 */
	public function pingOff($uid)
	{
		$flag = false;
		
		Better_DAO_User_Ping::getInstance($this->uid)->deleteByCond(array(
			'uid' => $this->uid,
			'ping_uid' => $uid,
			));
		Better_DAO_User_Pinger::getInstance($uid)->deleteByCond(array(
			'uid' => $uid,
			'pinger_uid' => $this->uid,
			));
		
		$flag = true;
		return $flag;
	}
	
	
	
}