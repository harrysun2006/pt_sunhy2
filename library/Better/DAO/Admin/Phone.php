<?php

class Better_DAO_Admin_Phone extends Better_DAO_Admin_Base
{
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'download_phone';
    	$this->priKey = 'pid';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct($identifier);
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
	
	
	public function getAll($params){
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$name_keyword = $params['name_keyword']? $params['name_keyword']: '';
		$brand = $params['brand']? $params['brand']: 0;
		$os = $params['os']? $params['os']: 0;
		
		$select = $this->rdb->select();
		$select2 = clone $select;
		$select->from($this->tbl.' AS p');
		$select2->from($this->tbl.' AS p', array(new Zend_Db_Expr("COUNT(p.pid) AS count")));
		$select->joinleft(BETTER_DB_TBL_PREFIX.'download_os AS o', 'p.oid=o.oid', array('o.name as oname'));
		$select->joinleft(BETTER_DB_TBL_PREFIX.'download_brand AS b', 'p.bid=b.bid', array('b.name as bname'));
		
		if($name_keyword){
			$select->where('p.name LIKE ?', '%'.$name_keyword.'%');
			$select2->where('p.name LIKE ?', '%'.$name_keyword.'%');
		}
		
		if($brand){
			$select->where('p.bid=?', $brand);
			$select2->where('p.bid=?', $brand);
		}
		
		if($os){
			$select->where('p.oid=?', $os);
			$select2->where('p.oid=?', $os);
		}
		
		$select->limitPage($page, $pageSize);
		$rs = parent::squery($select, $this->rdb);
		$results = $rs->fetchAll();
		
		$rs = parent::squery($select2, $this->rdb);
		$row = $rs->fetch();
		$count = $row['count'];
		
		$return = array(
			'rows'=>$results,
			'count'=>$count
		);
		
		return $return;
	}
}