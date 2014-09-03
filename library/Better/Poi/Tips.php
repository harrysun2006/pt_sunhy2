<?php

/**
 * POI的Tips
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Tips extends Better_Poi_Base
{
	protected static $instance = array();
	
	protected function __construct($poiId)
	{
		parent::__construct($poiId);
	}
	
	public static function getInstance($poiId)
	{
		if (!isset(self::$instance[$poiId])) {
			self::$instance[$poiId] = new self($poiId);
		}
		
		return self::$instance[$poiId];
	}	
		
	/**
	 * 获得本poi下所有Tips
	 * 
	 * @param $page
	 * @param $count
	 * @return array
	 */
	public function all($page=1, $count=20, $tipType='hot')//hot and new
	{
		$params = array(
			'page' => $page,
			'count' => $count,
			'poi' => $this->poiId
			);
		$result = array(
			'pages' => 0,
			'rows' => array(),
			'count' => 0,
			);
		
		$rows = Better_DAO_Blog_Tips::search($params);
		$result['pages'] = $rows['pages'];
		$result['count'] = $rows['count'];

		foreach ($rows['rows'] as $row) {
			$result['rows'][] = Better_Blog::parseBlogRow($row);
		}
		unset($rows);
		
		return $result;
	}
	
	public function getRangedTips(array $params)
	{
		$return = array(
			'count' => 0,
			'pages' => 0,
			'rows' => array()
			);
		$pageSize = $params['count'] ? $params['count'] : BETTER_PAGE_SIZE;
		$page = (int)$params['page'];
		
		$rows = Better_DAO_Blog_Tips::rangedTips(array(
			'page' => $page,
			'lon' => (float)$params['lon'],
			'lat' => (float)$params['lat'],
			'range' => (float)$params['range'],
			'poi_id' => (int)$params['poi_id'],
			'order' => $params['order'] ? $params['order'] : ''
			));
		if (count($rows)>0) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $pageSize);
			$tmp = isset($data[$page-1]) ? $data[$page-1] : array();
			
			$return['pages'] = count($data);
			unset($data);
			
			foreach ($tmp as $v) {
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
		}
					
		return $return;
	}
	
	
	/**
	 * 新增本poi下的Tips
	 * 
	 * @param $params
	 * @return 
	 */
	public function add(array $params)
	{
		
	}
	
	/**
	 * 根据条件搜索Tips
	 * 
	 * @param $params
	 * @return array
	 */
	public static function search(array $params)
	{
		
	}
	
	/**
	 * 
	 * 推荐的贴士
	 * @param array $params
	 */
	public static function &recommends(array $params)
	{
		$return = array(
			'rows' => array(),
			'count' => 0,
			'pages' => 0
			);
		
		$rows = Better_DAO_Poi_Tips_Recommends::getInstance($params['uid'])->getRecommends($params);
		
		if (is_array($rows) && count($rows)) {
			$return['count'] = count($rows);
			$data = array_chunk($rows, $params['page_size']);
			$tmp = isset($data[$params['page']-1]) ? $data[$params['page']-1] : array();
			$return['pages'] = count($data);
			unset($data);
			
			$upbids = array();

			foreach ($tmp as $v) {
				$return['rows'][] = Better_Blog::parseBlogRow($v);
			}
		}	

		return $return;
	}
}