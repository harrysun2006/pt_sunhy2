<?php

/**
 * 用户发送的站内私信数据操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_DmessageSend extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = null)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'dmessage_send';
    	$this->priKey = 'msg_id';
    	$this->orderKey = 'dateline';
    	
		parent::__construct ($identifier);
		
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_DmessageSend($identifier);
		}
		
		return self::$instance[$identifier];
	}
	
	public function getCount(array $params)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$params['uid'] && $select->where('uid=?', $params['uid']);
		$params['__since__'] && $select->where('msg_id>?', $params['__since__']);
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		
		return (int)$row['total'];
	}
		
	public function getDirectmesssageInfo($msgid)
	{
		$sql = "SELECT * FROM {$this->tbl} WHERE msg_id='{$msgid}'";
		$rs = $this->query($sql);
		$row = $rs->fetchAll();
		return $row;
	}
	
	public function getAllHistory($uid)
	{
		$select = $this->rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'dmessage_send', '*');
		$select->where('uid='.$uid.' and to_uid='.BETTER_SYS_UID);
		$select->orWhere('uid='.BETTER_SYS_UID.' and to_uid='.$uid);
		$select->order('dateline DESC');
		$rs = self::squery($select, $this->rdb);
		$rows = $rs->fetchAll();
		return $rows;
	}
}
