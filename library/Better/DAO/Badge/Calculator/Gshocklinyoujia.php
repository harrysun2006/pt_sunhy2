<?php

/**
G-SHOCK林宥嘉
签到北京西单大悦城，并至少同步一个第三方
POI: http://k.ai/poi?id=17331820

即时
6月19日 24:00




 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Gshocklinyoujia extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(5, 50, 0, 6, 9, 2011);
		$endtm = gmmktime(18, 0, 0, 6, 19, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==17331820){						
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