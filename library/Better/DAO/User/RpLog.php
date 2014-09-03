<?php

/**
 * 用户RP值变化记录
 *
 * @package Better.DAO.User
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_User_RpLog extends Better_DAO_Base
{
  
  	private static $instance = array();
  
 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
    	$this->tbl = BETTER_DB_TBL_PREFIX.'user_rp_log';
    	$this->priKey = 'id';
    	$this->orderKey = &$this->priKey;
    	
		parent::__construct ($identifier);
		
		$this->assignUserDbConnection();
	}
	
  	public static function getInstance($identifier=0)
	{
		if (!isset(self::$instance[$identifier]) || self::$instance[$identifier]==null) {
			self::$instance[$identifier] = new self($identifier);
		}
		
		return self::$instance[$identifier];
	}
	public function getUserRp(array $params=array())
	{
		
		$results = array('count'=>0, 'rows'=>array());	
		

		$page = $params['page'] ? intval($params['page']) : 1;	
		$page = $page-1;		
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$uid = $params['uid'];
		//Better_Log::getInstance()->logInfo($pageSize,'xxsql');	
		$select = "select * from ".$this->tbl." where uid='".$uid."' order by  id desc limit ".$page.",".$pageSize;			
		$rs = self::squery($select, $this->rdb);
		
		$row = $rs->fetchAll();	
		$selectcount = "select count(*) as t_count from ".$this->tbl." where uid='".$uid."'";
		$rscount = self::squery($selectcount, $this->rdb);
		$rowcount = $rscount->fetch();	
		$result['count'] = $rowcount['t_count'];		
		$result['rows'] = $row;		
		return $result;
	}
}