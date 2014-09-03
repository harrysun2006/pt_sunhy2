<?php

/**
 * 夺宝奇兵
 * 你的背包中有了3个宝物
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Duobaoqibing extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);

		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);

		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'user_treasures', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('uid=?', $uid);
		
		Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
		$rs = self::squery($select, $rdb);
		$row = $rs->fetch();
		
		if ($row['total']>=3) {
			$result = true;
		}
		
		return $result;
	}
}