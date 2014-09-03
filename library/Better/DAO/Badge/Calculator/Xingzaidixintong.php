<?php

/**
上线：7月25日     10:00；下线：   8月7日 24:00 
获得条件 签到以下POI ID中的任意一个，并至少同步到一个SNS :19087284,18921856,12746574,5784772,559112,4751218,8409490,14002432,16174411,1441631,1231339,17533848,304797  



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Xingzaidixintong extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);		
		$begtm = gmmktime(2, 0, 0, 7, 30, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 12, 2011);
		$now = time();		
		$poilist = array(19087811,19087813,19087823,19087827,19087828,19087830,19087831,19087833,19087834,19087835,19088188,19088193,19088194,19088225,19088228,19088230,19088231,11836412,19088233,19088234,19088236,19088239,12069133,16590237,11536002,19088244,16421037,14228130,9948492,4751218,19088245,4752677,11396859,19088248,8948108,8966847,11819875,19088252,8973800,14002432,19088257,17688969,19088263,19088267,19087948,15711470,15945594,18454077,19087949,16092510,19087956,19087958,19087964,19087967,19087988,19087990,16233720,18107843,19087993,8917283,19087998,16474201,19087999,19088000,9292407,19088003,19088005,9297822,8704886,7907390,9373429,19088009,19088010,19088011,19088012,19088013,19088014,19087284,18921856,12746574,5784772,559112,4751218,8409490,14002432,16174411,1441631,1231339,17533848,304797);
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