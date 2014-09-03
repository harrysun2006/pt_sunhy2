<?php

/**
 * 网站Banner
 *
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Webbanner extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'webbanner';
		$this->priKey = 'id';
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
	
	
	
	public function getAll(array $params)
	{
		$results = array('count'=>0, 'rows'=>array());			
		$page = $params['page'] ? intval($params['page']) : 1;	
		$page = $page-1;		
		$pageSize = $params['pageSize'] ? intval($params['pageSize']) : BETTER_PAGE_SIZE;
		$checked = trim($params['checked']) ? trim($params['checked']) : '';
		$what = "1";
		if($checked){
			$what .= " and checked=".$checked;
		}
		$select = "select * from ".$this->tbl." where ".$what."  order by rank ASC,checked desc, id desc limit ".$page*$pageSize.",".$pageSize;	
		$rs = self::squery($select, $this->rdb);	
		
		$row = $rs->fetchAll();	
		$selectcount = "select count(*) as t_count from ".$this->tbl." where ".$what;
		
		$rscount = self::squery($selectcount, $this->rdb);
		$rowcount = $rscount->fetch();	
		$result['count'] = $rowcount['t_count'];		
		$result['rows'] = $row;		
		return $result;
	}
	
	public function getMaxid()
	{
		$sql = "select max(id) as max_id from ".$this->tbl;
		$rs = self::squery($sql, $this->rdb);			
		$row = $rs->fetch();
		return $row['max_id'];
	}
}