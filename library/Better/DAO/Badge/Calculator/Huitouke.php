<?php

/**
 * 回头客
 * 你有3个月都在同一地点出现过
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Huitouke extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);

		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		
		$sql = "SELECT COUNT(DISTINCT (CONCAT( CAST(year( from_unixtime( `checkin_time` ) ) AS CHAR) , CAST(month( from_unixtime( `checkin_time` ) ) AS CHAR) ) )) AS `total`FROM ".BETTER_DB_TBL_PREFIX."user_place_log WHERE uid='".$uid."' AND checkin_time>(UNIX_TIMESTAMP()-3600*24*94) AND checkin_score>0  AND poi_id='".$poiId."'";

		Better_Log::getInstance()->logInfo(__CLASS__.':['.$sql.']', 'badge_sql');
		$rs = self::squery($sql, $rdb);
		$row = $rs->fetch();
		
		if ($row['total']>=3) {
			$result = true;
		}
		
		return $result;
	}
}