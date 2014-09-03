<?php

/**
 * 获取POI分类缓存
 * 
 * @package Better.Cache.Module
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Cache_Module_Poi_Category
{
	
	public static function load()
	{
		$cacher = Better_Cache::remote();
		
		$badgeKey = 'kai_poi_categories';
		$data = array();
		
		if (!$cacher->get($badgeKey)) {
			$tmp = Better_DAO_Poi_Category::getInstance()->getAll();
			$data = array();
			foreach ($tmp as $row) {
				$data[$row['category_id']] = $row;
			}
			$cacher->set($badgeKey, $data);
		}		
		
		return $data;
	}
}