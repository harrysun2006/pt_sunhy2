<?php

/**
获得条件
 签到背包客青年旅社（满陇桂雨店）http://k.ai/poi?id=517865+至少同步2个第三方社区
 
上线时间
 2011年4月13日 10:00
 
下线时间
 2012年4月13日 24:00
 




 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beibaoke extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$endtm = gmmktime(16, 0, 0, 4, 13, 2012);
		$now = time();		
		if($now<=$endtm && $poiId==517865){	
					
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