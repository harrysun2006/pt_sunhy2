<?php

/**
 * 惊喜和勋章关联
 * 
 * @package Better.DAO.Poi
 * @author fengj <fengj@peptalk.cn>
 */

class Better_DAO_Poi_N2b extends Better_DAO_Poi_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX . 'poi_n2b';
		$this->orderKey = 'nid';
		$this->priKey = 'nid';		
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