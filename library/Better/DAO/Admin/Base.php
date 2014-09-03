<?php

class Better_DAO_Admin_Base extends Better_DAO_Base
{
	protected static $serverIds = array();
	protected static $cacher = null;
	protected static $cachePrefix = 'admin_db_';
	
	public static function getServerIds()
	{
		if (count(self::$serverIds)==0) {
			$db = parent::registerDbConnection('assign_server');

			$select = $db->select();
			$select->from(BETTER_DB_TBL_PREFIX.'user_assign', 'DISTINCT(sid) AS sid');

			$rs = parent::squery($select, $db);
			$rows = $rs->fetchAll();
			foreach($rows as $row) {
				self::$serverIds[] = $row['sid'];
			}			
		}
		
		return self::$serverIds;
	}
	
	public static function getDbCacher()
	{
		if (self::$cacher==null) {
			self::$cacher = Better_Cache::remote();
		}
		
		return self::$cacher;
	}
	
	public static function clearDbCache($key)
	{
		//self::getDbCacher()->
	}

}