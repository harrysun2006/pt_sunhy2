<?php

/**
 * G-SHOCK
即日起至2011.1.16，24:00pm


签到以下任意POI+至少1个第三方同步 

http://k.ai/poi/356028

http://k.ai/poi/19054800

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Gshock extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{		
		parent::touch($params);
		$result = false;	
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$end = gmmktime(16, 0, 0, 1, 16, 2011);
		$now = time();			
		if ($now<=$end && ($poiId==356028 ||$poiId==19054800)) {
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);		
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=1) {
				$result = true;
			}
		}
		
		return $result;
	}
}