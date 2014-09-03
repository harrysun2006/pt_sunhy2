<?php

/**
 * 兑宝记录
 * 
 * @package Better.DAO.Game
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Game_Exchange extends Better_DAO_Game_Base
{
	protected static $instance = array();
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'treasure_exchange_request';
		$this->priKey = 'id';
		$this->orderKey = 'dateline';
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
	
	/**
	 * 
	 * 根据时间获得兑宝记录
	 * @param unknown_type $time
	 * @return array
	 */
	public function getHistory($time)
	{
		$rows = array();
		
		$year = substr($time, 0, 4);
		$month = substr($time, -2);
		
		$sql = "SELECT r.*, t.*
		FROM ".BETTER_DB_TBL_PREFIX."treasure_exchange_request AS r
			JOIN ".BETTER_DB_TBL_PREFIX."treasure AS t
				ON t.id=r.treasure_id
		WHERE MONTH(FROM_UNIXTIME(r.dateline))='".$month."' AND YEAR(FROM_UNIXTIME(r.dateline))='".$year."'
		ORDER BY r.dateline DESC
		";

		$rs = self::squery($sql, $this->rdb);
		$rows = $rs->fetchAll();
		
		return $rows;
	}
	
}