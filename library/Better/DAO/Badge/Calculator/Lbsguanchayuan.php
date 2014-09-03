<?php

/**
勋章名称：LBS观察员
勋章活动说明：
时间：1月16日0点~24点 （请勿提早上线哦）
方法：签到“铂澜咖啡建外SOHO店”http://k.ai/poi/82031可以获得

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Lbsguanchayuan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$config = Better_Config::getAppConfig();	
		$poiId = (int)$params['poi_id'];	
		$start = gmmktime(16, 0, 0, 1, 15, 2011);
		$end = gmmktime(16, 0, 0, 1, 16, 2011);
		$now = time();		
		if ($now<=$end && $now>=$start && $poiId==82031) {
			$result = true;
		}		
		return $result;
	}
}