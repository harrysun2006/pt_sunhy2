<?php

/**
 谢谢你 
描述 截止5月26日上午10点，在此地签到次数最多的前2名各获得刀郎5月28日演唱会门票2张！ 
同步语 我获得了开开刀郎演唱会〖谢谢你〗勋章！签到抢门票啦！ 
上下线时间确定 上线时间：5月23日10点 下线时间5月28日24:00（banner同时上下线） 
获得条件 poi id :326373 签到+至少一个同步 





 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Xiexieni extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 5, 23, 2011);
		$endtm = gmmktime(16, 0, 0, 5, 28, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==326373){	
					
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