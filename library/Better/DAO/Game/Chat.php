<?php

/**
 * 挖宝游戏对话
 * 
 * @package Better.DAO.Game
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Game_Chat extends Better_DAO_Game_Base
{
	protected static $instance = array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->tbl = BETTER_DB_TBL_PREFIX.'game_chat';
		$this->priKey = 'id';
		$this->orderKey = 'dateline';
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

		return self::$instance;
	}	
	
}