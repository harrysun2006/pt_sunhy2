<?php

/**
〖罗密欧与朱丽叶〗
精彩活动
签到以下POI+2SNS
朝阳9个剧场 http://k.ai/poi/77997
6月21日即时
6月27日0:00am

 


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Luomioyuzhuliye extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(16, 0, 0, 6,20, 2011);
		$endtm = gmmktime(16, 0, 0, 6,26, 2011);
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