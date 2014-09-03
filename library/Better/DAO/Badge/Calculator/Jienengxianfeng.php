<?php

/**
节能先锋
签到789艺文节北大站（http://k.ai/poi?id=19073326）789艺文节北师站（http://k.ai/poi?id=19073336），并说出含有“节能”的吼吼，或在以上两个地点的吼吼中含有“节能”关键词
2011年5月13日
2011年5月18日24:00


 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Jienengxianfeng extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 5, 12, 2011);
		$endtm = gmmktime(16, 0, 0, 5, 18, 2011);
		$now = time();		
		if($now>=$begtm && $now<=$endtm && ($poiId==19073326 || $poiId==19073336)){	
			
			$blog = &$params['blog'];						
			if ($blog['type']=='normal' || $blog['type']=='checkin') {
				$message = Better_Filter::make_semiangle($blog['message']);					
				$message = strtolower($message);		
				$checked1 = '/节能/';	
			
				if (preg_match($checked1, $message)) {					
					$result = true;			
				}
			}			
		}			
		
		return $result;
	}
}