<?php

/**
 * 关键词过滤
 *
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Filter extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'admin_filterwordlog';
		$this->priKey = 'id';
		$this->orderKey = &$this->priKey;
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	
	
	


}