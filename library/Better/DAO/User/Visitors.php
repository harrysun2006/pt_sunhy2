<?php

/**
 * 用户访客数据操作
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_User_Visitors extends Better_DAO_User
{
	private static $instance = array();
	private static $sysuser = '10000,168671';
	
	public function __construct($identifier=0)
	{
		parent::__construct($identifier);
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	public function rightbar(array $uids)
	{
		$results = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach ($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$select = $rdb->select();
			$selected = array(
				'uid', 'karma', 'username', 'avatar', 'nickname'
				);
			$select->from($this->profileTbl, $selected);
			$select->where('uid IN (?)', $uids);
			
			$rs = self::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			foreach ($rows as $row) {
				$results[$row['uid']] = $row;
			}
		}
		
		return $results;
	}
					
}