<?php

/**
 * 标致PEUGEOT

 * 12月22日起至12月24日24时止

 * 签到南京1912广场 http://k.ai/poi/8266+绑定任一同步
        
 * 
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Peugeotauto extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];	
		$end = gmmktime(16, 0, 0, 12, 24, 2010);
		$now = time();	
			
		if ($now<=$end && $poiId==8266) {
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