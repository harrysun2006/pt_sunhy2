<?php

/**
 * 通知分配表
 *
 * @package Better.DAO.Notify
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Notify_Assign extends Better_DAO_Base
{
	
	private static $instance = null;

	public function __construct($identifier=null)
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'notify_assign';
		$this->priKey = 'id';
		$this->orderKey = 'id';
		
		parent::__construct($identifier);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('assign_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}
	
}