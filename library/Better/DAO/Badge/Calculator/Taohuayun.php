<?php

/**
 * 桃花运勋章
 * 用户同时(签到时间相差在30分钟内)和3个及以上的异性在同一个地点签到
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Taohuayun extends Better_DAO_Badge_Calculator_Base
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
		
		if ($gender!='secret') {
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
				$select->where('p.gender=?', $checkGender);
				$select->where('l.checkin_time>?', time()-1800);
				$select->where('l.checkin_score>?', 0);
				$select->where('l.poi_id=?', $poiId);
				Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
				$rs = self::squery($select, $rdb);
				$row = $rs->fetch();
				
				$total += (int)$row['total'];
				
				if ($total>=3) {
					$result = true;
					break;
				}
			}
			
			if ($total>=3) {
				$result = true;
			}
		}

		return $result;
	}
	
}