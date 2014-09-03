<?php

/**
 * POI宝物
 * 
 * @package Better.Poi
 * @author leip <leip@peptalk.cn>
 *
 */
class Better_Poi_Treasure extends Better_Poi_Base
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
	 * poi内宝物
	 * 
	 * @param unknown_type $page
	 * @param unknown_type $count
	 */
	public function logs($page=1, $count=BETTER_PAGE_SIZE)
	{
		$tmp =  Better_DAO_Poi_Treasure::logs($this->poiId, $page, $count);
		$results = array();
		
		foreach ($tmp['rows'] as $row) {
			if ($row['id']<10) {
				$prefix = '00';
			} else if ($row['id']>=10 && $row['id']<100) {
				$prefix = '0';
			} else {
				$prefix = '';
			}
						
			$row['image_url'] = BETTER_STATIC_URL.'/images/treasures/'.$prefix.$row['id'].'.png';
			$results['rows'][] = $row;
		}
		
		$results['count'] = $tmp['count'];
		
		return $results;
	}
}