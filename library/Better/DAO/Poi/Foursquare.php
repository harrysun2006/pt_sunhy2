<?php

/**
 * 4sq poi数据操作
 * 
 * @package Better.DAO.Poi
 * @author yangl
 *
 */
class Better_DAO_Poi_Foursquare extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_foursquare';
		$this->priKey = 'id';
		$this->orderKey = &$this->priKey;
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

	public function save(array $params)
	{
		return $this->replace($params, $this->tbl);
	}
	
	public function get4sqPoi($vid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array('*'));
		$select->where('id=?', $vid);
		$select->limit(1);
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		return $row;
	}

}