<?php

/**
4人及以上同时签到以下（见附件列表）POI 
 
上线时间
 7月15日即时
 
下线时间
 8月16日0:00am
 

 

 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Mototuanzaiyiqi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(6, 0, 0, 7, 15, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 15, 2011);
		$poilist = array(123048,19086204,19078497,124078,19081631,19086206,126708,19086207,19049615,10420547,761354,19085976,556727,19086211,19086212,11394149,19086214,19085387,19086216,10423482);		
		$now = time();	
	
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
			$sql = "select count(*) as total from better_user_place_log where poi_id=".$poiId." and checkin_time>=".$begtm." and checkin_time<=".$endtm." and checkin_score>0 and uid!=".$uid." group by uid";	
			$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
			foreach($sids as $sid) {
				$cs = Better_DAO_Base::assignDbConnection('user_server_'.$sid);				
				$rdb = &$cs['r'];
				$wdb = &$cs['w'];					
				$rs = Better_DAO_Base::squery($sql, $rdb);			
				$data = $rs->fetchAll();
				$total = $total + count($data);			
			}			
			if($total>=4){
				$result = true;
			}
			
		}			
		
		return $result;
	}
	
	
	
}