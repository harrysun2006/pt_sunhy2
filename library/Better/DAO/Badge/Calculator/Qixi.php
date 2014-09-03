<?php

/**
 * 钟情于你勋章
- 我和TA都是开开用户
- 我和TA已经是好友
- 我和TA是异性
- 我和TA 8月16日在同一个POI签到（间隔不超过5分钟）
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Qixi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		$gender = $userInfo['gender'];
		
		$tz = 8;
		$offset = $tz*3600;
		$month = intval(date('m', time()+$offset));
		$day = intval(date('d', time()+$offset));		
		/*
		if (APPLICATION_ENV=='production') {
			$flag = ($month==8 && $day==16) ? true : false;
		} else {
			$flag = ($month==8 && $day<16) ? true : false;
		}
		
		if ($flag && $gender!='secret') {
			$total = 0;
			$checkGender = $gender=='male' ? 'female' : 'male';
			
			$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
			foreach ($sids as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = $cs['r'];
				
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'user_place_log AS l', 
					new Zend_Db_Expr('COUNT(DISTINCT(l.uid)) AS total')
					);
				$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=l.uid', array());
				$select->join(BETTER_DB_TBL_PREFIX.'friends AS f', 'p.uid=f.uid AND f.friend_uid='.$uid, array());
				$select->where('p.gender=?', $checkGender);
				$select->where('l.checkin_time>?', time()-300);
				$select->where('l.checkin_score>?', 0);
				$select->where('l.poi_id=?', $poiId);
				Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
				$rs = self::squery($select, $rdb);
				$row = $rs->fetch();
				
				$total += (int)$row['total'];
				
				if ($total>=1) {
					$result = true;
					break;
				}
			}
		}
		*/
		return $result;
	}
	
}