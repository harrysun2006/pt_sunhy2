<?php

/**
 * 商户相关
 * 
 * @package Better.DAO.Poi
 * @author leip <leip@peptalk.cn>
 */

class Better_DAO_Business_Poireport extends Better_DAO_Base
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
	
	public function getcheckingender($params){	
		$poi_id = $params['poi_id'];
		$begtm =  $params['begtm'];
		$endtm =  $params['endtm'];
		$score = (isset($params['score']) && $params['score']!='') ? $params['score']:0 ;
		
		
		$sqlwhat = "select sum(nums) as nums,gender from better_poi_checkingender where poi_id=".$poi_id." and dateline>=".$begtm." and dateline<=".$endtm." and score=".$score." group by gender";
		$rs = self::squery($sqlwhat, $this->rdb);
		$data = $rs->fetchAll();	
		return $data;
	}
	
	public function getcheckindays($params){	
		$poi_id = $params['poi_id'];
		$begtm =  $params['begtm'];
		$endtm =  $params['endtm'];	
		$score = (isset($params['score']) && $params['score']!='') ? $params['score']:0 ;	
		$sqlwhat = "select nums,date_format(from_unixtime(dateline),'%c-%e') as tmhour from better_poi_checkindays where poi_id=".$poi_id." and dateline>=".$begtm." and dateline<=".$endtm ." and score=".$score." group by date_format(from_unixtime(dateline),'%c-%e')";
		$rs = self::squery($sqlwhat, $this->rdb);
		$data = $rs->fetchAll();	
		return $data;
	}
	
	public function getcheckinhours($params){
		$poi_id = $params['poi_id'];
		$begtm =  $params['begtm'];
		$endtm =  $params['endtm'];
		$score = (isset($params['score']) && $params['score']!='') ? $params['score']:0 ;	
		$sqlwhat = "select sum(nums) as nums,date_format(from_unixtime(dateline),'%H') as hours from better_poi_checkinhours where poi_id=".$poi_id." and dateline>=".$begtm." and dateline<=".$endtm." and score=".$score."  group by date_format(from_unixtime(dateline),'%H')";
		
		$rs = self::squery($sqlwhat, $this->rdb);
		$data = $rs->fetchAll();	
		return $data;
	}
	public function getpoisync($params){
		$poi_id = $params['poi_id'];
		$begtm =  $params['begtm'];
		$endtm =  $params['endtm'];
		$sqlwhat = "select sum(nums) as nums,protocol from better_poi_sync where poi_id=".$poi_id." and dateline>=".$begtm." and dateline<=".$endtm." group by protocol";
		$rs = self::squery($sqlwhat, $this->rdb);
		$data = $rs->fetchAll();	
		return $data;
	}
}