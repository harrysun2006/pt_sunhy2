<?php

/**
 * 站内私信
 *
 * @package Better.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_User_DirectMessage extends Better_User_Base
{
	protected static $instance = array();

	public static function getInstance($uid)
	{
		if (!isset(self::$instance[$uid])) {
			self::$instance[$uid] = new self($uid);
		}
		
		return self::$instance[$uid];
	}

	public function __call($method, $params)
	{
		$className = 'Better_User_Notification_'.ucfirst($method);

		if (class_exists($className)) {
			return call_user_func($className.'::getInstance', $this->uid);
		} else {
			return null;
		}
	}
			
	
	/**
	 * 标记为已读
	 * 
	 * @param $msg_id
	 * @return bool
	 */
	public function readed($msg_id)
	{
		return $this->user->notification()->DirectMessage()->readed($msg_id);
	}

	/**
	 *	删除一个收到的私信
	 *
	 * @param integer $msg_id
	 *
	 */
	public function delReceived($msg_id)
	{
		return $this->user->notification()->DirectMessage()->delReceived($msg_id);
	}
	
	/**
	 *	删除一个发出的私信
	 *
	 * @param integer $msg_id
	 *
	 */
	public function delSent($msg_id)
	{
		return $this->user->notification()->DirectMessage()->delSent($msg_id);
	}

	/**
	 * 获取一个私信
	 *
	 * @param integer $msg_id
	 * @return array
	 */
	public function getReceived($msg_id)
	{
		return $this->user->notification()->DirectMessage()->getReceived($msg_id);
	}
	
	/**
	 * 获取一个发送过的私信
	 *
	 * @param integer $msg_id
	 * @return array
	 */
	public function getSent($msg_id)
	{
		return $this->user->notification()->DirectMessage()->getSent($msg_id);
	}
		
	/**
	 *  使用消息模板发送私信
	 *
	 * @param array $data
	 * @param array $receiverUserInfo
	 * @return integer
	 */
	public function sendTpl($tpl, $data, $userInfo)
	{
		return $this->user->notification()->DirectMessage()->sendTpl($tpl, $data, $userInfo);
	}
	
	/**
	 * 发送一个站内信
	 *
	 * @param array $receiverUserInfo
	 * @param string $content
	 * @return integer
	 */
	public function send($content, $uid, $strip_tags=true)
	{
		$params = array(
			'content'=> $content,
			'receiver'=> $uid
		);
		return $this->user->notification()->DirectMessage()->send($params);
	}

	/**
	 * 获取所有我发送的消息
	 *
	 * @param integer $page
	 * @param integer $count
	 * @return array
	 */
	public function getSents($page=1, $count=20, $since=0, $desc=true)
	{
		return $this->user->notification()->DirectMessage()->getSent($page, $count, $since, $desc);
	}

	/**
	 * 获取所有我收到的消息
	 *
	 * @param integer $page
	 * @param integer $count
	 * @return array
	 */
	public function getReceiveds(array $params)
	{
		return $this->user->notification()->DirectMessage()->getReceiveds($params);
	}

}