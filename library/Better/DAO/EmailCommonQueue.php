<?php

/**
 * 发送Email队列2
 *
 * @package Better.DAO
 */

class Better_DAO_EmailCommonQueue extends Better_DAO_Base
{
  
  	private static $instance = array();
  	private static $_maxCnt = 200;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'email_queue';
    	$this->priKey = 'queue_id';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct($identifier);
		
	}
	
  	public static function getInstance($identifier=0)
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function reconnection()
	{
		try {
			$this->rdb->getConnection();
			$this->wdb->getConnection();
		} catch (Exception $e) {
			$this->rdb->closeConnection();
			$this->wdb->closeConnection();
			
			$db = parent::registerDbConnection('common_server', true);
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}
	
	/**
	 * 获取所有Email队列
	 * 
	 * @return array
	 */
	public function &getAllQueue()
	{
		$rdb = $this->rdb;
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'email_queue');
		$select->where("result is NULL");
		$select->orwhere(
			new Zend_Db_Expr('result=\'FAILED\' AND tried<3')
			);
		$select->limit(self::$_maxCnt);
		$rs = self::squery($select, $rdb);
		$rows = $rs->fetchAll();
		
		return $rows;
	}
		
}
