<?php

/**
 
〖CONFUSION〗
精彩活动
1）4月29日即时~5月5日10:00，
签到以下POI+吼吼/签到同时吼吼/贴士（有无勾选地点均可）关键词G-SHOCK+至少同步1SNS
CASIO/浪琴表专柜（广百天河中怡店）http://k.ai/poi/19071494,19071495,19071493,19071507,19071492,19071509
CASIO专柜（天河吉之岛店）http://k.ai/poi/19071495
CASIO专柜（天河城百货店）http://k.ai/poi/19071493
CASIO专卖店（天河东时达表行）http://k.ai/poi/19071507
CASIO专柜（正佳广场店）http://k.ai/poi/19071492
CASIO专卖店（北京路店）http://k.ai/poi/19071509

2）5月7日00:00~17:00，
签到以下POI+吼吼/签到同时吼吼/贴士（有无勾选地点均可）关键词G-SHOCK+至少同步1SNS
http://k.ai/poi/11298214


Confusion

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Confusion extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$endtm1 = gmmktime(2, 0, 0, 5, 5, 2011);
		$begtm2 = gmmktime(16, 0, 0, 5, 6, 2011);
		$endtm2 = gmmktime(9, 0, 0, 5, 7, 2011);
		$poilist1 = array(19071494,19071495,19071493,19071507,19071492,19071509);
		$poiId = (int)$params['poi_id'];
		$now = time(); 
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		if (($now<=$endtm1 && in_array($poiId,$poilist1)) || ($now>=$begtm2 && $now<=$endtm2 && $poiId==11298214)) {	
				
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);
			$select = $rdb->select();
			$select->from(BETTER_DB_TBL_PREFIX.'3rdbinding', array(
				new Zend_Db_Expr('COUNT(*) AS total')
				));		
			$select->where('uid=?', $uid);						
			$rs = self::squery($select, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=1) {
				//Better_Log::getInstance()->logInfo(serialize($params),'confusion');		
				$blog = &$params['blog'];				
				$message = strtolower($blog['message']);		
				$checked = '/g-shock/';		
				if (preg_match($checked, $message)) {					
						$result = true;
				}						
			}
		}

		return $result;
	}
	
}