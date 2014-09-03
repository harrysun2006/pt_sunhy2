<?php

/**
签到以下POI+至少一个SNS同步
中国国际贸易中心-第八届中艺博国际画廊博览会（CIGE 2011）http://k.ai/poi/18913511

2011年4月6日
2011年4月25日
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Cigetwozerooneone extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$endtm = gmmktime(16, 0, 0, 4, 5, 2011);
		$begintm = gmmktime(16, 0, 0, 4, 25, 2011);
		$now = time();		
		if($now>=$begintm && $now<=$endtm & $poiId==18913511){					
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