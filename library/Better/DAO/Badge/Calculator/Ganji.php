<?php

/**
 * 赶集
 * 你在超过50个人签到过的地点签到了
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Ganji extends Better_DAO_Badge_Calculator_Base
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
		$select->from(BETTER_DB_TBL_PREFIX.'poi');
		$select->where('poi_id=?', $poiId);
		
		Better_Log::getInstance()->logInfo(__CLASS__.':['.$select.']', 'badge_sql');
		$rs = self::squery($select, $db);
		$row = $rs->fetch();
		
		if ($row['visitors']>=50) {
			$result = true;
		}
		
		return $result;
	}
}