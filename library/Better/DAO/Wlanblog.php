<?php

/**
 * WLAN同步信息
 * 
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Wlanblog extends Better_DAO_Base
{
	private static $instance = null;
	const CHECK_FALSE = '-1';
	const NEED_CHECK = '0';
	const CHECK_SUCESS = '1';
	const SYNC_SUCESS = '2';	
	const SYNC_FALSE = '3';
	const SYNC_PARAMS = '4';
	const SYNC_BUSY = '5';
	
	
	
	public function __construct()
	{
		$this->tbl = BETTER_DB_TBL_PREFIX.'wlan_blog';
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
	
	public function getAll($params)
	{
		//$select = $this->rdb->select();
		$result = array();
		$status = $params['status'];
		$page = isset($params['page']) && $params['page']>0 ? $params['page']:1;
		$count_page = isset($params['page_size']) ? $params['page_size']:20;
		$sqlrows = "select * from better_wlan_blog where 1";
		$sqltotal = "select count(*) as t_count from better_wlan_blog where 1";
		if($status!='all'){
			$sqlrows .=" and status='".$status."'";
			$sqltotal .=" and status='".$status."'";			
		}
		$rstotal = self::squery($sqltotal, $this->rdb);	
		$totalrows = $rstotal->fetch();
		$total =  $totalrows['t_count'];
		$pages = $page-1;
		$sqlrows .=" order by posttm desc limit ".$pages*$count_page.",".$count_page; 
		$rs = self::squery($sqlrows, $this->rdb);
		$rows = $rs->fetchAll();
		$date = array();
		foreach($rows as $row){
			$row['status_name'] = $row['status'];
			switch ($row['status'])	{
				case 0:
					$row['status_name'] = '待审核';
					break;
				case -1:
					$row['status_name'] = '未通过审核';
					break;
				case 1:
					$row['status_name'] = '待同步';
					break;
				case 2:
					$row['status_name'] = '同步成功';
					break;
				case 3:
					$row['status_name'] = '参数不正确';
					break;
				case 4:
					$row['status_name'] = '参数不完整';
					break;
				case 5:
					$row['status_name'] = '系统忙';
					break;
			}	
			
			
			$date[] = $row;		
		}		
		$result['count'] = $total;
		$result['rows'] = &$date;
		$result['page'] = &$page;		
		return $result;		
		
	}
	public function getInfo($id)
	{
		$select = $this->rdb->select();
		$result = array();
		
		$sqlrows = "select * from better_wlan_blog where id=".$id;
		
		$rs = self::squery($sqlrows, $this->rdb);	
		$rows = $rs->fetch();	
				
		return $rows;		
		
	}
	public function update($params)
	{
		$result = false;
		$id = $params['id'];
		if($id>0){
			$result = parent::update($params, $id, 'AND','better_wlan_blog');				
		}
		return $result;	
	}
	
}