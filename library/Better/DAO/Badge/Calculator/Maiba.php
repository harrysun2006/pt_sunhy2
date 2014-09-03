<?php

/**
 * 麦霸
 * 你在3家卡拉OK签到过
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Maiba extends Better_DAO_Badge_Calculator_Base
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
		$keys = array('卡拉OK', '唱K');
		
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
			
			$sql = "SELECT COUNT(*) AS total FROM ".BETTER_DB_TBL_PREFIX."poi WHERE poi_id IN ('".implode("','", $poiIds)."') AND (((`label` LIKE '%卡拉OK%' OR `label` LIKE '%KTV%' OR `label` LIKE '%唱K%') AND `certified`=0) OR (((`label` LIKE '%卡拉OK%' OR `label` LIKE '%KTV%' OR `label` LIKE '%唱K%') OR ((`name` LIKE '%卡拉OK%' OR `name` LIKE '%KTV%' OR `name` LIKE '%唱K%')) AND `certified`=1)))";
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$sql.']', 'badge_sql');
			$rs = self::squery($sql, $db);
			$row = $rs->fetch();
			
			if ($row['total']>=3) {
				$result = true;
			}
		}
		
		return $result;
	}
}