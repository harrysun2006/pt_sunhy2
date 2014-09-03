<?php

/**
 * 自动合并地点队列
 *
 * @package Better.DAO
 * @author yangl
 */

class Better_DAO_AutoMergePoisQueue extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct()
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'poi_overlap';
    	$this->priKey = 'refid';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct();
		$this->assignUserDbConnection();
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
	 * 获取所有poi队列
	 * 
	 * @return array
	 */
	public function &getAllQueue()
	{
		$queues = array();
		
		$rdb = $this->rdb;
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'poi_overlap');
		$select->where('flag=?', 0);
		
		$rs = self::squery($select, $rdb);
		$queues = $rs->fetchAll();
		
		
		return $queues;
	}
		
}
