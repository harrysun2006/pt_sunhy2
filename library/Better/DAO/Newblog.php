<?php

/**
 * 记录新的发布文章
 *
 * @author fengjun <fengj@peptalk.cn>
 */

class Better_DAO_Newblog extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'admin_newblog';
		$this->priKey = 'id';
		$this->orderKey = &$this->priKey;
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new Better_DAO_Newblog();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}

		return self::$instance;
	}
	
	/**
	 * 貌似采用PDO的Statement执行xy相关操作会报错，只能直接通过sql进行操作
	 *
	 */
	public function insert($data)
	{
		return $this->_insertXY($data) ? $data['bid'] : 0;
	}

	public function update($data, $val='', $cond='AND')
	{
		return $this->_updateXY($data, $val, $cond);
	}
}