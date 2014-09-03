<?php

/**
 
微情书

在任意地点吼吼中含有#微情书#，并至少同步到新浪微博
	
上线时间	2.9，即时	
下线时间	2.12，24:00pm
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Weiqingshu extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];	
		/*
		if (0 && $poiId>0) {	
			$blog = &$params['blog'];
			if ($blog['type']=='normal') {
				$message = $blog['message'];				
				if (preg_match('/微情书/', $message)) {
					$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
					$select = $rdb->select();
					$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
						new Zend_Db_Expr('COUNT(*) AS total')
						));		
					$select->where('uid=?', $uid);	
					$select->where('protocol=?', 'sina.com');		
					$rs = self::squery($select, $rdb);
					$row = $rs->fetch();			
					if ($row['total']>=1) {
						$result = true;
					}	
				}
			}
		}
		*/
		return $result;
	}
	
}