<?php
	
	class Better_DAO_Admin_Poitippoll extends Better_DAO_Admin_Base{
		
		private static $instance = null;
	
		public function __construct()
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'poi_poll';
			$this->priKey = 'id';
			$this->orderKey = &$this->priKey;
		}
	
		public static function getInstance()
		{
			if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('poi_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

			return self::$instance;
		}
		
		public function getAllPOItippolls($params=array()){
			
			$results = $data = array();
			
			$cacheKey = $params['cacheKey'] ? $params['cacheKey'] : '';
			$cacheKey = self::$cachePrefix.$cacheKey;
			$page = $params['page'] ? intval($params['page']) : 1;
			$from = $params['from'] ? (int) $params['from'] : 0;
			$to = $params['to'] ? (int) $params['to'] : 0;
			$userId = $params['userId'] ? trim($params['userId']) : '';
			$blogId = $params['blogId'] ? trim($params['blogId']) : '';
			$poiId = $params['poiId'] ? trim($params['poiId']) : '';
			$reload = $params['reload'] ? $params['reload'] : 0;
			
		  Better_Cache_Lock::getInstance()->wait($cacheKey);

		if ($reload || !parent::getDbCacher()->test($cacheKey)) {
			Better_Cache_Lock::getInstance()->lock($cacheKey);

				$rdb = $this->rdb;
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'poi_poll AS pl', '*');
				$select->join(BETTER_DB_TBL_PREFIX.'poi AS p', 'pl.poi_id=p.poi_id', '*');
				
				
				if ($from>0) {
					$select->where('pl.poll_time>=?', $from);
				}
				
				if ($to>0) {
					$select->where('pl.poll_time<=?', $to);
				}
				
				
				if ($userId!='') {
					$select->where($rdb->quoteInto('pl.uid LIKE ?', '%'.$userId.'%'));
				}
				
				if ($blogId!='') {
					$select->where($rdb->quoteInto('pl.blog_id LIKE ?', '%'.$blogId.'%'));
				}
				
				if ($poiId!='') {
					$select->where($rdb->quoteInto('pl.poi_id LIKE ?', '%'.$poiId.'%'));
				}
				

				$select->order('pl.poll_time DESC');
				$sql=$select->__toString();
				
				$results = $rdb->fetchAll($sql);
				
			
			parent::getDbCacher()->set($cacheKey, $results, 300);
			Better_Cache_Lock::getInstance()->release($cacheKey);
		} else {
			$results = parent::getDbCacher()->get($cacheKey);
		}

		return $results;
		}
		
	}
?>