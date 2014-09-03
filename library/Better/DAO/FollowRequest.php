<?php

/**
 * 用户加关注请求
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 * 
 * 2010-01-30 已过期 yangl
 */

class Better_DAO_FollowRequest extends Better_DAO_Base
{
  
  	//private static $instance = array();
  
 	/**
   	*
    */
    /*public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'follow_request';
    	
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
	
	public function getRequestInfo($uid)
	{
		$sql = "SELECT * FROM {$this->tbl} WHERE request_uid='{$uid}' and uid='{$this->identifier}'";	
		$rs = $this->query($sql);
		$row = $rs->fetchAll();
		return $row;
	}*/
}
