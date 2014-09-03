<?php

/**
 * Lbs
 * 
 * @package Better.DAO.Lbs
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Lbs_Cache extends Better_DAO_Base
{
	protected static $instance = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'lbs_cache';
		$this->priKey = 'uid';
		$this->orderKey = &$this->priKey;
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