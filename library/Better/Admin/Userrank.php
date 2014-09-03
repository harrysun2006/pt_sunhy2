<?php

class Better_Admin_Userrank
{
	
	public static function getAll(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
		$uid = Better_Registry::get('sess')->admin_uid;
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$user_keyword = $params['user_keyword'] ? trim($params['user_keyword']) : '';
		$act_type = $params['act_type'] ? trim($params['act_type']) : '';

		$reload = $params['reload'] ? 1 : 0;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		
		$cacheKey = md5($user_keyword.'_'.$act_type);

		$rows = Better_DAO_Admin_Userrank::getInstance()->getAll(array(
			'page' => $page,
			'user_keyword' => $user_keyword,
			'act_type' => $act_type,
			'reload' => $reload,
			'cacheKey' => $cacheKey,
			'page_size' => $pageSize,
			));
		
		$data = array_chunk($rows, $pageSize);
		$return['count'] = count($rows);
		$return['rows'] = &$data[$page-1];

		unset($data);

		return $return;		
			
	}
	
}