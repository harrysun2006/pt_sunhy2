<?php

/**
 * 菁菁校园
 * 你在大学签到过
 * 
 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Jingjingxiaoyuan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$flag = false;
		
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		
		$poiId = (int)$params['poi_id'];
		$poiInfo = Better_Poi_Info::getInstance($poiId)->getBasic();
		$key = '大学';
		
		if ($poiInfo['certified']==1 && (preg_match('/'.$key.'/', $poiInfo['name']) || preg_match('/'.$key.'/', $poiInfo['label']))) {
			$flag = true;
		} else if ($poiInfo['certified']==0 && preg_match('/'.$key.'/', $poiInfo['label'])) {
			$flag = true;
		}

		if ($flag==true) {
			$result = true;
		}
		
		return $result;
	}
}