<?php

/**
 * 夜店达人
 * 同一晚上（21:00以后）报到了4个及以上娱乐场所
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yediandaren extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$keywords = array();
		$keywords[] = '影院';
		$keywords[] = '电影院';
		$keywords[] = '剧院';
		$keywords[] = '剧场';
		$keywords[] = 'KTV';
		$keywords[] = 'K歌';
		$keywords[] = '夜总会';
		$keywords[] = '酒吧';
		$keywords[] = '桌游';
		$keywords[] = '桌球';
		$keywords[] = '台球';
		$keywords[] = '舞厅';
		$keywords[] = '迪斯科';
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$userInfo = $user->getUserInfo();
		$tz = $userInfo['timezone'];

		$time = time();
		$year = date('Y', $time);
		$month = date('m', $time);
		$day = date('d', $time);
		$hour = date('H', $time);
		$offset = $tz;
		$tzHour = $hour + $offset;
		$hourKey = 21;
		
		if ($tzHour>=21 || $tzHour<=3) {
			$from = gmmktime($hourKey-$offset, 0,0, $month, $day, $year);
			$to = $from + 6*3600;
			$poiIds = Better_DAO_User_PlaceLog::getInstance($uid)->getTonightCheckinedPoiIds($from, $to);
			
			if (count($poiIds)>0 && $poiIds[0]) {
				$db = parent::registerDbConnection('poi_server');
				
				$tmp = array();
				foreach ($keywords as $keyword) {
					$tmp[] = "(`label` LIKE ".$db->quoteInto('?', '%'.$keyword.'%')." AND `certified`='0') OR ((`label` LIKE ".$db->quoteInto('?', '%'.$keyword.'%')." OR `name` LIKE ".$db->quoteInto('?', '%'.$keyword.'%').") AND `certified`=1 )";
				}
			
				$sql = "SELECT COUNT(*) AS total FROM ".BETTER_DB_TBL_PREFIX."poi WHERE poi_id IN ('".implode("','", $poiIds)."') AND (".implode(' OR ', $tmp).")";
				
				Better_Log::getInstance()->logInfo(__CLASS__.':['.$sql.'], Time:['.date('Y-m-d H:i:s', $from).' - '.date('Y-m-d H:i:s', $to).']', 'badge_sql');
				$rs = self::squery($sql, $db);
				$row = $rs->fetch();
				
				if ($row['total']>=4) {
					$result = true;
				}		
			}	
		}
		
		return $result;
	}
	
}