<?php

/**
 * 徐家汇
 * 你在徐家汇签到过
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Xujiahui extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$range = '11318801,3063650,11318110,3061252';
		
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		if ($x && $y) {
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