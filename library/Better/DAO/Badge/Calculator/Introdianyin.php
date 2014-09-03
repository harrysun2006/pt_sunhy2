<?php

/**
 * Intro电音
签到http://k.ai/poi?id=19053534并至少同步一个第三方
重要：签到http://k.ai/poi?id=19053534并至少同步一个第三方还可以获得innight勋章，编号181
即时
5月22日24:00


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Introdianyin extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];		
		
		$poi_list2 = array(19053534);
		$begtm2 = gmmktime(16, 0, 0, 5, 17, 2011);
		$endtm2 = gmmktime(16, 0, 0, 5, 22, 2011);		
		$now = time(); 
		if  ($now>=$begtm2 && $now<=$endtm2 && in_array($poiId,$poi_list2)) {
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