<?php

/**
 * denounce 处理类
 * @author yanglei
 *
 */
class Better_Admin_Denounce {
	
	public static function getDenounceBlogs($params){
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$reason = $params['reason'] ? trim($params['reason']) : '';
		$reload = $params['reload'] ? 1 : 0;
		$from = $to = '';
		$status = $params['status']? $params['status']:'';
		
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
		
		
		$rows=Better_DAO_Admin_Denounce::getInstance()->getAllDenounces(array(
			'page' => $page,
			'keyword' => $keyword,
			'reload' => $reload,
			'from' => $from,
			'to' => $to,
			'reason'=>$reason,
			'page_size'=>$pageSize,
			'status'=>$status
			)
		);
		
		return self::parseRow($rows);
		
	}
	
	
	public static function getDenounceUsers($params){
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$reason = $params['reason'] ? trim($params['reason']) : '';
		$reload = $params['reload'] ? 1 : 0;
		$from = $to = '';
		$status = $params['status']? $params['status']:'';
		
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
		
		
		$rows=Better_DAO_Admin_Denounceuser::getInstance()->getAllDenounces(array(
			'page' => $page,
			'keyword' => $keyword,
			'reload' => $reload,
			'from' => $from,
			'to' => $to,
			'reason'=>$reason,
			'page_size'=>$pageSize,
			'status'=>$status
			)
		);
		
		return self::parseRow($rows);
		
	}
	
	
	public static function getDenouncePois($params){
		
		$return = array();
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$reason = $params['reason'] ? trim($params['reason']) : '';
		$reload = $params['reload'] ? 1 : 0;
		$from = $to = '';
		$status = $params['status']? $params['status']:'';
		
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
		
		
		$rows=Better_DAO_Admin_Denouncepoi::getInstance()->getAllDenounces(array(
			'page' => $page,
			'keyword' => $keyword,
			'reload' => $reload,
			'from' => $from,
			'to' => $to,
			'reason'=>$reason,
			'page_size'=>$pageSize,
			'status'=>$status
			)
		);
		
		$return['count'] = $rows['count'];
		foreach($rows['rows'] as $row){
			$poi_id = $row['poi_id'];
			$poi = Better_Poi_Info::getInstance($poi_id)->get();
			$row['poi_name'] = $poi['name'];
			
			switch ($row['reason'])
			{
				case 'closedown':
					$row['reason'] = '关门';
					break;
				case 'incorrect':
					$row['reason'] = '名称地点不符';
					break;
				case 'duplicate':
					$row['reason'] = '地点重复';
					break;
				case 'other':
					$row['reason'] = '其他';
					break;
				default:
					break;
			}
			
			switch($row['status'])
			{
				case 'in_progress':
					$row['status'] = '处理中';
					break;
				case 'have_progress':
					$row['status'] = '已处理';
					break;
				case 'no_progress':
					$row['status'] = '未处理';
					break;
				default:
					break;
			}
			
			$return['rows'][] = $row;
		}
		
		return $return;
		
	}
	
	
	private static function parseRow($rows){
		$return = array('count'=>0, 'rows'=>array());
		$return['count'] = $rows['count'];
		foreach($rows['rows'] as $row){
			$uid = $row['denounce_uid'];
			$user = Better_User::getInstance($uid)->getUser();
			$row['user'] = $user;
			
			switch($row['status'])
			{
				case 'in_progress':
					$row['status'] = '处理中';
					break;
				case 'have_progress':
					$row['status'] = '已处理';
					break;
				case 'no_progress':
					$row['status'] = '未处理';
					break;
				default:
					break;
			}
			
			if($row['attach']){
				$at = Better_Attachment::getInstance($row['attach']);
				$attach = $at->parseAttachment();

				$row['attach_tiny'] = $attach['tiny'];
				$row['attach_thumb'] = $attach['thumb'];
				$row['attach_url'] = $attach['url'];
			}
			
			$return['rows'][] = $row;
		}
		
		return $return;
		
	}
	
	
}

?>