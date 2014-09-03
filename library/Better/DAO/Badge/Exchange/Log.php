<?php

/**
 * 勋章兑换记录
 *
 * @package Better.DAO.Badge.Exchange
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Badge_Exchange_Log extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = null)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'badge_exchange_log';
    	$this->priKey = 'id';
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
	}
	
	public function exchanged($uid, $badgeId)
	{
		$row = $this->get(array(
			'uid' => $uid,
			'badge_id' => $badgeId
			));
			
		return isset($row['id']) ? true : false;
	}
	
}
