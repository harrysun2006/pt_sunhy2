<?php

/**
签到以下任一POI+吼吼“夸住宅”

9个剧场http://k.ai/poi/77997,78146,19059128,1271264,575494,5790976,4363627,416416,19056026,3408,6820166
 
上线时间
 8月17日即时
 
下线时间
 9月12日0:00am
 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Kuazhuzhai extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(2, 0, 0, 8, 17, 2011);
		$endtm = gmmktime(16, 0, 0, 9, 11, 2011);
		$poilist = array(77997,78146,19059128,1271264,575494,5790976,4363627,416416,19056026,3408,6820166);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && in_array($poiId,$poilist)){	
			$blog = &$params['blog'];							
			if ($blog['type']!='tips') {
				$message = strtolower($blog['message']);											
				$checkinfo1 = array('/夸住宅/');					
				foreach($checkinfo1 as $row){						
					if (preg_match($row, $message)){
						$result = true;	
						break;
					}	
				}
							
			}						
		}
		return $result;
	}
}