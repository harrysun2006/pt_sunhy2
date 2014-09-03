<?php

/**
 * Ppns Offline Message
 * 
 * @package Better.DAO.Ppns
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Ppns_Offline extends Better_DAO_Base
{
	private static $instance = null;

	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'ppns_offline';
		$this->priKey = 'uid';
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
	
	public function hasOfflineMsg($uid)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('uid=?', $uid);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (isset($row['uid']) && $row['uid']) ? true : false;
	}
	
	public function cleanOfflineMsg($uid)
	{
		$sql = "DELETE FROM `".$this->tbl."` WHERE `uid`='".intval($uid)."'";
		self::squery($sql, $this->wdb);
	}
	
	public function popupQueue()
	{
		$result = array();
		
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS q');
		$select->join(BETTER_DB_TBL_PREFIX.'ppns_session AS s', 's.uid=q.uid', array());
		$select->where('q.ready=?', 1);
		
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		if ($row['uid']) {
			$result = &$row;
			
			$this->cleanOfflineMsg($row['uid']);
		}
		
		return $result;
	}
	
}