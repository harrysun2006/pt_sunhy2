<?php

/**
 * 海子
签到“龙家营站”http://k.ai/poi/7961053+至少2个第三方同步 
3.24，即时
3.26，24:00pm

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Haizi extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{		
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$start = gmmktime(0, 0, 0, 3, 24, 2011);
		$end = gmmktime(16, 0, 0, 3, 26, 2011);
		$now = time();		
		
		if ($now<=$end && $now>=$start && $poiId==7961053) {
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