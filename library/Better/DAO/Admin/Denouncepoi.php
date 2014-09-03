<?php

/**
 * denounce 处理类
 * @author yanglei
 *
 */
class Better_DAO_Admin_Denouncepoi extends Better_DAO_Admin_Base{
	
		private static $instance = null;
	
		public function __construct()
		{
			$this->tbl = BETTER_DB_TBL_PREFIX.'poi_report';
			$this->priKey = 'id';
			$this->orderKey = 'report_time';
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
	
	public function getAllDenounces($params){
		
		$results = array();
		$page = $params['page'] ? intval($params['page']) : 1;
		$page_size = $params['page_size']? $params['page_size'] : BETTER_PAGE_SIZE;
		$keyword = $params['keyword']? trim($params['keyword']): '';
		$reason = $params['reason'] ? trim($params['reason']) : '';
		$reload = $params['reload'] ? intval($params['reload']) : 0;
		$from = $params['from'] ? (int) $params['from'] : 0;
		$to = $params['to'] ? (int) $params['to'] : 0;
		$act_result = isset($params['act_result'])? $params['act_result']: '';
		$status = $params['status']? $params['status']:'';
		
		$rdb = $this->rdb;
		$select = $rdb->select();
		$select2 = clone $select;
		
		$select->from($this->tbl.' AS d', '*');
		$select2->from($this->tbl.' AS d', array(new Zend_Db_Expr("COUNT(d.id) AS count")));
		
		if ($from>0) {
			$select->where('d.report_time>=?', $from);
			$select2->where('d.report_time>=?', $from);
		}
		
		if ($to>0) {
			$select->where('d.report_time<=?', $to);
			$select2->where('d.report_time<=?', $to);
		}
		
		if ($keyword!='') {
			$select->where($rdb->quoteInto('d.poi_id=?', $keyword));
			$select2->where($rdb->quoteInto('d.poi_id=?', $keyword));
		}
		
		if($reason){
			$select->where($rdb->quoteInto('d.uid=?', $reason));
			$select2->where($rdb->quoteInto('d.uid=?', $reason));			
		}
		
		if($status){
			$select->where($rdb->quoteInto('d.status=?', $status));
			$select2->where($rdb->quoteInto('d.status=?', $status));	
		}
		
		$select->limitPage($page, $page_size);
		
		$rs = parent::squery($select2, $rdb);
		$results2 = $rs->fetch();
		
		$results['count'] = $results2['count'];
		
		$select->order('d.report_time DESC');
		$sql=$select->__toString();
			
		$results['rows'] = $rdb->fetchAll($sql);
			
		return $results;
		
	}
	
	
	public function changeStatus($id, $status)
	{
		$result = 0;
			
		if($id && $status){
			$this->update(array('status'=>$status), $id) && $result = 1;
		}
		
		if($result){
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('更改举报POI状态为：<br>'.$status, 'change_status');
		}
		
		return $result;
	}
	
}

?>