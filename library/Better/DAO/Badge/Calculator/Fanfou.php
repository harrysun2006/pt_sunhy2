<?php

/**
 * 饭否
 *获得条件：在任意POI吼吼中含有“饭否”关键词

获得时间：饭否网开放后即可上线，目前还未开放
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Fanfou extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);

		$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);

		$select = $rdb->select();
		$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));		
		$select->where('uid=?', $uid);
		$select->where('protocol=?', 'fanfou.com');
		Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
		$rs = self::squery($select, $rdb);
		$row = $rs->fetch();
		
		if ($row['total']==1) {
			$result = true;
		}
		
		return $result;
	}
}