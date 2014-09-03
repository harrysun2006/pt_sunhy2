<?php

/**
 * new bind 
 *
 * @package Better.DAO
 * @author fengj <fengj@peptalk.cn>
 */

class Better_DAO_NewBind extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX . 'newbind';
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
			self::$instance[$identifier] = new Better_DAO_NewBind($identifier);
		}
		
		return self::$instance[$identifier];
	}	
	
	
	/**
	 * 
	 */
	public function getNew($limit = 5)
	{
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		shuffle($sids);
		foreach($sids as $sid) {		
			$cs = parent::assignDbConnection('user_server_'.$sid, true);
			$rdb = &$cs['r'];
			$select = $rdb->select();
			$select->from($this->tbl);
			$select->limit($limit);	
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			if ($rows) return $rows;
 		}		
		
		return $rows;		
	}

}
