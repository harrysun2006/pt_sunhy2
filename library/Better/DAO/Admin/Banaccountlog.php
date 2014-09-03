<?php

class Better_DAO_Admin_Banaccountlog extends Better_DAO_Admin_Base
{
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'admin_banaccount_log';
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
		$admin_uid = $params['admin_uid'] ? trim($params['admin_uid']) : '';
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$uid = $params['uid'] ? $params['uid'] : '';
		
		$select = $this->rdb->select();
		$select2 = clone $select;
		
		$select->from($this->tbl.' AS l', array(new Zend_Db_Expr("COUNT(l.id) AS count")));
		$select2->from($this->tbl.' AS l');
		
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
		
		if ($admin_uid!='') {
			$select->where($this->rdb->quoteInto('l.admin_uid LIKE ?', '%'.$admin_uid.'%'));
			$select2->where($this->rdb->quoteInto('l.admin_uid LIKE ?', '%'.$admin_uid.'%'));
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
	
	
	
	/**
	 * 得到用户被封号或禁言之前的状态
	 */
	public function getOldState($uid, $now_state=Better_User_State::BANNED){
		
		$select = $this->rdb->select();
		$select->from($this->tbl.' AS a', 'a.old_state');
		$select->where($this->rdb->quoteInto('a.uid=?', $uid));
		$select->where($this->rdb->quoteInto('a.now_state=?', $now_state));
		$select->where($this->rdb->quoteInto('a.old_state!=?', Better_User_State::BANNED));
		$select->order('a.dateline DESC');
		$select->limit(1);
		$rs = parent::squery($select, $this->rdb);
		$result = $rs->fetchAll();
		
		$old_state='';
		if($result){
			$old_state = $result[0]['old_state'];
		}
		return $old_state;
	}
	
	
}
