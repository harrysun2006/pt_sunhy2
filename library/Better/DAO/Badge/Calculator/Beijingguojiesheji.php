<?php

/**
〖北京国际设计周〗
精彩活动
签到以下POI +2SNS （每个相关活动POI，不定）
5月25日：中华世纪坛http://k.ai/poi/865038
5月27日即时
5月30日19:00pm



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Beijingguojiesheji extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 6, 12, 2011);
		$endtm = gmmktime(9, 30, 0, 6, 15, 2011);
		$now = time();		
		$poilist = array(865038);
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
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