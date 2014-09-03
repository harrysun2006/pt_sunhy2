<?php

/**
 * 盟主
 * 你同时拥有了10个掌门
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Mengzhu extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$db = parent::registerDbConnection('poi_server');
		$select = $db->select();
		$select->from(BETTER_DB_TBL_PREFIX.'poi', array(
			new Zend_Db_Expr('COUNT(*) AS total')
			));
		$select->where('major=?', $uid);
		$select->where('closed=?', 0);
		
		Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
		$rs = self::squery($select, $db);
		$row = $rs->fetch();
		
		if ($row['total']>=10) {
			$result = true;
		}
		
		return $result;
	}
}