<?php
	
	class Better_DAO_Admin_Badge extends Better_DAO_Admin_Base{
		
		private static $instance = null;
	
		public function __construct()
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'badge';
			$this->priKey = 'id';
			$this->orderKey = &$this->priKey;
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
		
		public function getAllBadges(array $params=array()){
			
			$results = $data = array();
			$page = $params['page'] ? intval($params['page']) : 1;
			$namekeyword = $params['namekeyword'] ? trim($params['namekeyword']) : '';
			$reload = $params['reload'] ? intval($params['reload']) : 0;
			$cacheKey = $params['cachekey'] ? trim($params['cacheKey']) : '';
			
			Better_Cache_Lock::getInstance()->wait($cacheKey);
			if(!parent::getDbCacher()->test($cacheKey) || $reload==1){
				Better_Cache_Lock::getInstance()->lock($cacheKey);
				
				$rdb = $this->rdb;
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'badge AS b', '*');
				
				if ($namekeyword!='') {
					$select->where($rdb->quoteInto('b.badge_name LIKE ?', '%'.$namekeyword.'%'));
				}
				
				$select->order('b.id DESC');
				$sql=$select->__toString();
					
				$results = $rdb->fetchAll($sql);
				
				parent::getDbCacher()->set($cacheKey, $results, 300);
			
				Better_Cache_Lock::getInstance()->release($cacheKey);
			}else{
				$results = parent::getDbCacher()->get($cacheKey);
			}
				
			return $results;
		}
		
		
	}
?>