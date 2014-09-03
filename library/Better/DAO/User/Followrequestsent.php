<?php

/**
 * 取用户发出的关注请求
 * 
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_User_Followrequestsent extends Better_DAO_Base
{
	private static $instance = array();

 	/**
   	*
    */
   /* public function __construct($identifier = 0)
    {
		$this->tbl = BETTER_DB_TBL_PREFIX.'follow_request_sent';
		$this->priKey = 'uid';
		$this->orderKey = 'dateline';
		
		parent::__construct ($identifier);
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}*/
	
}