<?php

/**
 * 纪录版本检查
 *
 * @package Better.DAO
 * @author fengj <fengj@peptalk.cn>
 */

class Better_DAO_SecretLog extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX . 'secretlog';
    	$this->priKey = 'secret';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct($identifier);
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
	public  function getByFiled($imei)
	{
		$select = $this->rdb->select();
		$select->from($this->tbl, '*');
		$select->where('imei=?', $imei);
		$rs = self::squery($select, $this->rdb);
		$row = $rs->fetch();
		return $row;
	}
		
}
