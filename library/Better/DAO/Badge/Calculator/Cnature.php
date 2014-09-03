<?php

/**
CNature沙漠英雄
方法一：签到以下任一地点：
19081225,19090508,17113298,7463596,15329503,6748427,19090520,10503017,9591497,8505088
方法二：吼吼中含有“CNature”或者“沙漠公益”关键词
即时
2011年8月18日24:00

 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Cnature extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(1, 0, 0, 8, 9, 2011);
		$endtm = gmmktime(16, 0, 0, 8, 18, 2011);
		$now = time();			
		$poilist = array(19081225,19090508,17113298,7463596,15329503,6748427,19090520,10503017,9591497,8505088);	
		if($now>=$begtm && $now<=$endtm){	
			$blog = &$params['blog'];				
			if($blog['type']=='checkin' && in_array($poiId,$poilist)){
				$result = true;	
			} else if($blog['type']=='normal' || $blog['type']=='checkin'){					
				$message = strtolower($blog['message']);											
				$checkinfo1 = array('/沙漠公益/','/cnature/');			
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