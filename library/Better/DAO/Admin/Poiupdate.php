<?php
	
	class Better_DAO_Admin_Poiupdate extends Better_DAO_Admin_Base{
		
		private static $instance = null;
	
		public function __construct()
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'poi_update';
			$this->priKey = 'id';
			$this->orderKey = 'dateline';
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
		
		public function getAll(array $params=array()){
			$page = $params['page']? $params['page']: 1;
			$pageSize = $params['count']? $params['count']: BETTER_PAGE_SIZE;
			$poi_id = $params['poi_id']? $params['poi_id']: '';
			
			$rdb = $this->rdb;
			$select = $rdb->select();
			$select2 = clone $select;
			
			$select->from($this->tbl.' AS u', array('*'));
			$select2->from($this->tbl.' AS u', array(new Zend_Db_Expr("COUNT(u.id) AS count")));
			
			if($poi_id){
				$select->where('u.poi_id=?', $poi_id);
				$select2->where('u.poi_id=?', $poi_id);
			}
			
			$select->where('u.flag=?', 0);
			$select2->where('u.flag=?', 0);
				
			$select->limitPage($page, $pageSize);
			$rs = parent::squery($select, $rdb);
			$rows = $rs->fetchAll();
			
			$rs = parent::squery($select2, $this->rdb);
			$row = $rs->fetch();
			$count = $row['count'];
			
			return array(
				'rows' => $rows,
				'count' => $count
			);		
	}
	
	
	public function getByPoiId($poi_id){
		$rdb = $this->rdb;
		$select = $rdb->select();
		$select->from($this->tbl.' AS u', array('id'));
		$select->where('u.poi_id=?', $poi_id);
		//$select->where('u.flag=?', 0);
		$rs = parent::squery($select, $rdb);
		$row = $rs->fetch();
		
		return $row['id'];
	}
	
	
}
?>