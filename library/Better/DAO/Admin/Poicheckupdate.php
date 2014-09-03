<?php

class Better_DAO_Admin_Poicheckupdate extends Better_DAO_Admin_Base
{
  	private static $instance = null;
  
 	/**
   	*
    */
    public function __construct($identifier=0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'admin_poi_check_update';
    	$this->priKey = 'id';
    	$this->orderKey = 'dateline';
    	
		parent::__construct($identifier);
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
	
	public function getAll($page=1)
	{
		$rdb = $this->rdb;
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'poi_check_update AS pcu', '*');
		$select->where('checked=0');
		$select->order('dateline DESC');
		$select->limitPage($page, BETTER_PAGE_SIZE);
		$sql=$select->__toString();
		$rows = $rdb->fetchAll($sql);
		
		$select1 = $rdb->select();
		$select1->from(BETTER_DB_TBL_PREFIX.'poi_check_update AS pcu', 'count(*) as countrow');
		$select1->where('checked=0');
		$sql=$select1->__toString();
		$count= $rdb->fetchRow($sql);
		$count = $count['countrow'];
		$results=array("rows"=>$rows,
							"count"=>$count);
		
		return $results;
	}
	public function getPoisBySearch($params=null)
	{
		if(empty($params)){
			return $this->getAll();
		}else{
			$rdb = $this->rdb;
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'poi_check_update AS pcu', '*');
			$select->where('checked=0 ');
			if($params['poi_id']){
				$select->where('poi_id='.$params['poi_id']);
			}
			if($params['from']){
				$select->where('dateline>'.$params['from']);
			}
			if($params['to']){
				$select->where('dateline<'.$params['to']);
			}
			$select->order('dateline DESC');
			$page = $params['page']?$params['page']:1;
			$page_size = $params['page_size']?$params['page_size']:BETTER_PAGE_SIZE;
			$select->limitPage($page, $page_size);
			$sql=$select->__toString();
			$rows = $rdb->fetchAll($sql);
			
			$select1 = $rdb->select();
			$select1->from(BETTER_DB_TBL_PREFIX.'poi_check_update AS pcu', 'count(*) as countrow');
			$select1->where('checked=0');
			if($params['poi_id']){
				$select1->where('poi_id='.$params['poi_id']);
			}
			if($params['from']){
				$select1->where('dateline>'.$params['from']);
			}
			if($params['to']){
				$select1->where('dateline<'.$params['to']);
			}
			$sql=$select1->__toString();
			$count= $rdb->fetchRow($sql);
			$count = $count['countrow'];
			$results=array("rows"=>$rows,
								"count"=>$count);
			return $results;
		}
	}
	public function getById($id)
	{
		$rdb = $this->rdb;
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'poi_check_update AS pcu', '*');
		$select->where('id='.$id);
		$sql=$select->__toString();
		$results = $rdb->fetchRow($sql);
		return $results;
	}
	public function updateCheckPOI($id,$status)
	{
		$rdb = $this->rdb;
		$table =BETTER_DB_TBL_PREFIX.'poi_check_update';
		$where = $rdb->quoteInto('id = ?', $id);
		$set = array(
		    'checked' =>$status
		);		
		$rows_affected = $rdb->update($table, $set, $where);
		$result = $rows_affected?1:0;
		if($status==2&&$result){
			$result=2;
		}
		return $result;
	}
	public function	deleteCheckPOI($id)
	{
		$rdb = $this->rdb;
		$table =BETTER_DB_TBL_PREFIX.'poi_check_update';
		$where = $rdb->quoteInto('id = ?', $id);		
		$rows_affected = $rdb->delete($table, $where);
		return $rows_affected;
	}
}
