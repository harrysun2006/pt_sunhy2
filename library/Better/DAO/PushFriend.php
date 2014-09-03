<?php

/**
 * new bind 
 *
 * @package Better.DAO
 * @author fengj <fengj@peptalk.cn>
 */

class Better_DAO_PushFriend extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX . 'pushfriend';
    	//$this->priKey = 'id';
    	//$this->orderKey = &$this->priKey;
    	
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
			self::$instance[$identifier] = new Better_DAO_PushFriend($identifier);
		}
		
		return self::$instance[$identifier];
	}	
	
	/**
	 * 
	 */
	public function getMyMsg($uid, $limit = 1)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('uid=?', $uid);
		$select->where('flag=?', 0);
		$select->limit($limit);
		
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		
		return $rows;
	}
	
	/**
	 * 更新通知表
	 */
	public function replaceRow($data)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('uid=?', $data['uid']);
		$select->where('refuid=?', $data['refuid']);
		$select->limit(1);

		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		if (!$row) {
			return self::insert($data);
		}
		return 0;
	}
}
