<?php

/**
 * qbs备份/冗余数据表操作
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_BlogLocation extends Better_DAO_Base
{
  
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct() {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'bloglocation';
    	$this->priKey = 'bid';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct ();
	}
	
  	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new Better_DAO_BlogLocation();
			$db = parent::registerDbConnection('qbs_backup_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}

  	public function replace($data, $tbl='')
	{
		return $this->_replaceXY($data, $tbl);
	}
	
  	public function insert($data, $tbl='')
	{
		return $this->_insertXY($data, $tbl);
	}
	
	public function update($data, $tbl='')
	{
		return $this->_updateXY($data, $tbl);
	}
}
