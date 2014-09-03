<?php

/**
 * 商户相关
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Business_Notification extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'shopkeeper_notification';
		$this->orderKey = 'id';	
		$this->priKey = 'id';	
	}

	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('business_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}		
		return self::$instance;
	}
	
	public function getInfo($id)
	{		
		$sql = "select * from ".$this->tbl." where r_id=".intval($id);		
		$rs = self::squery($sql, $this->rdb);
		$row = (array)$rs->fetch();		
		return $row;
	}
	
	
}