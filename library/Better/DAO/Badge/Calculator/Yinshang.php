<?php

/**
 * 
 * 隐上
 即日起-永久
 
获得条件
 签到杭州花园餐厅 http://k.ai/poi/19050101 +绑定任意2个同步 
 


9折优惠，掌门可获得一杯免费的特制饮品！

 *

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yinshang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$poi_list = 19050101;
		if($poiId==$poi_list){				
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