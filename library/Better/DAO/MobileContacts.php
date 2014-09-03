<?php

/**
 * 手机号码簿记录
 * 
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_MobileContacts extends Better_DAO_Base{
	
	private static $instance= null;
	
	function __construct(){
		
		$this->tbl = BETTER_DB_TBL_PREFIX.'mobile_contacts';
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