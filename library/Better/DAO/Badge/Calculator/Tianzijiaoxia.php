<?php

/**
 * 天子脚下
 * 在西单、三里屯、航天桥、亚运村、朝外签到过
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Tianzijiaoxia extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$range = '10847541,4063262,10846046,4062381|10854456,4066897,10853378,4065981|10853497,4065802,10840907,4064787|10850274,4072660,10850949,4073465|10851924,4065175,10853409,4065529';
		
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$badgeLoose = Better_Config::getAppConfig()->badge->spec_loose;
		
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
				
				$add = "MBRWithin(GeomFromText('POINT({$x} {$y})'), GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')) AND ";
				$rgs[] = "(".$add." MBRWithin(`xy`, GeomFromText('Polygon(({$x1} {$y2}, {$x1} {$y1}, {$x2} {$y1}, {$x2} {$y2}, {$x1} {$y2}))')))";
			}
			
			$sql .= ' AND ('.implode(' OR ', $rgs).')';	
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$sql.']', 'badge_sql');
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
					Better_Log::getInstance()->logInfo(__CLASS__.':['.$sql.']', 'badge_sql');
					
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