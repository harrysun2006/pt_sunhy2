<?php

/**
 * 连轴转
 * 你在12小时内签到了10次
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Lianzhouzhuan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);

		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);

		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'user_place_log', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $uid);
		$select->where('checkin_score>?', 0);
		$select->where('checkin_time>?', time()-3600*12);
		$select->where('checkin_time<?', time());
		
		Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
		$rs = self::squery($select, $rdb);
		$row = $rs->fetch();
		
		if ($row['total']>=9) {
			$result = true;
		}
		
		return $result;
	}
}