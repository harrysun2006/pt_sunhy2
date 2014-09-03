<?php

/**
 
勋章名称
 星客特之尊享
 
获得条件
 在上海新国际博览中心星客特室内展位（http://k.ai/poi?id=19068327）签到，并附上图片；或在该地点的吼吼中上传图片——并同步任意SNS
 
上线时间
 4月19日
 
下线时间
 4月29日
 

 




 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Xinketezunxiang extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		
		
		$poiId = (int)$params['poi_id'];		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$begtm = gmmktime(16, 0, 0, 4, 18, 2011);
		$endtm = gmmktime(16, 0, 0, 4, 29, 2011);
		$now = time();		
		$blog = &$params['blog'];
		if ($now>=$begtm && $now<=$endtm && $poiId==19068327) {						
			$result = true;		
		}	
		return $result;
	}
	
}