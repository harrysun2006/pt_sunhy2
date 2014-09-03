<?php

/**
 * 
 * 转换地点
 * 
 * @package Better.Api.Translator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Api_Translator_Place extends Better_Api_Translator_Base
{
	
	public function &translate(array $params)
	{
		$result = array();
			
		if ($params['lon'] && $params['lat']) {
			$result['lon'] = $params['lon'];
			$result['lat'] = $params['lat'];
			$result['address'] = $params['address'];
			$result['city'] = $params['city'];
		}

		return $result;		
	}
}