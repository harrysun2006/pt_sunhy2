<?php

/**
 * 腐败分子
 * 一周内你4个晚上都签到了
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Fubaifenzi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$userInfo = $user->getUserInfo();
		$tz = $userInfo['timezone'];
				
		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
		
		$startHour = 21 - $tz;
		$endHour = 24 - $tz;
		$startHour2 = 0 - $tz;
		$endHour2 = 3 - $tz;
		$hNow = date('H');
		
		if (($hNow>=$startHour && $hNow<=$endHour) || ($hNow>=$startHour2 && $hNow<=$endHour2)) {
			$hourWhere = new Zend_Db_Expr('HOUR(FROM_UNIXTIME(checkin_time)>='.((int)$startHour).')');
			$hourWhereEnd = new Zend_Db_Expr('HOUR(FROM_UNIXTIME(checkin_time)=<'.((int)$endHour).')');
			$checkinTimeWhere = $rdb->quoteInto('checkin_time>?', time()+$tz*3600 - 3600*24*7);
	
			$sql = "SELECT COUNT(DISTINCT(DAY(FROM_UNIXTIME(checkin_time)))) AS total 
				FROM `".BETTER_DB_TBL_PREFIX."user_place_log`
				WHERE uid='".intval($uid)."' AND checkin_score>0 AND checkin_time>".(time()+$tz*3600 - 3600*24*7)."
					AND (
						(
							HOUR(FROM_UNIXTIME(checkin_time))>=".((int)$startHour)."
							AND
							HOUR(FROM_UNIXTIME(checkin_time))<=".((int)$endHour)."
						)
						OR
						(
							HOUR(FROM_UNIXTIME(checkin_time))>=".((int)$startHour2)."
							AND
							HOUR(FROM_UNIXTIME(checkin_time))<=".((int)$endHour2)."						
						)
					)
			";
			
			Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
			$rs = self::squery($sql, $rdb);
			$row = $rs->fetch();
			$total = (int)$row['total'];
			
			if ($total>=4) {
				$result = true;
			}
		}
		
		return $result;
	}
	
}