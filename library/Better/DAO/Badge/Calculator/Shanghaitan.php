<?php

/**
 * 上海滩勋章数据
 * 在五角场、锦江乐园、人民广场、城隍庙、正大广场签到过
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Shanghaitan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$range = '11326334,3074925,11327178,3074971|11317668,3060123,11315511,3056040|11322488,3066948,11322219,3066665|11323936,3066217,11323658,3066444|11324951,3067850,11324255,3067181';
		
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		if ($x && $y) {
			$db = parent::registerDbConnection('poi_server');
			$sql = "SELECT COUNT(*) AS total FROM ".BETTER_DB_TBL_PREFIX."poi WHERE poi_id='".$poiId."' ";
			$ranges = explode('|', $range);
			
			foreach ($ranges as $v) {
				list($x1, $y1, $x2, $y2) = explode(',', $v);
				
				$add = "MBRWithin(GeomFromText('POINT({$x} {$y})'), GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')) AND ";
				$add = '';
				$rgs[] = "(".$add." MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')))";
			}
			
			$sql .= ' AND ('.implode(' OR ', $rgs).')';	

			$rs = self::squery($sql, $db);
			$row = $rs->fetch();
			
			if ($row['total']>0) {
				$uDb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
				$cnt = 0;
				
				foreach ($ranges as $v) {
					list($x1, $y1, $x2, $y2) = explode(',', $v);
					
					$sql = "SELECT COUNT(*) AS total
					FROM ".BETTER_DB_TBL_PREFIX."user_place_log 
					WHERE uid='".$uid."' AND MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))'))
					";
					
					$rs = self::squery($sql, $uDb);
					$row = $rs->fetch();
					
					if ($row['total']<=0) {
						break;
					} else {
						$cnt++;
					}					
				}
				
				$result = $cnt==count($ranges) ? true : false;
			}
		}
		
		return $result;
	}
}