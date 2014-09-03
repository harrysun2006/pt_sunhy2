<?php

/**
 * 附件分配表
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_AttachAssign extends Better_DAO_Base
{
	
	private static $instance = null;

	public function __construct($identifier=null)
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'attach_assign';
		$this->priKey = 'fid';
		$this->orderKey = 'fid';
		
		parent::__construct($identifier);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new Better_DAO_AttachAssign();
			$db = parent::registerDbConnection('assign_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}
	
	/**
	 * 获取附件的sid
	 * 
	 * @param $fileId
	 * @return integer
	 */
	public function getSid($fileId)
	{
		$sid = 0;
		
		if ($fileId) {
			$r = $this->get($fileId);
			isset($r['sid']) && $sid = $r['sid'];
		}
		
		return $sid;
		
	}
	
}