<?php

/**
 * 发送Email队列
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_EmailQueue extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'email_queue';
    	$this->priKey = 'queue_id';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct($identifier);
		$this->assignUserDbConnection(true);
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_EmailQueue($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	/**
	 * 获取所有Email队列
	 * 
	 * @return array
	 */
	public static function &getAllQueue()
	{
		$queues = array();
		
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'email_queue');
			$select->where("result is NULL");
			$select->orwhere(
				new Zend_Db_Expr('result=\'FAILED\' AND tried<3')
				);
			
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$queues[$row['queue_time']] = $row;
			}
		}
		
		if (count($queues)>0) {
			ksort($queues);
		}
		
		return $queues;
	}
		
}
