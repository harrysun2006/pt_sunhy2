<?php

/**
勋章名 P 永乐 
描述 P 你身边的LIVE娱乐管家 
同步语 P 我获得了开开【永乐】勋章！永乐票务，身边的LIVE娱乐管家~ 
上下线时间确定 P 4月12日下午2点，下线时间无 
获得条件 P 签到http://k.ai/poi/377612 并至少同步一个SNS可获得 

2011年4月8日即时

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yongle extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$start = gmmktime(6, 0, 0, 4, 12, 2011);
		$now = time();		
		if($now>=$start && $poiId==377612){	
					
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