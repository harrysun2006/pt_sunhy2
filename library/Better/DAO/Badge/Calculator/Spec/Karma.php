<?php

/**
 * Karma值类勋章
 * 
 * @package Better.DAO.Badge.Calculator.Spec
 * @author leip
 *
 */

class Better_DAO_Badge_Calculator_Spec_Karma extends Better_DAO_Badge_Calculator_Spec_Base
{
	protected static $limit = 999;
	protected static $direct = 'gt';
	
	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$limit = isset($params['limit']) ? (int)$params['limit'] : self::$limit;
		$direct = isset($params['direct']) ? $params['direct'] : self::$direct;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);

		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);

		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'profile', array(
			'karma'
			));
		$select->where('uid=?', $uid);
		
		$rs = self::squery($select, $rdb);
		$row = $rs->fetch();
		
		Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.'], Karma:['.$row['karma'].'], Limit:['.$limit.']', 'badge_sql');
		
		if ($direct=='gt') {
			if ($row['karma']>=$limit) {
				$result = true;
			}
		} else if ($direct=='lt') {
			if ($row['karma']<$limit) {
				$result = true;
			}
		}
		
		return $result;
	}	
}