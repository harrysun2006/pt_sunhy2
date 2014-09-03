<?php
/*
 * 操作 better_poi.better_poi_extra 表。
 */
	
	class Better_DAO_Poi_Extra extends Better_DAO_Base{
 		
		private static $instance = null;
	
		public function __construct()
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'poi_extra';
			$this->priKey = 'poi_id';
			$this->orderKey = &$this->priKey;
		}
	
		public static function getInstance()
		{
			//if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('poi_server', true);
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		//}

			return self::$instance;
		}
}
?>
