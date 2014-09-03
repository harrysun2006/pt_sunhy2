<?php

/**
 * 大声展类勋章基类
 * 
 * @package Better.DAO.Badge.Calculator.Spec
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Spec_Getitlouder extends Better_DAO_Badge_Calculator_Spec_Base
{
	protected static $md = '';
	
	public static function touch(array $params)
	{
		$result = false;
		
		$config = Better_Config::getAppConfig();
		$day = '2010'.self::$md;
		$now = date('Ymd', time()+3600*8);
		
		if ($day==$now && $config->getitloudermeeting->switch) {
			$timeNow = time();
			$poiId = (int)$params['poi_id'];
			$overTime = $config->poi->getitlouder->sh->overtime;
			$startTime = $config->poi->getitlouder->bj->overtime;
			
			if ($timeNow>$startTime && $timeNow<=$overTime) {
				$specPoiId = $config->poi->getitlouder->sh->id;
			} else if ($timeNow<=$startTime) {
				$specPoiId = $config->poi->getitlouder->bj->id;
			} else {
				$specPoiId = 0;
			}

			if ($specPoiId && $poiId && $poiId==$specPoiId) {
				$result = true;
			}
		}		
		
		return $result;
	}
}