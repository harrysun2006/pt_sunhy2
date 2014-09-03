<?php

/**
所有勋章获得时间都是6/10 10:00~7/11 0:00，但四个勋章对应的Poi 不同：

可获得获得甘香罗马勋章的poi有：

19071406,19071409,4293872,19071412,19071413,19071415,19071417,19071424
 

 

 

 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Afuganxiangluoma extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(2, 0, 0, 8, 10, 2011);
		$endtm = gmmktime(16, 0, 0, 7, 10, 2012);
		$now = time();		
		$poilist = array(19071406,19071409,4293872,19071412,19071413,19071415,19071417,19071424);
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