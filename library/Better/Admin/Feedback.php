<?php

/**
 * feedback 处理类
 * @author yanglei
 *
 */
class Better_Admin_Feedback {
	
	public static function getFeedbacks($params){
		
		$return = array(
			'count' => 0,
			'rows' => array(),
		);
		
		$uid = Better_Registry::get('sess')->admin_uid;
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$typekeyword = $params['type_keyword'] ? trim($params['type_keyword']) : '';
		$reload = $params['reload'] ? 1 : 0;
		$from = $to = '';
		$status = isset($params['status']) ? $params['status'] : '';
		
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
		
		$cacheKey = md5($page.'_'.$from.'_'.$to.'_'.$keyword.'_'.$typekeyword.'_'.$status.'_feedback');
		
		$rows=Better_DAO_Admin_Feedback::getInstance()->getAllFeedbacks(array(
			'page' => $page,
			'keyword' => $keyword,
			'reload' => $reload,
			'type_keyword' => $typekeyword,
			'cachekey' => $cacheKey,
			'from' => $from,
			'to' => $to,
			'status'=> $status
			)
		);
		
		$data = array_chunk($rows, $pageSize);
		$return['count'] = count($rows);
		$return['rows']= $data[$page-1];
		unset($data);
		
		return $return;
		
	}
	
	
	public static function delFeedback($ids){
		
		$result = false;

		foreach($ids as $id){
			$content = Better_DAO_Admin_Feedback::getInstance()->get($id);
			
			Better_DAO_Admin_Feedback::getInstance()->delete($id);
			
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('删除反馈：<br>'.$content['content'], 'del_feedback');
		}
			
		$result = true;
		
		return $result;
		
	}
	
}

?>