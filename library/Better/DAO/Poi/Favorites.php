<?php

/**
 * POI收藏数据
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_Poll extends Better_DAO_Base
{
	private static $instance = array();

    public function __construct($identifier = 0)
    {
		parent::__construct ($identifier);
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_favorites';
		$this->priKey = 'uid';
		$this->orderKey = 'dateline';

		parent::__construct ($identifier);
		
		$this->assignUserDbConnection();
	}

  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
}