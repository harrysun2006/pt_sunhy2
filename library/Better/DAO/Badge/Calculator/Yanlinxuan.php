<?php

/**
 * 燕临轩
签到以下任一POI+2SNS
http://k.ai/poi/167458
http://k.ai/poi/224660
http://k.ai/poi/5964377



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Yanlinxuan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];		
		$poi_list = array(167458,224660,5964377);
		
		if (in_array($poiId,$poi_list)) {
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);		
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=2) {
				$result = true;
			}
		}

		return $result;
	}
	
}