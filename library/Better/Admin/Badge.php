<?php
/**
 * admin页面勋章管理
 * @author  yangl
 */

class Better_Admin_Badge{
	
	/**
	 * 获得勋章
	 */
	public static function getBadges($params){
		$return = array(
			'count' => 0,
			'rows' => array(),
		);
		
		$uid = Better_Registry::get('sess')->admin_uid;
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$namekeyword = $params['namekeyword'] ? trim($params['namekeyword']) : '';
		$reload = $params['reload'] ? 1 : 0;
		
		$rows=Better_DAO_Admin_Badge::getInstance()->getAllBadges(array(
			'page' => $page,
			'namekeyword' => $namekeyword,
			'reload' => $reload
			)
		);
		
		$data = array_chunk($rows, $pageSize);
		$return['count'] = count($rows);
		$return['rows']= $data[$page-1];
		unset($data);
		
		return $return;
	}
	
	
	
	/**
	 * 后台修改badge
	 */
	public static function updateBadge(array $data, $val){
		if($data && is_array($data) && $val){
			Better_DAO_Admin_Badge::getInstance()->update($data, $val);
			return true;
		}else{
			return false;
		}
	}
	
}


?>