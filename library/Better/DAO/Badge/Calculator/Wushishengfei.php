<?php

/**
〖无事生非〗
品牌&优惠
签到以下POI+吼吼“无事生非”或“Much Ado About Nothing”（much ado about nothing， 不区分大小写）+2SNS
科技文化艺术中心：http://k.ai/poi/131572
5月6日即时
6月7日1:00am

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Wushishengfei extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 5, 5, 2011);
		$endtm = gmmktime(17, 0, 0, 6, 6, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && $poiId==131572){	
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);						
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=2) {	
				$blog = &$params['blog'];						
				if ($blog['type']=='normal' || $blog['type']=='checkin') {
					$message = strtolower($blog['message']);		
					$checked1 = '/无事生非/';	
					$checked2 = '/much ado about nothing/';	
					if (preg_match($checked1, $message) || preg_match($checked2, $message)) {					
						$result = true;			
					}
				}			
			}			
		}
		return $result;
	}
}