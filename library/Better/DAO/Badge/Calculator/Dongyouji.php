<?php

/**
签到以下POI+2SNS

9个剧场 http://k.ai/poi/77997
 
上线时间
 7月5日即时
 
下线时间
 7月11日0:00am
 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Dongyouji extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(2, 0, 0, 7, 5, 2011);
		$endtm = gmmktime(16, 0, 0, 7, 10, 2011);
		$now = time();		
		$poilist = array(77997);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){						
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