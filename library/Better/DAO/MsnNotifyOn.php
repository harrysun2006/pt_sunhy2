<?php

/**
 * msn通知On数据库操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_MsnNotifyOn extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'msn_notify_on';
    	$this->priKey = 'uid';
    	$this->orderKey = 'dateline';
    	
		parent::__construct($identifier);
		$this->assignUserDbConnection();
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
		
}
