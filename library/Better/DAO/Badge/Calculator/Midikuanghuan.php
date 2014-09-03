<?php

/**
 * MIDI狂欢
签到北京市京浪岛公园http://k.ai/poi/15606913并同步任意SNS即可获得

2011年4月30日
2011年5月2日 24:00


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Midikuanghuan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 4, 29, 2011);
		$endtm = gmmktime(16, 0, 0, 5, 2, 2011);
		$now = time();		
		$blog = &$params['blog'];
		if ($now>=$begtm && $now<=$endtm && $poiId==15606913){					
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