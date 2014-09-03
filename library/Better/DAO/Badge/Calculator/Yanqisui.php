<?php

/**
签到以下POI+2SNS

718联合厂-炎7周年Party： http://k.ai/poi?id=19084447
 
上线时间
 7月11日即时
 
下线时间
 7月24日1:00am
 


 

 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yanqisui extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(2, 0, 0, 7, 11, 2011);
		$endtm = gmmktime(17, 0, 0, 7, 23, 2011);
		$now = time();		
		$poilist = array(19084447);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){						
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