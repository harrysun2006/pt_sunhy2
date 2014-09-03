<?php

/**
 * Publictimeline的队列
 *
 * @package Better.DAO
 * @author fengj <fengj@peptalk.cn>
 */

class Better_DAO_Queue_Publictimeline extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'queue_publictimeline';
    	$this->priKey = 'id';
    	$this->orderKey = 'queue_time';
    	
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
		
}
