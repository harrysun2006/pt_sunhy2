<?php

/**
 * 用户的免打扰设置
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_Hunting_Silent extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_hunting_silent';
    	$this->priKey = 'uid';
    	$this->orderKey = &$this->priKey;
    	
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
	
	public static function getAllSilent()
	{
		$servers = Better_DAO_User_Assign::getInstance()->getServerIds();
		
		$results = array();
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);
			$rdb = $cs['r'];
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'user_hunting_silent', array(
				'uid'
				));
				
			$select->where('expire_time<?', time());
			
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach ($rows as $row) {
				$results[] = $row['uid'];
			}
		}
		
		return $results;
	}
	
}