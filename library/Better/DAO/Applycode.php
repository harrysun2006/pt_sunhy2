<?php

/**
 * 申请的邀请码
 *
 * @package Better.DAO
 * @author yangl
 */

class Better_DAO_Applycode extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'apply_invitecode';
    	$this->priKey = 'id';
    	$this->orderKey = 'id';
    	
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
		
}
