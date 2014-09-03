<?php
	
	class Better_DAO_Admin_Badgealarm extends Better_DAO_Admin_Base{
		
		private static $instance = null;
	
		public function __construct()
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'badge_alarm';
			$this->priKey = 'badge_id';
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
		
		public function filter(array $params=array()){
			
			$results = array(
				'rows'=> array(),
				'count'=> 0
			);
			$page = $params['page'] ? intval($params['page']) : 1;
			$page_size = $params['page_size'] ? intval($params['page_size']) : 20;
			$badge_id = $params['badge_id'] ? trim($params['badge_id']) : '';
			$badge_name = $params['badge_name'] ? trim($params['badge_name']) : '';
			
			$rdb = $this->rdb;
			$select = $rdb->select();
			
			if($badge_id){
				$select->where('b.id=?', $badge_id);				
			}
			if ($badge_name) {
				$select->where($rdb->quoteInto('b.badge_name LIKE ?', '%'.$badge_name.'%'));
			}
			
			$select2 = clone $select;
			
			
			$select->from(BETTER_DB_TBL_PREFIX.'badge as b', array('*', 'b.badge_name as name'));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'badge_alarm AS a', 'b.id=a.badge_id', array('*'));
			
			$select2->from(BETTER_DB_TBL_PREFIX.'badge AS b', array(new Zend_Db_Expr("COUNT(*) as count")));
			
			$select->limitPage($page, $page_size);
			
			$select->order('a.badge_id DESC');
			$select->order('b.id DESC');
			$sql=$select->__toString();
				
			$rs = $this->query($select, $rdb);
			$results['rows'] = $rs->fetchAll();
			
			$rs = $this->query($select2, $rdb);
			$count = $rs->fetch();
			$results['count'] = $count['count'];
				
			return $results;
		}
		
		
		
		/**
		 * 获得需要检查的勋章
		 */
		public function getCheckBadge(){
			$rows = array();
			$rdb = $this->rdb;
			$select = $rdb->select();
			$select->from($this->tbl.' as b', '*');
			$select->where(time().'-b.last_check>=(b.interval)*60');
			$rs = $this->query($select, $rdb);
			$rows = $rs->fetchAll();
			
			return $rows;
		}
		
		
	}
?>