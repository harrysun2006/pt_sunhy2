<?php

/**
 * Ppns Session
 * 
 * @package Better.DAO.Ppns
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Ppns_Session extends Better_DAO_Base
{
	private static $instance = null;

	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'ppns_session';
		$this->priKey = 'uid';
		$this->orderKey = 'start_time';
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