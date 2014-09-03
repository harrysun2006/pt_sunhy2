<?php

/**
 * 雕刻时光
 

上下线时间确定
 即日起-2个月
 
获得条件
 签到雕刻时光咖啡馆 http://k.ai/poi/19060249或http://k.ai/poi/852644 +绑定任意2个同步 
 *

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Diaokeshiguang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$start = gmmktime(16, 0, 0, 3, 13, 2011);
		$end = gmmktime(16, 0, 0, 5, 14, 2011);
		$now = time();	
		if(defined('IN_API') && $now<=$end && ($poiId==19060249 || $poiId==852644)){
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