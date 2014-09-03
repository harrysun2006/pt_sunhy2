<?php

/**
 * followers
 *
 * @package Better.DAO
 * @author fengj <fengj@peptalk.cn>
 */

class Better_DAO_Poloqueue extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX . 'polo_queue';
    	$this->priKey = 'queue_id';
    	$this->orderKey = &$this->priKey;
    	
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

	/**
	 * 弹出一个队列数据
	 * 
	 * @return array
	 */
	public function popupQueue($count=1)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('send_time=?', '0');
		$select->order('queue_time ASC');
		$select->limit($count);
		
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		
		return $rows;
	}	
}
