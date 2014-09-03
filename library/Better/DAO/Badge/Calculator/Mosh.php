<?php

/**
 *魔时网专属k.ai勋章描述

获得条件：即日起~12.25/24:00，开开用户签到以下POI+至少1个同步第三方：

          魔时网五周年平安夜复古Party http://k.ai/poi/19052974 

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Mosh extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$end = gmmktime(16, 0, 0, 12, 25, 2010);
		$now = time();		
		if($now<=$end && $poiId==19052974){
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