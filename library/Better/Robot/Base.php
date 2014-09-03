<?php

/**
 * Better机器人基类
 * 
 * @package Better.Robot
 * @author leip <leip@peptalk.cn>
 *
 */

abstract class Better_Robot_Base
{
	protected $uid = 0;
	protected $protocol = '';
	protected $robot = '';
	protected $commands = array(
		'h', 'k', 'g', 'on', 'off', 's', 'd', 'cancel', 'help'
		);
	protected $error = '';
	protected $msg = '';
	protected $lang = null;
	
	const COMMAND_NOT_SUPPORTED = 1000;
	const COMMAND_NOT_IMPLEMENT = 2000;
	const COMMAND_PARAM_NOT_VALID = 3000;
	const UNKNOWN_ERROR = 9000;
	
	public function __construct($uid, $robot)
	{
		$this->uid = $uid;
		$this->robot = $robot;
		
		if ($this->uid) {
			$userInfo = Better_User::getInstance($this->uid)->getUser();
			
			//	让机器人说用户的语言
			$this->lang = Better_Language::loadIt($userInfo['language']);
		}
	}
	
	public function __call($method, $params)
	{
		return $this->execCommand($method, $params);
	}
	
	/**
	 * 帮助信息
	 * 
	 * @return bool
	 */
	abstract protected function commandH();
	abstract protected function commandHelp();
	
	/**
	 * 打开消息提醒
	 * 
	 * @param $params
	 * @return bool
	 */
	abstract protected function commandK(array $params);
	abstract protected function commandOn(array $params);
	
	/**
	 * 关闭消息提醒
	 * 
	 * @param $params
	 * @return bool
	 */
	abstract protected function commandG(array $params);
	abstract protected function commandOff(array $params);
	
	/**
	 * 发送私信
	 * 
	 * @param $params
	 * @return bool
	 */
	abstract protected function commandD(array $params);
	abstract protected function commandS(array $params);
	
	/**
	 * 发表围脖
	 * 
	 * @param $params
	 * @return bool
	 */
	abstract protected function commandPost(array $params);
	
	/**
	 * 删除刚才发的围脖
	 * 
	 * @param array $params
	 * @return array
	 */
	abstract protected function commandCancel(array $params);
	
	/**
	 * 获取可用的机器人指令
	 * 
	 * @return array
	 */
	public function getAvailableCommands()
	{
		return $this->commands;
	}
	
	/**
	 * 执行机器人指令
	 * 
	 * @param $command
	 * @param $params
	 * @return integer
	 */
	public function execCommand($command, $params=array())
	{
		if (in_array($command, $this->commands)) {
			$commandMethod = 'command'.ucfirst($command);
			if (method_exists($this, $commandMethod)) {
				return call_user_func(array(
					$this, 
					$commandMethod
					), $params);
			} else {
				$this->error = self::COMMAND_NOT_IMPLEMENT;
				$this->msg = '指令错误，该指令尚未开发完成';
				Better_Log::getInstance()->logAlert('Command '.$command.' not implement', 'robot');
				
				return false;
			}
		} else {
			$this->msg = '指令错误，不支持该指令';
			$this->error = self::COMMAND_NOT_SUPPORTED;
			Better_Log::getInstance()->logAlert('Command '.$command.' not supported', 'robot');
			
			return false;
		}
	}
	
	/**
	 * 获取错误新手
	 * 
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}
	
	/**
	 * 获取返回的提示消息
	 * 
	 * @return string
	 */
	public function getMessage()
	{
		return $this->msg;	
	}

}