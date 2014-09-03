<?php

/**
 * POI 分类
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Category extends Better_Poi_Base
{
	
	/**
	 *  获取所有可用分类
	 *  
	 *  @return array
	 */
	public static function getAvailableCategories()
	{
		$cs = array();
		$rows = Better_DAO_Poi_Category::getInstance()->getAll(array(
			'order' => 'category_id ASC'
			));
		foreach ($rows as $row) {
			$cs[$row['category_id']] = $row;
		}
		
		return $cs;
	}
	
	/**
	 * 
	 * 获取分类的logo图片（根据数据库查询结果）
	 * @param unknown_type $data
	 * @param unknown_type $size
	 */
	public static function getCategoryImage($data, $size='101')
	{
		$data['category_image'] || $data['category_image'] = 'life.png';
		return Better_Config::getAppConfig()->base_url.'/images/poi/category/'.$size.'/'.$data['category_image'];
	}
	
	/**
	 * 
	 * 分类名称到类别logo的映射
	 * 
	 * @param unknown_type $category
	 * @param unknown_type $size
	 */
	public static function mapCategoryToImage($category, $size='101')
	{
		$img = '';
		$config = Better_Config::getAppConfig();
		$prefix = $config->base_url.'/images/poi/category/'.$size.'/';
		$prefix = '';
		
		switch ($category) {
			case 1:
				$img = 'food.png';
				break;
			case 2:
				$img = 'shop.png';
				break;
			case 3:
				$img = 'entertainment.png';
				break;
			case 4:
				$img = 'life.png';
				break;
			case 5:
				$img = 'outside.png';
				break;
			case 6:
				$img = 'education.png';
				break;
			case 7:
				$img = 'life.png';
				break;
			case 8:
				$img = 'entertainment.png';
				break;
			case 9:
				$img = 'outside.png';
				break;
			case 10:
				$img = 'travel.png';
				break;
			default:
				$img = 'life.png';
				break;
		}
		
		return $prefix.$img;
	}
}