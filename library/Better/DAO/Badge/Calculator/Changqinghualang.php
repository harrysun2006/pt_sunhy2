<?php

/**
 * 
常青画廊
精彩活动
签到以下POI+至少同步1SNS：
http://k.ai/poi/383341
2011年4月26日即时



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Changqinghualang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
			
		if($poiId==383341){	
					
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