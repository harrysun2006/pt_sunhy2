<?php
/* better_common.better_admin_poicluster 
 */
	
	class Better_DAO_Admin_Poicluster extends Better_DAO_Admin_Base{
		
		private static $instance = null;
	
		public function __construct($type='poicluster')
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'admin_'.$type;
			$this->priKey = 'id';
			//$this->orderKey = 'refid';
		}

		public static function getInstance($type='poicluster')
		{
			if (self::$instance==null) {
			self::$instance = new self($type);
			$db = parent::registerDbConnection('common_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}

			return self::$instance;
		}
		
    public function delKeyword($w)
    {
      return $this->wdb->delete($this->tbl, "keyword=\"$w\"");
    }

    /*
     * params: assoc_array(page, page_size)
     *
     * return: assoc_array(count:number, page_size:number, page:number, rows:array)
     */
    public function getKeywords(array $params=array())
    {
			$page = $params['page'] ? intval($params['page']) : 1;
			$page_size = $params['page_size'] ? intval($params['page_size']) : intval(BETTER_PAGE_SIZE);

			$rdb = $this->rdb;
			
			$select2 = $rdb->select();
			$select2->from($this->tbl.' AS s', array('s.id', 's.keyword', 's.lat', 's.lon', 's.radius'));
      $select2->where('closed=?', 0);
			$select2->limitPage($page, $page_size);
			$rs = parent::squery($select2, $rdb);
			$rows = $rs->fetchAll();
			
      // todo, consider SQL_CALC_FOUND_ROWS
      $rs = parent::squery("select count(*) as cnt from {$this->tbl} where closed=0", $rdb);
      $a = $rs->fetchAll();
      $count = intval($a[0]['cnt']);

			return array('count'=>$count, 'page_size'=>$page_size, 'page'=>$page, 'rows'=>$rows);
	}
}
?>

