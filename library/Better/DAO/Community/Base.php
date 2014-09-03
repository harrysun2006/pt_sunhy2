<?php

abstract class Better_DAO_Community_Base extends Better_DAO_Base{

	protected static $hours = 24;
	protected static $serverIds =array();
	
	protected static function getServerIds()
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
	
	public static abstract function getAllResults();
	

}