<?php

/**
〖品相大师课〗
精彩活动
签到以下POI +2SNS
尤伦斯当代艺术中心：http://k.ai/poi/865038
5月22日9:50am
下次上线时间预告：6月15日
5月22日12:00am




 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Pinxiangdashike extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$begtm = gmmktime(16, 0, 0, 6, 12, 2011);
		$endtm = gmmktime(9, 30, 0, 6, 15, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==865038){	
					
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