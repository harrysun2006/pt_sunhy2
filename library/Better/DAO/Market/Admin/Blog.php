<?php

class Better_DAO_Market_Admin_Blog extends Better_DAO_Base
{
	
	private static $instance = array();

 	/**
   	*
    */
    public function __construct($identifier = 0)
    {
		$this->tbl = BETTER_DB_TBL_PREFIX.'market_blog';
		$this->priKey = 'bid';
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
	
	
	public static function getAllData($params)
	{	
		$poiId = $params['poi_id']? $params['poi_id']: 0;
		$page = $params['page']? $params['page'] : 1 ;
		$page_size = $params['page_size']? $params['page_size'] : 20 ;
		
		$status = $params['status']? $params['status'] : '';
		$mid = $params['market_uid']? $params['market_uid'] : 0;
		
		$dua = Better_DAO_User_Assign::getInstance();
		$servers = $dua->getServerIds();

		$results = array();
		$return = array('count'=>0, 'rows'=>array());
		foreach($servers as $_sid) {
			$cs = parent::assignDbConnection('user_server_'.$_sid);	
			$select = $cs['r']->select();
			$select->from(BETTER_DB_TBL_PREFIX.'blog as b', array('b.bid', 'b.uid', 'b.type', 'b.poi_id', 'b.message', 'b.dateline', 'b.attach', 'b.source'));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'market_blog as m', 'b.bid=m.bid', array('m.kai_checked', 'm.partner_checked'));
			$select->joinleft(BETTER_DB_TBL_PREFIX.'profile as p', 'b.uid=p.uid', array('p.username', 'p.nickname'));
			$select->where('b.poi_id=?', $poiId);
			$select->where('b.checked=?', 1);
			$select->where('b.priv=?', 'public');
			if($status){
				if($mid==1000){
					if($status=='has_check'){
						$select->where('m.kai_checked=?', 1);
					}else if($status=='not_check'){
						$select->where('m.kai_checked is null OR m.kai_checked=?', 0);
					}
				}else if($mid==2000){
					if($status=='has_check'){
						$select->where('m.partner_checked=?', 1);
					}else if($status=='not_check'){
						$select->where('m.partner_checked is null OR m.partner_checked=?', 0);
					}
				}
			}
			$rs = self::squery($select, $cs['r']);
			$rows = $rs->fetchAll();
			foreach ($rows as $row) {
				$results[$row['dateline'].$row['uid']] = $row;
			}
		}
		
		krsort($results);
		$return['count']= count($results);
		
		$tmp = array_chunk($results, $page_size);	
		$return['rows']=  $tmp[$page-1];
		
		return $return;
	}
	
	
}