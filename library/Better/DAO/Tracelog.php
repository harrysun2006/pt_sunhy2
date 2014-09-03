<?php

/**
 * 记录log
 *
 * @author fengjun <fengj@peptalk.cn>
 */

class Better_DAO_Tracelog extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'admin_tracelog';
		$this->priKey = 'id';
		$this->orderKey = &$this->priKey;
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new Better_DAO_Tracelog();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
}