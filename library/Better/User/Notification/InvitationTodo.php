<?php

/**
 * 私信类通知
 * 
 * @package Better.User.Notification
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_Notification_InvitationTodo extends Better_User_Notification_Base
{
	protected static $instance = array();
	protected $type = 'invitation_todo';

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}	
	
	public function &getReceiveds(array $params=array())
	{
		$userInfo = $this->user->getUserInfo();		
		$results = parent::getReceiveds($params);
		if ($results['count']!=$userInfo['received_msgs']) {
			$this->user->updateUser(array(
				'received_msgs' => $results['count'],
				));
		}
		
		return $results;
	}
//	
//	public function &getSents($page=1, $count=BETTER_PAGE_SIZE, $since=0, $desc=true)
//	{
//		$userInfo = $this->user->getUserInfo();
//		
//		$results = parent::getSents($page, $count, $since, $desc);
//		if ($results['count']!=$userInfo['sent_msgs']) {
//			$this->user->updateUser(array(
//				'sent_msgs' => $results['count'],
//				));
//		}
//		
//		return $results;		
//	}

}