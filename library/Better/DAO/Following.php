<?php

/**
 * 用户关注别人相关数据操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 * 
 * 2010-01-30 已过期 yangl
 */

class Better_DAO_Following extends Better_DAO_Base
{
  
  	//private static $instance = array();
  
 	/**
   	*
    */
    /*public function __construct($identifier = null)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'following';
    	$this->priKey = 'uid';
    	$this->orderKey = 'dateline';
    	
		parent::__construct ($identifier);
		
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_Following($identifier);
		}
		
		return self::$instance[$identifier];
	}*/
		
}
