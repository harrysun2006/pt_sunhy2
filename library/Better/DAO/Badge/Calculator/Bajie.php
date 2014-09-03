<?php

/**
 * 八戒
 * 你在20家餐厅签到过
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Bajie extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$flag = false;
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$poiId = (int)$params['poi_id'];
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		$key = '餐厅';
		
		if ($poiInfo['certified']==1 && (preg_match('/'.$key.'/', $poiInfo['name']) || preg_match('/'.$key.'/', $poiInfo['label']))) {
			$flag = true;
		} else if ($poiInfo['certified']==0 && preg_match('/'.$key.'/', $poiInfo['label'])) {
			$flag = true;
		}				

		if ($flag==true) {
			$poiIds = self::getCheckinedPoiIds($uid);
			$db = parent::registerDbConnection('poi_server');
			
			$sql = "SELECT COUNT(*) AS total FROM ".BETTER_DB_TBL_PREFIX."poi WHERE poi_id IN ('".implode("','", $poiIds)."') AND ((`label` LIKE '%".$key."%' AND `certified`='0') OR ((`label` LIKE '%".$key."%' OR `name` LIKE '%".$key."%') AND `certified`=1))";
			
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$sql.']', 'badge_sql');
			$rs = self::squery($sql, $db);
			$row = $rs->fetch();
			
			if ($row['total']>=20) {
				$result = true;
			}
		}
		
		return $result;
	}
}