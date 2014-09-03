<?php
/**
 * admin页面用户check in管理
 * @author  yangl
 */

class Better_Admin_Usercheckin{
	
	/**
	 * 获得用户check in历史
	 */
	public static function getUserCheckins($params){
		$return = array(
			'count' => 0,
			'rows' => array(),
		);
		
		$uid = Better_Registry::get('sess')->admin_uid;
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$userkeyword = $params['userkeyword'] ? trim($params['userkeyword']) : '';
		$reload = $params['reload'] ? 1 : 0;
		$from = $to = '';
		
		if ($params['from']) {
			$from = $params['from'];
			$y = substr($from, 0, 4);
			$m = substr($from, 5, 2);
			$d = substr($from, 8, 2);	
			$from = gmmktime(0, 0, 0, $m, $d, $y)-BETTER_8HOURS;
		}
		
		if ($params['to']) {
			$to = $params['to'];
			$y = substr($to, 0, 4);
			$m = substr($to, 5, 2);
			$d = substr($to, 8, 2);	
			$to = gmmktime(23, 59, 59, $m, $d, $y)-BETTER_8HOURS;
		}
		
		$cacheKey = md5($userkeyword.'_'.$from.'_'.$to.'_'.$uid.'_'.'checkin');
		
		$rows=Better_DAO_Admin_Usercheckin::getAllUserCheckins(array(
			'page' => $page,
			'userkeyword' => $userkeyword,
			'cacheKey' => $cacheKey,
			'from' => $from,
			'to' => $to,
			'reload' => $reload
			)
		);
		
		//$rows=self::parseCheckins($rows);
		
		$data = array_chunk($rows, $pageSize);
		$return['count'] = count($rows);
		foreach($data[$page-1] as $row){
			$return['rows'][]=self::parseCheckin($row, $reload);
		}
		
		unset($data);
		
		return $return;
	}
	
	
	private static function parseCheckin($row, $reload=0){

			$poi_cacheKey=md5('poi_table');
			Better_Cache_Lock::getInstance()->wait($poi_cacheKey);

			$poi_data=array();
			if(!Better_DAO_Admin_Base::getDbCacher()->test($poi_cacheKey) || $reload==1){
				Better_Cache_Lock::getInstance()->lock($poi_cacheKey);
				
				$pois=Better_DAO_Admin_Poi::getInstance()->getAllPOIs(array('reload'=>1));
				if(count($pois)>0){
					foreach($pois as $poi){
						$poi_data[$poi['poi_id']]=$poi;
					}
				}
				
				Better_DAO_Admin_Base::getDbCacher()->set($poi_cacheKey, $poi_data);
				Better_Cache_Lock::getInstance()->release($poi_cacheKey);
			}else{
				$poi_data=Better_DAO_Admin_Base::getDbCacher()->get($poi_cacheKey);
			}
			
			$poi_category_data=array();
			$categories=Better_DAO_Admin_Poi::getInstance()->getCategoties();
			if(count($categories)>0){
				foreach ($categories as $category){
					$poi_category_data[$category['category_id']]=$category;	
				}
			}
			

			$tmp=$poi_data[$row['poi_id']];
			$tmp1=$poi_category_data[$row['poi_category_id']];
			$row['poi_name']=$tmp['name'];
			$row['poi_address']=$tmp['address'];
			$row['poi_category_name']=$tmp1['category_name'];
		
			return $row;
	}
	
}


?>