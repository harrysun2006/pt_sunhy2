<?php

/**
 * 商户相关
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Business_Venue extends Better_DAO_Base
{

	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		$this->tbl = BETTER_DB_TBL_PREFIX.'shopkeeper_require';
		$this->orderKey = 'id';	
		$this->priKey = 'id';	
	}

	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
			$db = parent::registerDbConnection('business_server');
			self::$instance->_setAdapter($db);	
			self::$instance->setDb($db);
		}		
		return self::$instance;
	}
	
	public function getVenue($params){
		$id = $params['id'];
		$poi_id = $params['poi_id'];
		$uid = $params['uid'];		
		
		if($id>0){
			$sql = "id=".$id;
		} else {
			$sql = " poi_id=".$poi_id ." and uid=".$uid." order by dateline desc limit 1";
		}
		$sqlwhat = "select * from ".$this->tbl." where ".$sql;
		$rs = self::squery($sqlwhat, $this->rdb);		
		$data = $rs->fetch();	
		
		return $data;
	}
	
	
}