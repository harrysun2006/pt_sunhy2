<?php

/**
所有勋章获得时间都是6/10 10:00~7/11 0:00，但四个勋章对应的Poi 不同：

可获得薰衣草田勋章的poi有：

767015,6892582,19069618,6903563,19069623,19069627,19069628,6906147,6896943
 

 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Afuxunyicaotian extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(2, 0, 0, 8, 10, 2011);
		$endtm = gmmktime(16, 0, 0, 7, 10, 2011);
		$now = time();		
		$poilist = array(767015,6892582,19069618,6903563,19069623,19069627,19069628,6906147,6896943);
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