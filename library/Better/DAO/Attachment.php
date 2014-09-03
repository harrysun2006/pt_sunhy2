<?php

/**
 * 附件相关数据操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Attachment extends Better_DAO_Base
{
	
	protected static $parsedAttach = array();
	protected static $instance = array();
	protected static $assignedAttach = array();
	
	public $serverId = '';
	
	/**
	 *
	 */
	public function __construct($identifier=0)
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'attachments';
		$this->priKey = 'file_id';
		$this->orderKey = &$this->priKey;
		
		parent::__construct($identifier);
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_Attachment($identifier);
		}
		
		return self::$instance[$identifier];
	}

	public function getAllByFids($fids, $limit=0, $method='limitPage')
	{
		return $this->getAll(array(
				$this->priKey=>$fids,
				), $limit, $method);
	}
	
	/**
	 * 更新附件分配表
	 *
	 * @TODO 将该方法删除，改为直接调用AttachAssign中的方法
	 *
	 * @param $fid
	 * @param $sid
	 * @return unknown_type
	 */
	public function insertAssign($fid, $sid)
	{
		Better_DAO_AttachAssign::getInstance()->insert(array(
			'fid' => $fid,
			'sid' => $sid,
			));
	}
	
	/**
	 * 删除一个附件分配关系
	 *
	 * @TODO 将该方法删除，改为直接调用AttachAssign中的方法
	 * @param $fid
	 * @return unknown_type
	 */
	public function delAssign($fid)
	{
		$fid && Better_DAO_AttachAssign::getInstance()->deleteFid($fid);
	}
	
}

?>