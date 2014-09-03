<?php

/**
 * Polo2
 * New Polo活动第二枚勋章
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Polo2 extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$config = Better_Config::getAppConfig();
		$polo = Better_Market_Polo::getInstance();
		
		$poiId = (int)$params['poi_id'];
		if (in_array($poiId, $polo->poiIds)) {
			$start = gmmktime(11, 0, 0, 2, 14, 2011);
			$end = gmmktime(11, 59, 59, 2, 14, 2011);
			$time = time();
			
			if ($time>$start && $time<$end) {
				$result = true;
			}
		}
		
		return $result;
	}
}