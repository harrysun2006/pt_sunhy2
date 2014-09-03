<?php

/**
 * 开开用户实地签到以下任何一个POI+至少2个同步第三方，无时限：
         云海肴(后海店)http://k.ai/poi/6855779
         云海肴(中关村店)http://k.ai/poi/19051741


 *

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yunhaiyao extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		if($poiId==6855779 || $poiId==19051741){
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