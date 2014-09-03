<?php

/**
 * 用户加好友请求
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_FriendsRequest extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'friends_request';
    	
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

	public function getRequestCount()
	{
		$sql = "SELECT COUNT(*) AS count FROM {$this->tbl} WHERE uid='{$this->identifier}'";
		$rs = $this->query($sql);
		$row = $rs->fetch();
		
		return $row['count'];
	}
	
	/**
	 * 检查最近是否发过加好友请求 
	 * 
	 * @param int   $since 最近时间
	 * @return array       已经加过好友的id
	 */
	public function getRecentRequests($since)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl);
		$select->where('uid=?', $this->identifier);
		$select->where('dateline>?',$since);
		$rs = self::squery($select, $this->rdb);
		
		return $rs->fetchAll();
	}
}
