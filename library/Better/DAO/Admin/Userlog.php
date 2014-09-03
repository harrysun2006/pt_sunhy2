<?php

class Better_DAO_Admin_Userlog extends Better_DAO_Admin_Base
{
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'admin_user_log';
    	$this->priKey = 'id';
    	$this->orderKey = 'dateline';
    	
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
		$uid = $params['uid'] ? $params['uid'] : '';
		
		$select = $this->rdb->select();
		$select2 = clone $select;
		
		$select->from($this->tbl.' AS l', array(new Zend_Db_Expr("COUNT(l.id) AS count")));
		$select2->from($this->tbl.' AS l');
		
		$select->join(BETTER_DB_TBL_PREFIX.'administrators AS a', 'a.uid=l.admin_uid', array('a.username AS ausername'));
		$select2->join(BETTER_DB_TBL_PREFIX.'administrators AS a', 'a.uid=l.admin_uid', array('a.username AS ausername'));
		
		if($uid){
			$select->where('l.uid LIKE ?', '%'.$uid.'%');
			$select2->where('l.uid LIKE ?', '%'.$uid.'%');
		}
		
		if ($from>0) {
			$select->where('l.dateline>=?', $from);
			$select2->where('l.dateline>=?', $from);
		}

		if ($to>0) {
			$select->where('l.dateline<=?', $to);
			$select2->where('l.dateline<=?', $to);
		}

		if ($act_type!='') {
			if($act_type=='reset_dmsg'){
				$select->where($this->rdb->quoteInto('l.act_type=?', 'del_received_dmsg').' OR '.$this->rdb->quoteInto('l.act_type=?', 'del_sended_dmsg'));
				$select2->where($this->rdb->quoteInto('l.act_type=?', 'del_received_dmsg').' OR '.$this->rdb->quoteInto('l.act_type=?', 'del_sended_dmsg'));
			}else{
				$select->where($this->rdb->quoteInto('l.act_type=?', $act_type));
				$select2->where($this->rdb->quoteInto('l.act_type=?', $act_type));
			}
			
		}
		
		if ($user_keyword!='') {
			$select->where($this->rdb->quoteInto('a.username LIKE ?', '%'.$user_keyword.'%'));
			$select2->where($this->rdb->quoteInto('a.username LIKE ?', '%'.$user_keyword.'%'));
		}
		
		if ($keyword!='') {
			$select->where($this->rdb->quoteInto('l.content LIKE ?', '%'.$keyword.'%'));
			$select2->where($this->rdb->quoteInto('l.content LIKE ?', '%'.$keyword.'%'));
		}
		

		$rs = parent::squery($select, $this->rdb);
		$row = $rs->fetch();
		$count = $row['count'];
			
		
		$select2->order('l.dateline DESC');
		$select2->limitPage($page, $pageSize);
			
		$rs = parent::squery($select2, $this->rdb);
		$results = $rs->fetchAll();

		return array(
			'rows' => $results,
			'count' => $count
			);		
	}
}
