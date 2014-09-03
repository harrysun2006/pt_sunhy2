<?php

/**
 * POI排行榜
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Sort
{
	
	public static function &sort(array $params)
	{
		$params['page'] = 1;
		$params['count'] = $params['count'] ? $params['count'] : 10;
		$params['what'] = 'poi';
		
		$rows = Better_Search::factory($params)->search();
		
		return $rows['rows'];
	}
}