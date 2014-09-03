<?php
/**
 * admin页面 POI修改
 * @author  yangl
 */

class Better_Admin_Poiupdate{
	
	/**
	 * 获得一些POI
	 */
	public static function getPOIs($params=array()){
		$return = array(
			'count' => 0,
			'rows' => array(),
		);
		
		$uid = Better_Registry::get('sess')->admin_uid;
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$poi_id = $params['poi_id']? $params['poi_id']: '';
		
		$result = Better_DAO_Admin_Poiupdate::getInstance()->getAll(array(
			'page'=>$page,
			'count'=>$pageSize,
			'poi_id'=>$poi_id
		));
		
		if($result['rows']){
			foreach($result['rows'] as $row){
				$pid = $row['poi_id'];
				$poi = Better_Poi_Info::getInstance($pid)->getBasic();
				
				$poi['dateline'] = $row['dateline'];
				$poi['id'] = $row['id'];
				$return['rows'][] = $poi;
			}
		}
		
		$return['count'] = $result['count'];
		
		return $return;
	}
	
	
}


?>