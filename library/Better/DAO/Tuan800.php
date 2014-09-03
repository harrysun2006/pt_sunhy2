<?php

/**
 * taun800数据
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Tuan800 extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = 'poiact_tuan800';
		$this->orderKey = 'identifier';	
		$this->priKey = 'identifier';	
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
	
	
	public function &getAll()
	{
		$result = array(
			'rows' => array(),
			'count' => 0
			);
		
		$select = $this->rdb->select();
		$select->from($this->tbl.' as t', array('*'));
		$select->where('t.flag=?', 0);

		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		foreach ($rows as $row) {
			$result['rows'][] = $row;
		}
		$result['count'] = count($result['rows']);
		
		return $result;
	}
	
}