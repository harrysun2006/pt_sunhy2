<?php

/**
 * 短信监控数据库
 * 
 * @package Better.DAO.Sms
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Sms_Monitor extends Better_DAO_Base
{
	private static $instance = null;

	public function __construct($identifier=null)
	{
		$this->tbl = 'sendqueue';
		$this->priKey = 'mobile';
		$this->orderKey = &$this->priKey;
		
		parent::__construct($identifier);
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('sms');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}
}