<?php

/**
 * 【银泰百货】 
描述 P 银泰精品购物新风潮！签到银泰百货3次，开开送你银泰网200元电子优惠券！ 
同步语 P 我获得了开开【银泰百货】勋章和银泰网200元电子优惠券！ 
上下线时间确定 P 3月15日下午1点至3月31日24时 
获得条件 P 签到银泰百货任意门店1次，获得勋章。
 

 *

 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Yintaibaihuo extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$user = Better_User::getInstance($uid);
		$start = gmmktime(5, 0, 0, 3, 15, 2011);
		$end = gmmktime(16, 0, 0, 3, 31, 2011);
		$uid = (int)$params['uid'];		
		$poi_list = array(126662,19061115,19061116,19061117,19061118,19061119,19061120,19061121,19061122,19061123,19061125,19062868,19061126,19061127,19061128,19061130,19061131,19061132,19061133,19061134,19061137,19061138,19061139,19061141);	
		$now = time();	
		if($now<=$end && $now>=$start && in_array($poiId,$poi_list)){
			$result = true;
		}
		return $result;
	}
}