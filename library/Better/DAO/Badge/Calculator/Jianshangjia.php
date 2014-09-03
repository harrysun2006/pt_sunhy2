<?php

/**
 * 鉴赏家
 * 你在6家博物馆/画廊签到过
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Jianshangjia extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$flag = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$poiId = (int)$params['poi_id'];
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		$keys = array('博物馆', '画廊');
		
		if ($poiInfo['certified']==1) {
			foreach ($keys as $key) {
				if (preg_match('/'.$key.'/', $poiInfo['name']) || preg_match('/'.$key.'/', $poiInfo['label'])) {
					$flag = true;
					break;
				}
			}
		} else {
			foreach ($keys as $key) {
				if (preg_match('/'.$key.'/', $poiInfo['label'])) {
					$flag = true;
					break;
				}
			}			
		}
		
		if ($flag==true) {
			$poiIds = self::getCheckinedPoiIds($uid);
			$db = parent::registerDbConnection('poi_server');
			
			$sql = "SELECT COUNT(*) AS total FROM ".BETTER_DB_TBL_PREFIX."poi WHERE poi_id IN ('".implode("','", $poiIds)."') AND (((`label` LIKE '%博物馆%' OR `label` LIKE '%画廊%') AND `certified`='0') OR (((`label` LIKE '%博物馆%' OR `label` LIKE '%画廊%') OR (`name` LIKE '%博物馆%' OR `name` LIKE '%画廊%')) AND `certified`=1))";
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$sql.']', 'badge_sql');
			$rs = self::squery($sql, $db);
			$row = $rs->fetch();
			
			if ($row['total']>=6) {
				$result = true;
			}
		}
		
		return $result;
	}
}