<?php

/**
 
闹元宵

吼吼“元宵”关键词即可获得
2.18/01:00am
	
上线时间	2.9，即时	
下线时间	2.12，24:00pm
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */

class Better_DAO_Badge_Calculator_Naoyuanxiao extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;		
		$end = gmmktime(16, 0, 0, 2, 17, 2011);
		$now = time(); 
		
		if ($now<=$end) {	
			$blog = &$params['blog'];
			if ($blog['type']=='normal') {
				$message = $blog['message'];				
				if (preg_match('/元宵/', $message)) {					
						$result = true;				
				}
			}
		}

		return $result;
	}
	
}