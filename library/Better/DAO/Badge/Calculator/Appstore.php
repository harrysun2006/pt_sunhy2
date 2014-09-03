<?php

/**
 * AppStore
 * 请帮忙以浦东Apple店为中心，半径5公里，时间是7月10日至23日。凡是在此时间地点范围内签到的用户，都可以获得如附件的苹果勋章。
 * POI   http://www.k.ai/poi?id=151997 
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Appstore extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		return false;
		
		parent::touch($params);
		$result = false;
		$config = Better_Config::getAppConfig();
		$range = $config->app_store->badge->range;
		
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$start = gmmktime(16, 0, 0, 7, 10, 2010);
		$end = gmmktime(16, 0, 0, 7, 23, 2010);
		$now = time();		
		
		if ($now<=$end && $now>=$start && $poiId==$config->app_store->badge->poi_id && $x && $y) {
			$db = parent::registerDbConnection('poi_server');
	
			$select = $db->select();
			$select->from(BETTER_DB_TBL_PREFIX.'poi', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));
			$select->where('poi_id=?', $poiId);
			
			$sql = "SELECT COUNT(*) AS total FROM ".BETTER_DB_TBL_PREFIX."poi WHERE poi_id='".$poiId."' ";
	
			$ranges = explode('|', $range);
			
			foreach ($ranges as $v) {
				list($x1, $y1, $x2, $y2) = explode(',', $v);
				
				$add = $badgeLoose ? '' : "MBRWithin(GeomFromText('POINT({$x} {$y})'), GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')) AND ";
				$rgs[] = "(".$add." MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')))";
			}
			
			$sql .= ' AND ('.implode(' OR ', $rgs).')';	
			
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$sql.']', 'badge_sql');
			$rs = self::squery($sql, $db);
			$row = $rs->fetch();
			
			if ($row['total']>0) {
				$result = true;
			}
		}
		
		return $result;
	}
}