<?php

/**
 * 魔方
 *10月27日8时起在此POI(http://k.ai/poi/124425)  

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Mofun extends Better_DAO_Badge_Calculator_Base
{
	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$start = gmmktime(0, 0, 0, 10, 27, 2010);
		$now = time();	
		/*
		if ($now>=$start && $poiId==124425) {	
			$result = true;
		}
		*/
		return $result;
	}
}