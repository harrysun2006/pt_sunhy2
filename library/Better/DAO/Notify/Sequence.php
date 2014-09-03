<?php

/**
 * 通知序列表
 *
 * @package Better.DAO.Notify
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Notify_Sequence extends Better_DAO_Base
{
	
	private static $instance = null;
	public static $seq = null;

	public function __construct($identifier=null)
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'notify_sequence';
		$this->priKey = 'seq';
		$this->orderKey = 'seq';
		
		parent::__construct($identifier);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('assign_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}
	
	public function get()
	{
		$rows = $this->getAll(null, null);
		self::$seq = $rows[0]['seq'];

		$this->increase('seq');

		return self::$seq;
	}

}