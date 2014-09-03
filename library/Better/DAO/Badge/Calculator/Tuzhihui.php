<?php

/**
勋章名称
 智慧兔
 
获得条件
 签到任一指定的12家旗舰店+同步任一SNS可获此勋章。
 
上线时间
 3月9日
 
下线时间
 4月9日24:00

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Tuzhihui extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$start = gmmktime(16, 0, 0, 3, 8, 2011);
		$end = gmmktime(16, 0, 0, 4, 9, 2011);
		$now = time();
		$poi_list = array(19061043,19061046,19061047,19061048,19061049,19061050,19061051,19061052,19061053,7593633,19061055);
		if ($now<=$end && $now>=$start && in_array($poiId,$poi_list)){				
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