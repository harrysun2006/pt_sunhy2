<?php

/**
 * RSS导入表
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_RssImport extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'rss_import';
    	$this->priKey = 'uid';
    	$this->orderKey = 'dateline';
    	
		parent::__construct($identifier);
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new Better_DAO_RssImport($identifier);
		}
		
		return self::$instance[$identifier];
	}
		
}
