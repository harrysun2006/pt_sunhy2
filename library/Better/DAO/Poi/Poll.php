<?php

/**
 * POI投票数据
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
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_poll';
		$this->priKey = 'id';
		$this->orderKey = 'poll_time';
    	
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	public function insert($data)
	{
		$rows_affected = $this->wdb->insert($this->tbl, $data);
		return $this->wdb->lastInsertId() ? $this->wdb->lastInsertId() : $rows_affected;
	}	
	
	public function get($val)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$this->parseWhere($select, $val);
		$select->limit(1);

		$result = self::squery($select, $this->rdb);

		return $result->fetch();
	}	
}