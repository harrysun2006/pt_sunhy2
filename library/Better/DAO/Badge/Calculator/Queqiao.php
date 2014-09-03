<?php

/**
 * 鹊桥
 * 8月14日10：00至8月16日24:00，在乌鹊桥为中心的半径10km签到都能拿到
 * http://www.k.ai/poi?id=130174
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Queqiao extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$range = '11236310,3079240,11251066,3069578';
		
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$start = gmmktime(2, 0, 0, 8, 14, 2010);
		$end = gmmktime(16, 0, 0, 8, 16, 2010);
		$now = time();
		
		if ($now<=$end && $now>=$start && $x && $y) {
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
			
			if ($row['total']>=1) {
				$result = true;
			}
		}
		
		return $result;
	}
	
}