<?php

/**
 *y优惠券
 *
 * @package Better.DAO
 * @author fengj <fengj@peptalk.cn>
 */

class Better_DAO_Marketcoupon extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX . 'market_coupon';
    	$this->priKey = 'id';
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
		
	public function getunused($partner)
	{		
		$result = array();		
		$sqlrows = "select * from ".$this->tbl." where partner=".$partner." and uid=0";		
		$rs = self::squery($sqlrows, $this->rdb);
		$result = $rs->fetchAll();			
		return $result;				
	}	
}
