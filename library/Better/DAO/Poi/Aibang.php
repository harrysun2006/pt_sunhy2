<?php

/**
 * 爱帮poi数据操作
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_Aibang extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_aibang';
		$this->priKey = 'bizid';
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
	
	public function getAbPoi($abId)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			'bizid', 'name', 'tel', 'addr', 'price', 'desc', 'taste', 'tag', 'review', 'dist', 'mapxy', 'cfd', 'lon', 'lat', 'recommend_dish',
			'x', 'y'
			));
		$select->where('bizid=?', $abId);
		$select->limit(1);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return $row;
	}
	
	public function save(array $params)
	{
		return $this->replace($params, $this->tbl);
	}

}