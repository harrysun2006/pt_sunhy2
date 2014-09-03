<?php

/**
 * POI举报
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Poi_Report extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->tbl = BETTER_DB_TBL_PREFIX.'poi_report';
		$this->orderKey = 'report_time';
		$this->priKey = 'id';
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
	
	public function reported($poiId, $uid, $reason)
	{
		$result = false;
		$uid = (int)$uid;
		
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $uid);
		$select->where('poi_id=?', $poiId);
		$select->where('report_time>?', time()-3600*24);
		$select->where('reason=?', $reason);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		$result = $row['total'] ? true : false;
		
		return $result;		
	}
	
}