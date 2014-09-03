<?php

/**
勋章名称：参差咖啡
获得条件：

时间不限

以下任意POI签到即可得


参差咖啡国贸店		19055626
参差咖啡中心店		10048750
参差咖啡水果湖		10051087
参差咖啡长报路店		7539820
参差咖啡江大店		19055639
参差咖啡双湖桥店		10051082
参差咖啡SHOWTIME店		19055641
参差咖啡书屋光谷店		19055707
参差花房咖啡		19055705



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Cenci extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];		
		$poi_list = array(19055626,10048750,10051087,7539820,19055639,10051082,19055641,19055707,19055705);	
	
		if (in_array($poiId,$poi_list)) {
			$result = true;
		}

		return $result;
	}
}