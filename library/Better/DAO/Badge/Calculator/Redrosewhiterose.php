<?php

/**
 * 红玫瑰与白玫瑰
 * http://k.ai/poi/131572  苏州科技文化艺术中心

 

生效时间：即日起-----12月11日。



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Redrosewhiterose extends Better_DAO_Badge_Calculator_Base
{

	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];
		$endtime = gmmktime(16, 0, 0, 12, 10, 2010);
		$now = time();
		if($now<=$endtime && $poiId==131572){
			$result = true;
		}		
		return $result;
	}
}