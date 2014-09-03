<?php

/**
 * 红粉佳人
 * 你有20个异性好友
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Hongfenjiaren extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$userInfo = $user->getUserInfo();
		
		if ($userInfo['gender']=='female') {
			$total = 0;
			$sids = Better_DAO_User_Assign::getInstance()->getServerIds();
			
			foreach ($sids as $sid) {
				$cs = parent::assignDbConnection('user_server_'.$sid);
				$rdb = &$cs['r'];
		
				$select = $rdb->select();
				$select->from(BETTER_DB_TBL_PREFIX.'friends AS f', array(
					new Zend_Db_Expr('COUNT(*) AS total')
					));
				$select->join(BETTER_DB_TBL_PREFIX.'profile AS p', 'p.uid=f.uid', array());
				$select->where('f.friend_uid=?', $uid);
				$select->where('p.gender=?', 'male');
	
				Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
				$rs = self::squery($select, $rdb);
				$row = $rs->fetch();
				
				$total += (int)$row['total'];
				
				if ($total>=19) {
					$result = true;
					break;
				}
			}
			
			if ($total>=19) {
				$result = true;
			}
		}
		
		return $result;
	}
}