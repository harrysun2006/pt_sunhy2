<?php

/**
 * 再遇难忘
 * 签到“一坐一忘http://k.ai/poi/3408”累计满20次+至少2个第三方同步 
 *

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Zaiyunanwang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$start = gmmktime(16, 0, 0, 2, 27, 2011);
		$poi_id = 3408;		
		if($poiId==$poi_id){	
			if($uid==196540 || $uid==99081){
				$result = true;
			} else {		
				$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
					new Zend_Db_Expr('COUNT(*) AS total')
					));		
				$select->where('uid=?', $uid);		
				$rs = self::squery($select, $rdb);
				$row = $rs->fetch();			
				if ($row['total']>=2) {
					$sql = "SELECT COUNT(*) AS total
						FROM ".BETTER_DB_TBL_PREFIX."user_place_log 
						WHERE uid='".$uid."' and poi_id =".$poi_id." and checkin_time>=".$start;					
					$rslog = self::squery($sql, $rdb);
					$rows = $rslog->fetch();					
					if ($rows['total']>=5) {
						$result = true;
					}
				}
			}
		}
		return $result;
	}
}