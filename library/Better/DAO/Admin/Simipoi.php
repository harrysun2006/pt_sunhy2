<?php
	
	class Better_DAO_Admin_Simipoi extends Better_DAO_Admin_Base{
		
		private static $instance = null;
	
		public function __construct($type='similar')
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'poi_'.$type;
			$this->priKey = 'id';
			$this->orderKey = 'refid';
		}
	
		public static function getInstance($type='similar')
		{
			if (self::$instance==null) {
			self::$instance = new self($type);
			$db = parent::registerDbConnection('poi_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

			return self::$instance;
		}
		
		public function getAllSimiPOIs(array $params=array()){
			
			$results1 = $results2 = $results3 = array();
			$ref_arr = array();
			$results = array('count'=>0, 'rows'=>array());
			
			$page = $params['page'] ? intval($params['page']) : 1;
			$reload = $params['reload'] ? $params['reload'] : 0;
			$pageSize = $params['pageSize'] ? intval($params['pageSize']) : BETTER_PAGE_SIZE;
			$namekeyword = $params['namekeyword']? $params['namekeyword'] : '';
			$placekeyword = $params['placekeyword']? $params['placekeyword'] : '';

			$rdb = $this->rdb;
			
			$select3 = $rdb->select();
			$select3->from($this->tbl.' AS s', array('s.refid'));
			if($namekeyword){
				$select3->where('s.refname LIKE ?', '%'.$namekeyword.'%');
			}
			if($placekeyword){
				$select3->where('s.refaddrline LIKE ?', '%'.$placekeyword.'%');
			}
			$select3->group('s.refid');
			$select3->order('s.refid');
			$select3->limitPage($page, $pageSize);
			$rs = parent::squery($select3, $rdb);
			$results3 = $rs->fetchAll();
			foreach($results3 as $v){
				$ref_arr[] = $v['refid'];
			}
			
			if($ref_arr){
				$select = $rdb->select();
				$select->from($this->tbl.' AS s', '*');
				$select->where('s.refid IN (?)', $ref_arr);
				$select->order('s.refid');
				$rs = parent::squery($select, $rdb);
				$results1 = $rs->fetchAll();
			}
			
			
			$select2= $rdb->select();
			$select2->from($this->tbl.' AS s', array(new Zend_Db_Expr("COUNT(distinct s.refid) AS count")));
			if($namekeyword){
				$select2->where('s.refname LIKE ?', '%'.$namekeyword.'%');
			}
			if($placekeyword){
				$select2->where('s.refaddrline LIKE ?', '%'.$placekeyword.'%');
			}
			$rs = parent::squery($select2, $rdb);
			$results2 = $rs->fetch();
			
			$results['count'] = $results2['count'];
			$results['rows'] = $results1;
			
			
			return $results;
	}
	
	
}
?>