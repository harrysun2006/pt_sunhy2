<?php

/**
 * 用户ID序列表
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_User_Sequence extends Better_DAO_Base
{
	
	private static $instance = null;
	public static $seq = null;

	public function __construct($identifier=null)
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'user_sequence';
		$this->priKey = 'seq';
		$this->orderKey = &$this->priKey;
		
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
	
	/**
	 * 读取序列
	 *
	 * @see library/Better/DAO/Better_DAO_Base#get($val)
	 */
	public function get()
	{
		$rows = $this->getAll(null, null);
		self::$seq = $rows[0]['seq'];
		
		return self::$seq;
	}
	
	/**
	 * 更新序列
	 * 
	 * @return 
	 */
	public function update()
	{
		$this->increase('seq');
	}

}