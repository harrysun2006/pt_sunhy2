<?php

/**
 * 宝物兑换
 * 
 * @package Better.DAO.Treasure
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Treasure_ExchangeRequest extends Better_DAO_Base
{
	private static $instance = null;

	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'treasure_exchange_request';
		$this->priKey = 'id';
		$this->orderKey = 'dateline';
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