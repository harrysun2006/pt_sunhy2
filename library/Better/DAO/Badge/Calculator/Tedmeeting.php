<?php

/**
上下线时间确定 上线：6.24上午10点;下线：6.26 24点。Banner同时上下线 
获得条件 签到http://k.ai/poi?id=19082607 并同步任一SNS 

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Tedmeeting extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(2, 0, 0, 6, 24, 2011);
		$endtm = gmmktime(16, 0, 0, 6, 26, 2011);
		$now = time();		
		$poilist = array(19082607);
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