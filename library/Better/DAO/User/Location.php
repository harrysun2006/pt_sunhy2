<?php

/**
 * qbs备份/冗余 用户位置操作
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_Location extends Better_DAO_Base
{
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct()
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'userlocation';
    	
		parent::__construct ();
	}
	
  	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
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