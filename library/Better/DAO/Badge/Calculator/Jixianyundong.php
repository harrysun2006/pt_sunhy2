<?php

/**
勋章名 极限运动 
描述 2011起亚XGAMES世界极限运动大赛亚洲站，与潮流一族分享极限运动的至酷体验！4月25日至5月2日，签到获勋章！4月25日至4月28日中午，签到江湾体育中心参与抽奖，15张门票免费赠！ 
同步语 我获得了开开〖极限运动〗勋章！2011起亚XGAMES世界极限运动大赛亚洲站，与潮流一族分享极限运动的至酷体验！ 
4月25日上午10点~5月2日24点


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Jixianyundong extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(2, 0, 0, 4, 25, 2011);
		$endtm = gmmktime(16, 0, 0, 5, 2, 2011);
		$now = time();
		if($now>=$begtm && $now<=$endtm && $poiId==18208378){	
					
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