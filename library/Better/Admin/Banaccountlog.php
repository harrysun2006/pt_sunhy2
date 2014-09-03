<?php

class Better_Admin_Banaccountlog
{
	
	public static function getAll(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$admin_uid = $params['admin_uid'] ? trim($params['admin_uid']) : '';
		$uid = $params['uid'] ? $params['uid'] :'';
		$reload = $params['reload'] ? 1 : 0;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
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
		

		$rows = Better_DAO_Admin_Banaccountlog::getInstance()->getAll(array(
			'page' => $page,
			'from' => $from,
			'to' => $to,
			'admin_uid' => $admin_uid,
			'page_size' => $pageSize,
			'uid' => $uid
			));
			
		return $rows;			
	}
	
}