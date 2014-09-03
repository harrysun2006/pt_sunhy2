<?php

/**
 * 绅士
 * 你有三个好友
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Shenshi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);

		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);

		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'friends', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $uid);
		Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
		$rs = self::squery($select, $rdb);
		$row = $rs->fetch();
		$total = (int)$row['total'];
		
		if ($total>=2) {
			$result = true;
		}
		
		return $result;
	}
}