<?php

/**
2011年4月22日8:30—13:30，签到“国家会议中心——艾瑞高峰会议易传媒展台” http://k.ai/poi?id=17151558 并同步至新浪微博、或腾讯微博、或搜狐微博、或网易微博
//2011年7月27日8:30—15:00，签到“艾瑞深圳年会易传媒展台” http://k.ai/poi?id=19088192并同步至新浪or搜狐or腾讯or网易微博

2011年7月28日8:30
2011年7月28日15：00

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Ayimedia extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$distance = (float)$params['distance'];
		$user = Better_User::getInstance($uid);		
		$start = gmmktime(0, 30, 0, 7, 28, 2011);
		$endtm = gmmktime(7, 00, 0, 7, 28, 2011);
		$now = time();		
		if($now>=$start && $now<=$endtm && $poiId==19088192 && $distance>0){					
			$rdb = Better_DAO_User_Assign::getInstance()->getRdbByUid($uid);			
			$sql = "select count(*) as total from ".BETTER_DB_TBL_PREFIX."3rdbinding where uid=".$uid." and protocol in ('sina.com', 'sohu.com', '163.com', 'qq.com')";		
			$rs = self::squery($sql, $rdb);
			$row = $rs->fetch();			
			if ($row['total']>=1) {
				$result = true;
			}						
		}
		return $result;
	}
}