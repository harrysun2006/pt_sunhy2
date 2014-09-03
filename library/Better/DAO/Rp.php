<?php

/**
 * rp 增加
 *
 * @package Better.DAO
 * @author fengj <fengj@peptalk.cn>
 */

class Better_DAO_Rp extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'rp';
    	$this->priKey = 'uid';
    	$this->orderKey = 'rp_week';
    	
		parent::__construct($identifier);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function updateRp($uid, $rp, $city)
	{	
		$rdb = $this->rdb;
		$wdb = $this->wdb;
		$tb = $this->tbl;
		$select = "SELECT uid, rp_week FROM $tb WHERE uid='$uid'";
		$rs = self::squery($select, $rdb);
		$row = $rs->fetch();
		if ($row) {
			$rp_week = $row['rp_week'] + $rp;
			$sql = "UPDATE $tb SET rp_week=$rp_week, live_city='$city' WHERE uid='$uid'";
		} else {
			$rp_week = $rp;
			$sql = "INSERT INTO $tb VALUES ('$uid', $rp_week, '$city')";
		}
		$rs = self::squery($sql, $wdb);
		return $rs;
	}

	
	/**
	 * 
	 * @param $city
	 * @param $limit
	 * @return unknown_type
	 */
	public function getCityUser($city, $limit = 100)
	{
		$rdb = $this->rdb;
		$tb = $this->tbl;
		
		$sql = "SELECT * FROM $tb WHERE live_city='$city' ORDER BY rp_week DESC LIMIT $limit";
		$rs = self::squery($sql, $rdb);
		$rows = $rs->fetchAll();
		
		return $rows;
	}
	
	
	/**
	 * 
	 */
	public function getUserByIds($ids, $limit = 100)
	{
		$rdb = $this->rdb;
		$tb = $this->tbl;
		
		$uids = $ids ? join(",", $ids) : "''";
		$uids = "(" . $uids . ")";
		
		$sql = "SELECT * FROM $tb WHERE uid IN $uids ORDER BY rp_week DESC LIMIT $limit";
		$rs = self::squery($sql, $rdb);
		$rows = $rs->fetchAll();
		
		return $rows;		
	}
	
	public function clearTable()
	{
		$wdb = $this->wdb;
		$tb = $this->tbl;
		$sql = "TRUNCATE TABLE $tb";
		$rs = self::squery($sql, $wdb);
	}
}
