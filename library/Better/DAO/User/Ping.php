<?php

/**
 * 用户Ping
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_Ping extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_ping';
    	$this->priKey = 'dateline';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct ($identifier);
		
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}		
}