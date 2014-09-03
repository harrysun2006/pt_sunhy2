<?php

class Better_DAO_Admin_Tracelog extends Better_DAO_Admin_Base
{
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'admin_tracelog';
    	$this->priKey = 'id';
    	$this->orderKey = 'postdate';
    	
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
	
	public function getAll(array $params)
	{
		$results = $data = array();

		$page = $params['page'] ? intval($params['page']) : 1;
		$from = $params['from'] ? (int) $params['from'] : 0;
		$to = $params['to'] ? (int) $params['to'] : 0;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$user_id = $params['user_id'] ? trim($params['user_id']) : '';
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$act = $params['action'] ? $params['action'] : '';

		$select = $this->rdb->select();
		
		if($act){
			$select->where('l.action=?', $act);
		}

		if ($from>0) {
			$select->where('l.postdate>=?', $from);
		}

		if ($to>0) {
			$select->where('l.postdate<=?', $to);
		}
				
		if($user_id){
			$select->where('l.uid=?', $user_id);
		}
		
		if ($keyword!='') {
			$select->where($this->rdb->quoteInto('l.data LIKE ?', '%'.$keyword.'%'));
		}


		$select2 = clone $select;
		$select->from($this->tbl.' AS l', array(new Zend_Db_Expr("COUNT(l.id) AS count")));

		$rs = parent::squery($select, $this->rdb);
		$row = $rs->fetch();
		$count = $row['count'];
			
		$select2->from($this->tbl.' AS l');
		$select2->order('l.postdate DESC');
		$select2->limitPage($page, $pageSize);

		$rs = parent::squery($select2, $this->rdb);
		$results = $rs->fetchAll();

		return array(
			'rows' => $results,
			'count' => $count
			);		
	}
	/**
	 * 重写insert 方法 insert的内容不在写到数据库中去，而是写到文件中去
	 * @param array $params
	 */
	public function insert(array $params = array())
	{
		$msg='';
		foreach($params as $key=>$value){
			$msg .= $key.'=>'.$value.';  ';
		}
		Better_Log::getInstance()->log($msg,'user_trace');
	}
}