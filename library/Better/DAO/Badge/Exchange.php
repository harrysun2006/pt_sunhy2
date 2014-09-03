<?php

/**
 * 勋章兑换
 *
 * @package Better.DAO.Badge
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Badge_Exchange extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct()
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'badge_exchange';
    	$this->priKey = 'badge_id';
    	$this->orderKey = 'badge_id';
    	
		parent::__construct (0);
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
	
}
