<?php

/**
 * Better_DAO_3rdFollowid 
 *
 * @package Better.DAO
 * @author fengj <fengj@peptalk.cn>
 */

class Better_DAO_3rdFollowid extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX . '3rdfollowid';
    	$this->priKey = 'id';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct($identifier);
		$this->assignUserDbConnection();
	}
	
	
	/**
	 * 
	 * @param $identifier
	 * @return unknown_type
	 */
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_3rdFollowid($identifier);
		}
		
		return self::$instance[$identifier];
	}	
	
	/**
	 * 
	 */
	public function getFollowId($uid)
	{
		
	}
}
