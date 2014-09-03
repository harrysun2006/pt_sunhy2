<?php

/**
 * 微博客相关数据操作
 *
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Todopoi extends Better_DAO_Base
{
	
	private static $instance = array();

	
    public function __construct($identifier = 0)
    {
		$this->tbl = BETTER_DB_TBL_PREFIX.'todo_poi';
		$this->priKey = 'id';
		
		parent::__construct ($identifier);
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_Todopoi($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	public function getByMsgId($msg_id)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$select->where('msg_id=?', $msg_id);
		$select->limit(1);
		$result = self::squery($select, $this->rdb);
		return $result->fetch();		
	}
	
	
}

?>