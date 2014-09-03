<?php

/**
 * WLAN同步信息
 * 
 * @package Better.DAO
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Invite extends Better_DAO_Base
{
	private static $instance = null;
	
	
	
	
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
	
	public static function  topInvitation($params)
	{
		$begtm = isset($params['begtm'])?$params['begtm']:0;
		$nums = isset($params['nums'])?$params['nums']:4;	
		$return = array(
			'total' => 0,
			'rows' => array()
			);		
		$results = array();
		$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
		foreach($sids as $sid) {
			$cs = parent::assignDbConnection('user_server_'.$sid);
			$rdb = &$cs['r'];
			
			$sql = "select ref_uid as uid,from_unixtime(min(regtime)) as first_time,from_unixtime(max(regtime)) as last_time,count(distinct uid) as  number from better_profile
left join better_account  using(uid)
where ref_uid!=0 and regtime>".$begtm."
group by ref_uid
order by number desc limit 10";	
			
			$rs = self::squery($sql, $rdb);
			$rows = $rs->fetchAll();
			foreach($rows as $v) {
				$results[$v['uid']]['number'] = $results[$v['uid']]['number']+$v['number'];
				$results[$v['uid']]['uid'] = $v['uid'];				
			}
		}
		
		//	取出合并后的limit条数据
		if (count($results)>0) {
			$return['total'] = count($results);		
			foreach ($results as $key => $row) {			         
			   $accuracy[$key] = $row['number'];
			}		
			
			array_multisort($accuracy, SORT_DESC,$results);
									
			$return['rows'] =$results;		
		}

		return $return;		
	}
	
}