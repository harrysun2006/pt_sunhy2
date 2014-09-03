<?php

/**
勋章名称
 玩乐兔 3
 
获得条件
 签到“娱乐”分类POI+同步任一SNS可获此勋章。
 
上线时间
 3月9日
 
下线时间
 4月9日24:00

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Tuwanle extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		$start = gmmktime(16, 0, 0, 3, 8, 2011);
		$end = gmmktime(16, 0, 0, 4, 9, 2011);
		$now = time();
		
		if ($now<=$end && $now>=$start && ($poiInfo['category_id']==3 || $poiInfo['category_id']==1) && (preg_match('/北京/', $poiInfo['city']) || preg_match('/上海/', $poiInfo['city']) || preg_match('/广州/', $poiInfo['city'])|| preg_match('/成都/', $poiInfo['city']))){				
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