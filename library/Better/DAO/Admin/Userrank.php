<?php

class Better_DAO_Admin_Userrank extends Better_DAO_Admin_Base
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

		$cacheKey = $params['cacheKey'] ? $params['cacheKey'] : '';
		$cacheKey = self::$cachePrefix.$cacheKey;
		$page = $params['page'] ? intval($params['page']) : 1;
		$user_keyword = $params['user_keyword'] ? trim($params['user_keyword']) : '';
		$act_type = $params['act_type'] ? trim($params['act_type']) : '';
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;

		$select = $this->rdb->select();
		
		$select->from($this->tbl.' AS l', array(new Zend_Db_Expr("COUNT(l.uid) AS count"), 'l.act_type', 'l.uid', 'l.username'));

		if ($act_type!='') {
			$select->where($this->rdb->quoteInto('l.act_type=?', $act_type));
		}
		
		if ($user_keyword!='') {
			$select->where($this->rdb->quoteInto('l.username LIKE ?', '%'.$user_keyword.'%')." OR ".$this->rdb->quoteInto('l.uid LIKE ?', '%'.$user_keyword.'%'));
		}
		
		
		$select->group('l.act_type');
		$select->group('l.uid');
		$select->order('count DESC');
		
		$rs = $this->rdb->query($select);
		$results = $rs->fetchAll();

		return 	$results;
	}
}
