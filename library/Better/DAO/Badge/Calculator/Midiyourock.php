<?php

/**
 
You Rock!!
签到北京市京浪岛公园http://k.ai/poi/15606913并吼吼“我要去迷笛”，（或在该地点吼吼“我要去迷笛”），同步任意SNS，即可获得 
2011年4月20日
2011年5月2日24:00

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Midiyourock extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 4, 19, 2011);
		$endtm = gmmktime(16, 0, 0, 5, 2, 2011);
		$now = time();		
		$blog = &$params['blog'];
		if ($now>=$begtm && $now<=$endtm && $poiId==15606913) {									
			if ($blog['type']=='normal' || $blog['type']=='checkin') {	
				$message = $blog['message'];		
				$checked = '/我要去迷笛/';				
				if (preg_match($checked, $message)) {										
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
			}
		}
		return $result;
	}
	
}