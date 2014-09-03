<?php

class Better_Admin_Filter
{
	
	public static function getAll(array $params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
		$uid = Better_Registry::get('sess')->getUid();
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$user_keyword = $params['user_keyword'] ? trim($params['user_keyword']) : '';
		$act_type = $params['act_type'] ? trim($params['act_type']) : '';
		$word_type = $params['word_type'] ? trim($params['word_type']) : '';
		$need_check = $params['need_check'] ? trim($params['need_check']) : '';

		$reload = $params['reload'] ? 1 : 0;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$from = $to = '';
		$uid = $params['uid'] ?$params['uid'] :0;
		
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
		
		$rows = Better_DAO_Admin_Filter::getInstance()->getAll(array(
			'page' => $page,
			'from' => $from,
			'to' => $to,
			'keyword' => $keyword,
			'user_keyword' => $user_keyword,
			'act_type' => $act_type,
			'reload' => $reload,
			'page_size' => $pageSize,
			'word_type' => $word_type,
			'need_check' => $need_check,
			'uid' => $uid
			));
			
		foreach($rows['rows'] as $row){
			
			$id = $row['uid'];
			$user = Better_User::getInstance($id)->getUserInfo();
			$row['username'] = $user['username'];
			
			$word_type = str_replace('replace_word', '2', $row['word_type']);
			$word_type = str_replace('ban_word', '1', $word_type);
			$word_type = str_replace('after_check_word', '3', $word_type);
			$word_type = str_replace('need_check_word', '4', $word_type);
			
			$row['word_type'] = $word_type;
			$return['rows'][] = $row;
		}
		
		$return['count'] = $rows['count'];
		
		return $return;					
	}
}