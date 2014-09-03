<?php

/**
〖正极无限大〗
 
勋章分类
 精彩活动
 
获得条件
 签到以下48个POI任意一个+同步任一SNS 

（见附件）
 
上线时间
 6月13日即时
 
下线时间
 7月10日0:00am
 
 
 19080887,19080886,19080885,19080884,19080882,19080881,19080880,19080879,19080877,19080876,19080875,19080874,19080873,19080872,19080871,19080870,19080869,19080868,19080869,19080868,19080867,19080866,19080864,19080862,19080861,19080860,19080859,19080857,19080856,19080855,19080854,19080853,19080852,19080851,19080850,19080849,19080848,19080846,19080845,19080844,19080843,19080842,19080841,19080840,19080837,19080836,19080835,19080834


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Zhenjiwuxianda extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(16, 0, 0, 6, 12, 2011);
		$endtm = gmmktime(16, 0, 0, 7, 9, 2011);
		$now = time();		
		$poilist = array(19080887,19080886,19080885,19080884,19080882,19080881,19080880,19080879,19080877,19080876,19080875,19080874,19080873,19080872,19080871,19080870,19080869,19080868,19080869,19080868,19080867,19080866,19080864,19080862,19080861,19080860,19080859,19080857,19080856,19080855,19080854,19080853,19080852,19080851,19080850,19080849,19080848,19080846,19080845,19080844,19080843,19080842,19080841,19080840,19080837,19080836,19080835,19080834, 85808, 19080832);
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