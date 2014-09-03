<?php

/**
 * 取用户相关微博的DAO
 * 
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_User_Rtcounters extends Better_DAO_Base
{
	private static $instance = array();

	private $profileTbl = '';
	private $attachTbl = '';

 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
		$this->tbl = BETTER_DB_TBL_PREFIX.'rtblog_counters';
		
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