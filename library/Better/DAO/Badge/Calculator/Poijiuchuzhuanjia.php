<?php

/**
所有勋章获得时间都是6/10 10:00~7/11 0:00，但四个勋章对应的Poi 不同：

可获得玫瑰谷勋章的poi有：
6898094,19071222,19071225,19071232,19071235,19071236,19071237,19071268
 

 



 * @package Better.DAO.Badge.Calculator
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_DAO_Badge_Calculator_Poijiuchuzhuanjia extends Better_DAO_Badge_Calculator_Base
{
/*
 * 'poi_info' => $poiToInsert,
						'poi_id' => $flag,
						'uid' => $params['creator'],
						'doing' => 'new'
 */
	public static function touch(array $params)
	{
		parent::touch($params);
		$result = false;
		$poiId = (int)$params['poi_id'];	
		$uid = (int)$params['uid'];
		$doing = $params['doing'];		
		if(isset($params['doing']) && $params['doing']=='update'){						
			$result = true;
		}
		return $result;
	}
}