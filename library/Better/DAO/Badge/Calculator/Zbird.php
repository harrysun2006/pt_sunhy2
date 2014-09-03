<?php

/**
 * 钻石小鸟
 *开开手机客户端用户，仅在以下POI实地签到可得，无时限：

zbird钻石小鸟<北京体验中心> http://k.ai/poi/630449

zbird钻石小鸟<上海体验中心> http://k.ai/poi/426490

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Zbird extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$zbird_sh_poi_id = 426490;
		$zbird_bj_poi_id = 630449;
		/*
		if(defined('IN_API') && ($poiId==$zbird_sh_poi_id || $poiId==$zbird_bj_poi_id )){
			$result = true;
		}
		*/
		return $result;
	}
}