<?php

/**
 * 淮海路勋章
 * 在淮海路创建过3个及以上地点
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Huaihailu extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$range = '11320868,3065066,11320365,3065234';
		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		
		$rgs = array();
		$ranges = explode('|', $range);
		foreach ($ranges as $v) {
			list($x1, $y1, $x2, $y2) = explode(',', $v);
			
			$rgs[] = " MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))";
		}				
		$where = new Zend_Db_Expr(implode(' OR ', $rgs));
		
		$db = parent::registerDbConnection('poi_server');
		
		$sql = "SELECT COUNT(*) AS total FROM ".BETTER_DB_TBL_PREFIX."poi WHERE creator='".$uid."' AND (".implode(' OR ', $rgs).")";
		
		Better_Log::getInstance()->logInfo(__CLASS__.':['.$sql.']', 'badge_sql');
		$rs = self::squery($sql, $db);
		$row = $rs->fetch();
		
		if ($row['total']>=3) {
			$result = true;
		}

		return $result;
	}	
}