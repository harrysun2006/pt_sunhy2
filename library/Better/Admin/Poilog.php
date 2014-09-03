<?php

class Better_Admin_Poilog
{
	
	public static function getAll(array $params)
	{
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$user_keyword = $params['user_keyword'] ? trim($params['user_keyword']) : '';
		$act_type = $params['act_type'] ? trim($params['act_type']) : '';

		$reload = $params['reload'] ? 1 : 0;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$from = $to = '';
		
		$poi_id = $params['poi_id']? $params['poi_id']: '';
		
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
		

		$rows = Better_DAO_Admin_Poilog::getInstance()->getAll(array(
			'page' => $page,
			'from' => $from,
			'to' => $to,
			'keyword' => $keyword,
			'user_keyword' => $user_keyword,
			'act_type' => $act_type,
			'reload' => $reload,
			'page_size' => $pageSize,
			'poi_id'=> $poi_id
			));

		return $rows;			
	}
	
}