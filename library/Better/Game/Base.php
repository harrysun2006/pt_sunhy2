<?php

/**
 * 
 * 游戏基类
 * 
 * @package Better.Game
 * @author leip <leip@peptalk.cn>
 *
 */

defined('IN_GAME') || define('IN_GAME', true);

abstract class Better_Game_Base
{
	protected $uid = 0;
	protected $user = null;
	protected $config = null;
	
	protected function __construct($uid)
	{
		$this->uid = $uid;
		if ($uid>0) {
			$this->user = Better_User::getInstance($uid);
		}	
		
		$this->config = Better_Config::getAppConfig()->game;
	}
	
	public function __call($method, $params)
	{
		Better_Log::getInstance()->logAlert('Game method ['.$method.'] not valid', 'game');
	}	
	
}