<?php
/**
 * admin页面勋章警报
 * @author  yangl
 */

class Better_Admin_Badgealarm{
	
	/**
	 * 获得勋章
	 */
	public static function filter($params){
		$return = array(
			'count' => 0,
			'rows' => array(),
		);
		
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$badge_id = $params['badge_id'] ? trim($params['badge_id']) : '';
		$badge_name = $params['badge_name'] ? trim($params['badge_name']) : '';
		$reload = $params['reload'] ? 1 : 0;
		
		$rows=Better_DAO_Admin_Badgealarm::getInstance()->filter(array(
			'page' => $page,
			'pageSize' => $pageSize,
			'badge_id'=> $badge_id,
			'badge_name' => $badge_name
			)
		);
		
		
		return $rows;
	}
	
	
	
}


?>