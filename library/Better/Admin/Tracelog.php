<?php

class Better_Admin_Tracelog
{
	
	public static function getAll(array $params)
	{
		
		$rows = self::_getAll($params);
		
		return $rows;
	}
	
	protected static function _getAll(array $params)
	{
		exit;
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
		$uid = Better_Registry::get('sess')->getUid();
		$action = $params['a'] ? $params['a'] : '';
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$user_id = $params['user_id'] ? trim($params['user_id']) : '';
		
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

		$rows = Better_DAO_Admin_Tracelog::getInstance()->getAll(array(
			'action' => $action,
			'page' => $page,
			'from' => $from,
			'to' => $to,
			'keyword' => $keyword,
			'user_id' => $user_id,
			'page_size' => $pageSize,
			));
			
		$return = array('count' => $rows['count'], 'rows'=>array());
		if ($return['count']>0) {
			foreach ($rows['rows'] as $row) {
				$uid = $row['uid'];
				$userInfo = Better_User::getInstance($uid)->getUser();
				$row['userInfo'] = $userInfo;
				$return['rows'][] = $row;
			}
		}

		return $return;			
	}
	
}