<?php

/**
勋章名称
 时光雕刻
 
获得条件
 在以下地点实地签到，158617、459696、197334、6442542、463874、29396、19065257、303492、19065259、19059219、10000018，并至少同步到新浪微博
 
上线时间
 2011年4月11日，10:00
 
下线时间
 2011年6月30日，24:00
 






 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Shiguangdiaokecoffee extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$distance = (float)$params['distance'];
		$user = Better_User::getInstance($uid);		
		$start = gmmktime(2, 0, 0, 4, 11, 2011);
		$endtm = gmmktime(16, 0, 0, 6, 30, 2011);
		$poilist = array(158617,459696,197334,6442542,463874,29396,19065257,303492,19065259,19059219,10000018);
		$now = time();		
		/*
		if($now>=$start && $now<=$endtm && in_array($poiId,$poilist) && $distance<=10000){					
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);	
			$select->where('protocol=?', 'sina.com');	
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=1) {
				$result = true;
			}
			
		}
		*/
		return $result;
	}
}