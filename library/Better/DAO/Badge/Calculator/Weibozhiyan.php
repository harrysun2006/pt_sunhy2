<?php

/**
 * 开开手机客户端用户，在以下POI实地签到可得，无时限：

                     微薄之盐·12th-day House  http://k.ai/poi/3085195

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Weibozhiyan extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];
		$x = (float)$params['x'];
		$y = (float)$params['y'];
		$uid = (int)$params['uid'];
		/*
		if (defined('IN_API') && $poiId==3085195) {
			$result = true;
		}
		*/
		return $result;
	}
}