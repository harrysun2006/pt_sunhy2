<?php

/**
 * 宝物兑换公告
 * 
 * @package Better.DAO.Treasure
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Treasure_Announce extends Better_DAO_Base
{
	private static $instance = null;

	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'treasure_announce';
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
	
	public function getAnnounce($page=1, $count=BETTER_PAGE_SIZE)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		$total = $row['total'];
		
		$rows = array();
		if ($total>0) {
			$select = $this->rdb->select();
			$select->from($this->tbl);
			$select->limitPage($page, $count);
			$select->order('dateline DESC');
			
			$rs = self::squery($select, $this->rdb);
			$rows = $rs->fetchAll();
		}
		
		return array(
			'count' => $total,
			'rows' => &$rows,
			);
	}
}