<?php

/**
童“画”色彩
签到活动国瑞购物中心http://k.ai/poi?id=122903并至少同步一个SNS

2011年7月7日9:00
2011年7月17日24:00




 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Tonghuasecai extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(1, 0, 0, 7, 7, 2011);
		$endtm = gmmktime(16, 0, 0, 7, 17, 2011);
		$now = time();		
		$poilist = array(122903);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
					
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