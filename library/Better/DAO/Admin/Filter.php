<?php

class Better_DAO_Admin_Filter extends Better_DAO_Admin_Base
{
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'admin_filterwordlog';
    	$this->priKey = 'id';
    	$this->orderKey = 'createtime';
    	
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
		$user_keyword = $params['user_keyword'] ? trim($params['user_keyword']) : '';
		$act_type = $params['act_type'] ? trim($params['act_type']) : '';
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$word_type = $params['word_type'] ? trim($params['word_type']) : '';
		$need_check = $params['need_check'] ? trim($params['need_check']) : '';
		$uid = $params['uid'] ?$params['uid'] :0;
		$select = $this->rdb->select();

		if ($from>0) {
			$select->where('l.createtime>=?', $from);
		}

		if ($to>0) {
			$select->where('l.createtime<=?', $to);
		}
		
		if ($keyword!='') {
			$select->where($this->rdb->quoteInto('l.reftext LIKE ?', '%'.$keyword.'%'));
		}
			
		if ($act_type!='') {
			$select->where($this->rdb->quoteInto('l.type=?', $act_type));
		}
		
		if($word_type!=''){
			$select->where($this->rdb->quoteInto('l.word_type LIKE ?', '%'.$word_type.'%'));
		}
		
		if($need_check==1){
			$select->where('l.need_check=?', $need_check);
		}
		
		if($uid>0){
			$select->where($this->rdb->quoteInto('l.uid=?',$uid));
		}	
		if ($user_keyword!='') {
			$select->where($this->rdb->quoteInto('l.username LIKE ?', '%'.$user_keyword.'%'));
		}
		
		$select->where('l.flag=?', 1);
		
		$select->where('l.uid!=?', 10000);
		
		/*if($act_type=='userinfo'){
			$select3 = clone $select;
			$select3->from($this->tbl.' AS l', array(new Zend_Db_Expr('MAX(l.createtime) as time')));
			$select3->group('l.bid');
			$rs = parent::squery($select3, $this->rdb);
			$row3 = $rs->fetchAll();
			foreach ($row3 as $v){
				$timeArr[] = "'".$v['time']."'";
			}
			$timeString = implode(',', $timeArr);
			
			$select->where('l.createtime in ('.$timeString.')');
		}*/
		
		$select2 = clone $select;
		$select->from($this->tbl.' AS l', array(new Zend_Db_Expr("COUNT(l.id) AS count")));
		
		$rs = parent::squery($select, $this->rdb);
		$row = $rs->fetch();
		$count = $row['count'];
			
		$select2->from($this->tbl.' AS l');
		$select2->order('l.createtime DESC');
		$select2->limitPage($page, $pageSize);

		$rs = parent::squery($select2, $this->rdb);
		$results = $rs->fetchAll();

		return array(
			'rows' => $results,
			'count' => $count
			);		
	}
}