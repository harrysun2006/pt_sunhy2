<?php

class Better_Admin_BlogDeleted
{

	public static function delBlogs(array $bids)
	{
		$result = false;

		Better_DAO_Admin_BlogDeleted::getInstance()->deleteByCond(array(
			'bid' => $bids
			));
		
		//Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('彻底删除微博（ids: '.implode(',', $bids).'）', 'delete');
		
		$result = true;
		
		return $result;
	}
	
	public static function restore(array $bids)
	{
		$result = false;

		$rows = Better_DAO_Admin_BlogDeleted::getInstance()->getAll(array(
			'bids' => $bids,
			'page' => '1',
			'pageSize' => 999999
			));
		foreach($rows['rows'] as $row) {
			$blog = unserialize($row['data']);
			$blog['x'] = $row['x'];
			$blog['y'] = $row['y'];
			Better_DAO_Blog::getInstance($blog['uid'])->insert($blog);
		}
		
		Better_DAO_Admin_BlogDeleted::getInstance()->deleteByCond(array(
			'bid' => $bids
			));
			
		//Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('恢复已删除微博（ids: '.implode(',', $bids).'）', 'insert');
		
		$result = true;
		
		return $result;
	}	
	
	public static function getBlogs($params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
		$uid = Better_Registry::get('sess')->getUid();
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$user_keyword = $params['user_keyword'] ? trim($params['user_keyword']) : '';
		$reload = $params['reload'] ? 1 : 0;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;

		$uid = $params['uid'] ? trim($params['uid']) : '';
		$order = $params['order']=='asc' ? 'ASC' : 'DESC';
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

		$rows = Better_DAO_Admin_BlogDeleted::getInstance()->getAll(array(
			'uid' => $uid,
			'page' => $page,
			'from' => $from,
			'to' => $to,
			'keyword' => $keyword,
			'user_keyword' => $user_keyword,
			'reload' => $reload,
			'order' => $order,
			));

		$return['count'] = $rows['count'];
		foreach($rows['rows'] as $row) {
			$row['blog'] = unserialize($row['data']);
			$return['rows'][] = Better_Admin_Blog::parseBlogRow($row);
		}

		return $return;
	}
}