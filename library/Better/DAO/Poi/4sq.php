<?php

/**
 * Foursquare poi数据操作
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Poi_4sq extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_4sq';
		$this->priKey = 'poi_id';
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
	
	/**
	 * 根据poi取对应的4sq id
	 * 
	 * @return integer
	 */
	public function get4sqId($poiId)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			'poi_id_4sq'
			));
		$select->where('poi_id=?', $poiId);
		$select->limit(1);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		$result = $row['poi_id_4sq'];
		
		return $result;
	}
	
	/**
	 * 保存一个对应关系
	 * 
	 * @return bool
	 */
	public function save($poiId, $poiId4sq)
	{
		return $this->replace(array(
			'poi_id' => $poiId,
			'poi_id_4sq' => $poiId4sq
			), $this->tbl);
	}

}