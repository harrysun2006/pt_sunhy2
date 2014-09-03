<?php

/**
 
签到时吼吼以下内容，或吼吼中（有无勾选地点均可）含有以下关键词+2个SNS：
“拿烟斗的男孩”，或“拿烟斗的小男孩”
2011年4月8日即时
2011年4月9日24：00



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Picasso extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		
		$endtm = gmmktime(16, 0, 0, 4, 9, 2011);
		$poiId = (int)$params['poi_id'];
		$now = time(); 
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		if ($now<=$endtm) {			
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);						
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=2) {	
				$blog = &$params['blog'];						
				if ($blog['type']=='normal' || $blog['type']=='checkin') {
					$message = $blog['message'];		
					$checked1 = '/拿烟斗的男孩/';	
					$checked2 = '/拿烟斗的小男孩/';	
					if (preg_match($checked1, $message) || preg_match($checked2, $message)) {					
						$result = true;			
					}
				}			
			}
		}

		return $result;
	}
	
}