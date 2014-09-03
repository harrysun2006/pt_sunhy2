<?php

/**
 * Better_DAO_3rdFollowid 
 *
 * @package Better.DAO
 * @author fengj <fengj@peptalk.cn>
 */

class Better_DAO_3rdFriends extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX . '3rdfriends';
    	$this->priKey = 'id';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct($identifier);
	}
	
	
	/**
	 * 
	 * @param $identifier
	 * @return unknown_type
	 */
  	public static function getInstance($identifier=0)
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}	
	
	/**
	 * 
	 * @param $partner
	 * @param $tid
	 * @return unknown_type
	 */
	public function getFriendIds($partner, $tids)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$select->where('partner=?', $partner);
		$new_tid = array();
		foreach ($tids as $tid) {
			$new_tid[] = (string)$tid;
		}
		$select->where('tid in (?)', $new_tid);
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetchAll();	

		return $row;
	}
	
}
