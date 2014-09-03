<?php

/**
 * 计数统计
 *
 */

class Better_DAO_Counters extends Better_DAO_Admin_Base
{
	
	
	public static function getUserCounts(array $params=null)
	{

			$from = isset($params['from'])? $params['from']:'';
			$to = isset($params['to'])? $params['to']:'';
			$karma = isset($params['karma'])? $params['karma'] : 0 ;
			$treasures = isset($params['treasures'])? $params['treasures'] : 0 ;
			
			if ($from) {
			$y = substr($from, 0, 4);
			$m = substr($from, 5, 2);
			$d = substr($from, 8, 2);	
			$from = gmmktime(0, 0, 0, $m, $d, $y)-8*3600;
			}
		
			if ($to) {
			$y = substr($to, 0, 4);
			$m = substr($to, 5, 2);
			$d = substr($to, 8, 2);	
			$to = gmmktime(23, 59, 59, $m, $d, $y)-8*3600;
			}
			/*if($from){
				$from = strtotime($from.' 0:0:0')-8*3600;
			}
			if($to){
				$to = strtotime($to.' 23:59:59')-8*3600;
			}*/
			
			//die($from.' '.$to);
			
			$serverIds = parent::getServerIds();
			
			$usercount = 0;
			foreach ($serverIds as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'account as a',  array(new Zend_Db_Expr("COUNT(a.uid) AS count")));
				$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=a.uid', array('p.karma'));
				$select->join(BETTER_DB_TBL_PREFIX.'profile_counters as c', 'c.uid=p.uid', array('c.treasures'));
				
				if($from){
					$select->where('a.regtime>=?', $from);
				}
				
				if($to){
					$select->where('a.regtime<=?', $to);				
				}
				
				if($karma){
					$select->where('p.karma>=?', $karma);
				}
				
				if($treasures){
					$select->where('c.treasures>=?', $treasures);
				}
				
				$rs = parent::squery($select, $rdb);
				$row = $rs->fetch();
				$count = $row['count'];
				
				$usercount += (int)$count;
				
			}
			
			return $usercount;
			
	}
	
	
	public static function getBlogCounts($type='', $fromTime='', $toTime=''){
	
		if ($fromTime) {
			$from = $fromTime;
			$y = substr($from, 0, 4);
			$m = substr($from, 5, 2);
			$d = substr($from, 8, 2);	
			$from = gmmktime(0, 0, 0, $m, $d, $y)-8*3600;
			}
		
			if ($toTime) {
			$to = $toTime;
			$y = substr($to, 0, 4);
			$m = substr($to, 5, 2);
			$d = substr($to, 8, 2);	
			$to = gmmktime(23, 59, 59, $m, $d, $y)-8*3600;
			}
		
			$serverIds = parent::getServerIds();
			
			$blogcount = 0;
			foreach ($serverIds as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'blog as b',  array(new Zend_Db_Expr("COUNT(b.bid) AS count")));
				
				if($from){
					$select->where('b.dateline>=?', $from);
				}
				
				if($to){
					$select->where('b.dateline<=?', $to);				
				}
				
				if($type){
					$select->where($rdb->quoteInto('b.type=?', $type));					
				}
				
				$rs = parent::squery($select, $rdb);
				$row = $rs->fetch();
				$count = $row['count'];
				
				$blogcount += (int)$count;
				
			}
			
			return $blogcount;	
	}
	
	
	public static function getFrinedCounts($friends=10)
	{

			$serverIds = parent::getServerIds();
			
			$usercount = 0;
			foreach ($serverIds as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'friends as f',  array(new Zend_Db_Expr("COUNT(f.uid) AS count")));
				$select->group('f.uid');
				$select->having('count>=?', $friends);
				
				$rs = parent::squery($select, $rdb);
				$rows = $rs->fetchAll();
				$count = count($rows);
				
				$usercount += (int)$count;
				
			}
			
			return $usercount;
			
	}
	
	
	public static function getFromPartnerCounts(array $params=array()){
		
		$from = isset($params['from'])? $params['from']:'';
		$to = isset($params['to'])? $params['to']:'';
		
		if ($from) {
			$y = substr($from, 0, 4);
			$m = substr($from, 5, 2);
			$d = substr($from, 8, 2);	
			$from = gmmktime(0, 0, 0, $m, $d, $y)-8*3600;
		}
	
		if ($to) {
			$y = substr($to, 0, 4);
			$m = substr($to, 5, 2);
			$d = substr($to, 8, 2);	
			$to = gmmktime(23, 59, 59, $m, $d, $y)-8*3600;
		}
					
		$result =array();
		$rdb = parent::registerDbConnection('common_server');
				
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'from_partner as f',  array('f.partner_id', new Zend_Db_Expr("COUNT(f.partner_id) AS count")));
		if($from){
			$select->where('f.visit_time>=?', $from);
		}
		
		if($to){
			$select->where('f.visit_time<=?', $to);				
		}
		$select->group('f.partner_id');
				
		$rs = parent::squery($select, $rdb);
		$rows = $rs->fetchAll();
			
		foreach($rows as $row){
			$result[$row['partner_id']] = $row['count'];
		}
		return $result;
	}
	
	
	//注册来源
	public static function getPartnerCounts(array $params=array())
	{
			$from = isset($params['from'])? $params['from']:'';
			$to = isset($params['to'])? $params['to']:'';
			
			if ($from) {
				$y = substr($from, 0, 4);
				$m = substr($from, 5, 2);
				$d = substr($from, 8, 2);	
				$from = gmmktime(0, 0, 0, $m, $d, $y)-8*3600;
			}
		
			if ($to) {
				$y = substr($to, 0, 4);
				$m = substr($to, 5, 2);
				$d = substr($to, 8, 2);	
				$to = gmmktime(23, 59, 59, $m, $d, $y)-8*3600;
			}
			
			$serverIds = parent::getServerIds();
			
			$result =array();
			$tmp = array();
			foreach ($serverIds as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'account as a',  array('a.partner', new Zend_Db_Expr("COUNT(*) AS count")));
				$select->group('a.partner');
				if($from){
					$select->where('a.regtime>=?', $from);
				}
				
				if($to){
					$select->where('a.regtime<=?', $to);				
				}				
				
				$rs = parent::squery($select, $rdb);
				$rows = $rs->fetchAll();
				
				foreach($rows as $row){
					if(!$row['partner']){
						$row['partner'] = 'K.ai';
					}
					$tmp[$row['partner']][] = $row['count'];
				}
				
			}
			
			foreach($tmp as $key=>$val){
				$result[$key] = array_sum($val);
			}
			
			return $result;
			
	}
	
	
	
	public static function getPoiCounts($from='', $to=''){
		
			if ($from) {
			$y = substr($from, 0, 4);
			$m = substr($from, 5, 2);
			$d = substr($from, 8, 2);	
			$from = gmmktime(0, 0, 0, $m, $d, $y)-8*3600;
			}
		
			if ($to) {
			$y = substr($to, 0, 4);
			$m = substr($to, 5, 2);
			$d = substr($to, 8, 2);	
			$to = gmmktime(23, 59, 59, $m, $d, $y)-8*3600;
			}
		
		
		$rdb = parent::registerDbConnection('poi_server');
				
		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'poi as p',  array(new Zend_Db_Expr("COUNT(p.poi_id) AS count")));
		
		if($from){
			$select->where('p.create_time>=?', $from);
		}
		
		if($to){
			$select->where('p.create_time<=?', $to);
		}
				
		$rs = parent::squery($select, $rdb);
		$row = $rs->fetch();
		$count = $row['count'];	
		
		return $count;
	}
	
}