<?php

/**
 * 举报Poi 处理类
 * 
 * @package Better.DAO.Denounce
 * @author yanglei
 *
 */
class Better_DAO_Denounce_Poi extends Better_DAO_Base
{
	
	private static $instance= null;
	
	function __construct(){
		
		$this->tbl = BETTER_DB_TBL_PREFIX.'denounce_poi';
		$this->priKey = 'id';
	}
	
	public static function getInstance()
	{
		if(self::$instance==null){
			self::$instance=new self();
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);
			self::$instance->setDb($db);
		}
		
		return self::$instance;
	}
	
}

?>