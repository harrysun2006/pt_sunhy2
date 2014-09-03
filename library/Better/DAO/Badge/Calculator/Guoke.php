<?php

/**
 *过客
签到“http://k.ai/poi/4363627”+至少同步新浪微博
4月2日（六）
-




 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Guoke extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];		
		
		//$start = gmmktime(7, 30, 0, 4, 2, 2011);
		
		//$now = time(); 
		if ( $poiId==4363627) {
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