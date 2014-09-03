<?php
/**
 * admin页面用户勋章管理
 * @author  yangl
 */

class Better_Admin_Userbadge{
	
	private static $badges;
	
	
	/**
	 * 获得用户勋章
	 */
	public static function getUserBadges($params){
		$return = array(
			'count' => 0,
			'rows' => array(),
		);
		
		$uid = Better_Registry::get('sess')->admin_uid;
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$userkeyword = $params['userkeyword'] ? trim($params['userkeyword']) : '';
		$reload = $params['reload'] ? 1 : 0;
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
		
		$cacheKey = md5($userkeyword.'_'.$from.'_'.$to.'_'.$uid.'_'.'badge');
		
		$rows=Better_DAO_Admin_Userbadge::getAllUserBadges(array(
			'page' => $page,
			'userkeyword' => $userkeyword,
			'cacheKey' => $cacheKey,
			'from' => $from,
			'to' => $to,
			'reload' => $reload
			)
		);
		
		if(!self::$badges){
			self::$badges = Better_DAO_Admin_Base::getDbCacher()->get(md5('badge_table'));
		}
		
		$data = array_chunk($rows, $pageSize);
		$return['count'] = count($rows);
		foreach($data[$page-1] as $row){
			$return['rows'][]=self::parseBadge($row);
		}
		
		unset($data);
		
		return $return;
	}
	
	
	private static function parseBadge($row){
			
			$row['badge_name']=self::$badges[$row['bid']]['badge_name'];
			$row['badge_picture']=self::$badges[$row['bid']]['badge_picture'];
		
		return $row;
	}
	
}


?>