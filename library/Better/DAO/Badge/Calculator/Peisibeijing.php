<?php

/**
佩斯北京
签到以下POI+至少同步1SNS：
http://k.ai/poi/4362054



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Peisibeijing extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$start = gmmktime(16, 0, 0, 4, 18, 2011);
		$now = time();		
		if($poiId==4362054){	
					
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