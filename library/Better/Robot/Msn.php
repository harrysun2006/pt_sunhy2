<?php

/**
 * Better的MSN机器人
 * 
 * @package Better.Robot
 * @author leip <leip@peptalk.cn>
 * 
 */

class Better_Robot_Msn extends Better_Robot_Base
{

	public function __construct($uid, $robot)
	{
		parent::__construct($uid, $robot);
		$this->protocol = 'msn';
	}
	
	/**
	 * 获取某个msn机器人所有要发的通知
	 * 
	 * @param $robot
	 * @return array
	 */
	public static function getMyNotifies($robot)
	{
		$result = array();

		$tmp = Better_DAO_BindIm::getInstance()->getAll(array(
			'bot' => $robot,
			'binded' => 1,
			'protocol' => 'msn',
			));

		if (count($tmp)) {
			foreach($tmp as $row) {
				$uid = $row['uid'];
				$msn = $row['im'];
				$ids = array();

				$notify = Better_User::getInstance($uid)->notification()->all()->getReceiveds(array(
					'type' => array('friend_request', 'follow_request'),
					'im_delived' => 0,
					'page' => 1,
					'count' => 5,
					));
				foreach ($notify['rows'] as $row) {
					$row['im'] = $msn;
					$result[] = $row;
					$ids[] = $row['msg_id'];
				}
				
				if (count($ids)>0) {
					Better_DAO_DmessageReceive::getInstance($uid)->updateByCond(array(
						'im_delived' => 1,
					), array(
						'uid' => $uid,
						'im_delived' => 0,
						'msg_id' => $ids
					));
				}
			}
		}

		return $result;
	}
	
	/**
	 * 显示帮助
	 * 
	 * @see library/Better/Robot/Better_Robot_Base#commandH()
	 */
	protected function commandH()
	{
		/*
		 * 关闭 所有消息提醒 g
开启 所有消息提醒 k

关闭 某个用户的开开提醒 g 用户名
开启 某个用户的开开提醒 k 用户名  
(例：开启张三的开开提醒  k 张三)
		 */
		$this->msg = $this->lang->robot->command->help;
		
		return true;
	}
	
	protected function commandHelp()
	{
		return $this->commandH();
	}

	/**
	 * 执行开启指令
	 * 
	 * @param $params
	 * @return bool
	 */
	protected function commandK(array $params)
	{
		$result = false;

		Better_User::getInstance($this->uid)->updateUser(array(
			'receive_msn_notify' => '1',
			));
		$this->msg = $this->lang->robot->command->k->success;

		if (isset($params['username']) && $params['username']) {
			$kuser = Better_User::getInstance($params['username'], 'nickname');
			$kuserInfo = $kuser->getUser();

			if ($kuserInfo['uid']) {
				Better_DAO_MsnNotifyOff::getInstance($this->uid)->deleteByCond(array(
					'uid' => $this->uid,
					'offuid' => $kuserInfo['uid'],
					));
				Better_DAO_MsnNotifyOn::getInstance($this->uid)->deleteByCond(array(
					'uid' => $this->uid,
					'onuid' => $kuserInfo['uid'],
					));
					
				Better_DAO_MsnNotifyOn::getInstance($this->uid)->insert(array(
					'uid' => $this->uid,
					'onuid' => $kuserInfo['uid'],
					'dateline' => time(),
					));
					
				$this->msg = str_replace('{NICKNAME}', $kuserInfo['nickname'], $this->lang->robot->command->k->somebody->success);
					
				$result = true;
			} else {
				$this->msg = $this->lang->robot->command->k->somebody->failed;
				$this->error = self::COMMAND_PARAM_NOT_VALID;
			}
		} else {
			Better_DAO_MsnNotifyOff::getInstance($this->uid)->deleteByCond(array(
				'uid' => $this->uid,
				));
				
			Better_DAO_MsnNotifyOn::getInstance($this->uid)->deleteByCond(array(
				'uid' => $this->uid
				));
			
			$result = true;
		}
		
		return $result;
	}
	
	/**
	 * 执行开启指令(同commandK)
	 * @param $params
	 * @return bool
	 */
	protected function commandOn(array $params)
	{
		return $this->commandK($params);
	}
	
	protected function commandG(array $params)
	{
		$result = false;

		if (isset($params['username']) && $params['username']) {
			$guser = Better_User::getInstance($params['username'], 'nickname');
			$guserInfo = $guser->getUser();
			
			if ($guserInfo['uid']) {
				Better_DAO_MsnNotifyOn::getInstance($this->uid)->deleteByCond(array(
					'uid' => $this->uid,
					'onuid' => $guserInfo['uid'],
					));
				Better_DAO_MsnNotifyOff::getInstance($this->uid)->deleteByCond(array(
					'uid' => $this->uid,
					'offuid' => $guserInfo['uid'],
					));
				Better_DAO_MsnNotifyOff::getInstance($this->uid)->insert(array(
					'uid' => $this->uid,
					'offuid' => $guserInfo['uid'],
					'dateline' => time(),
					));
				
				$this->msg = str_replace('{NICKNAME}', $guserInfo['nickname'], $this->lang->robot->command->g->somebody->success);
					
				$result = true;
			} else {
				$this->msg = $this->lang->robot->command->g->somebody->failed;
				$this->error = self::COMMAND_PARAM_NOT_VALID;
			}
		} else {
			Better_DAO_MsnNotifyOn::getInstance($this->uid)->deleteByCond(array(
				'uid' => $this->uid,
				));
			
			Better_User::getInstance($this->uid)->updateUser(array(
				'receive_msn_notify' => 0,
				));
			$this->msg = $this->lang->robot->command->g->success;
				
			$result = true;
		}
		
		return $result;
	}
	
	protected function commandOff(array $params)
	{
		return $this->commandG($params);
	}
	
	protected function commandD(array $params)
	{
		return $this->commandS($params);
	}
	
	protected function commandS(array $params)
	{
		$result = false;
		$username = isset($params['username']) ? $params['username'] : '';
		$text = isset($params['content']) ? $params['content'] : '';
		$this->msg = $this->lang->robot->command->s->failed;

		if ($username && $text) {
			$suser = Better_User::getInstance($username, 'nickname');
			$suserInfo = $suser->getUser();
			if ($suserInfo['uid'] && $this->uid!=$suserInfo['uid']) {
				$msg_id = Better_User::getInstance($this->uid)->notification()->directMessage()->send(array(
					'receiver' => $suserInfo['uid'],
					'content' => $text
					));
					
				if ($msg_id) {
					$this->msg = $this->lang->robot->command->s->success;
					$result = true;
				} else {
					$this->error = self::UNKNOWN_ERROR;
				}
			} else {
				$this->msg = $this->lang->robot->command->s->user_invalid;
				$this->error = self::COMMAND_PARAM_NOT_VALID;
			}
		} else {
			$this->msg = $this->lang->robot->command->s->content_invalid;
			$this->error = self::COMMAND_PARAM_NOT_VALID;
		}
		
		return $result;
	}
	
	protected function commandCancel(array $params)
	{
		$result = false;
		$bid = isset($params['bid']) ? $params['bid'] : 0;
		
		$this->error = self::COMMAND_PARAM_NOT_VALID;
		$this->msg = $this->lang->robot->lang->command->cancel->failed;
					
		if ($bid) {
			$data = Better_Blog::getBlog($bid);
			$bid = $data['blog']['bid'];
			if ($bid) {
				$uid = $data['user']['uid'];
				if ($uid==$params['commandUid'] && Better_Blog::delete($bid)) {
					$this->error = '';
					$this->msg = $this->lang->robot->command->cancel->success;
					$result = true;							
				}
			}
		}
		
		return $result;
	}
	
	protected function commandPost(array $params)
	{
		
	}
}