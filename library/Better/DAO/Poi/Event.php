<?php

/**
 * 记录掌门
 * 
 * @package Better.DAO.Poi
 * 
 *
 */
class Better_DAO_Poi_Event extends Better_DAO_Poi_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_event';
		$this->priKey = 'id';
		$this->orderkey = &$this->priKey;
	}
		
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('poi_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}
		return self::$instance;		
	}
	
}