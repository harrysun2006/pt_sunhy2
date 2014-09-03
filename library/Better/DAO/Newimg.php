<?php

/**
 * 记录图片
 *
 * @author fengjun <fengj@peptalk.cn>
 */

class Better_DAO_Newimg extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'admin_imglog';
		$this->priKey = 'id';
		$this->orderKey = &$this->priKey;
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new Better_DAO_Newimg();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	public function remove($ids)
	{
		$this->wdb->delete($this->tbl, $this->wdb->quoteInto('id IN(?)', $ids));
	}
}