<?php

/**
 
手冢治虫
获得条件	任意地点吼吼#手冢治虫#+至少2个第三方同步 	
上线时间	2.9，即时	
下线时间	2.12，24:00pm
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Shouzhongzhichong extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];	
		$start = gmmktime(16, 0, 0, 2, 8, 2011);
		$end = gmmktime(16, 0, 0, 2, 12, 2011);	
		$now = time();		
		if ($now>=$start && $now<=$end && $poiId>0) {	
			$blog = &$params['blog'];
			if ($blog['type']=='normal') {
				$message = $blog['message'];				
				if (preg_match('/手冢治虫/', $message)) {
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
			}
		}

		return $result;
	}
	
}