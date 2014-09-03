<?php

/**
慕青视觉
签到“慕青视觉工作室”（http://k.ai/poi?id=19078041）并至少同步一个SNS

2011年6月9日9:00
2012年6月9日24:00


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Muqingshijue extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(1, 0, 0, 6, 9, 2011);
		$endtm = gmmktime(16, 0, 0, 6, 9, 2012);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==19078041){						
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