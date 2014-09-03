<?php

/**
Ataraxia
签到“Mao Livehouse http://k.ai/poi?id=226316”+至少2个第三方同步 
4月12日即时
4月16日2:00am



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Ataraxia extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$endtm = gmmktime(18, 0, 0, 4, 15, 2011);
		$now = time();		
		if($now>=$endtm && $poiId==226316){	
					
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