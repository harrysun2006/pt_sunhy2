<?php

/**
 * 签到过的POI
 * 
 */
class Better_DAO_Poi_Checkin extends Better_DAO_Base
{
	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_checkin';
		$this->orderkey = 'dateline';
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