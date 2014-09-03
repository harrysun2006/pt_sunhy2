<?php

/**
 * POI搜索日志
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_Search_Logclick extends Better_DAO_Poi_Base
{
	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_search_logclick';
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