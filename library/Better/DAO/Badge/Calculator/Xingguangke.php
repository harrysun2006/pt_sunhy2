<?php

/**
 *勋章名称
 星光客
 
获得条件
 签到星光客青年旅社http://k.ai/poi?id=6108783，并至少同步至两个第三方社区
 
上线时间
 2011年4月15日10:00
 
下线时间
 2012年4月15日 24:00
 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Xingguangke extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 4, 15, 2011);
		$endtm = gmmktime(16, 0, 0, 4, 15, 2012);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==6108783){	
					
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