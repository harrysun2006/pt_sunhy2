<?php

/**
 * TOP100 DJs
 * 签到以下任意POI+至少2个第三方同步 
 * 3月5日2:00am
愚公移山（张自忠路店）http://k.ai/poi/4364267
DJ Mag
 *

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Topyibaidj extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$end = gmmktime(18, 0, 0, 3, 4, 2011);
		$poi_id = 4364267;
		$now = time();		
		if($now<=$end && $poi_id==$poiId){					
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);		
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=2) {				
					$result = true;				
			}
			
		}
		return $result;
	}
}