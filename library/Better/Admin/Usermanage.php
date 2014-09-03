<?php

class Better_Admin_Usermanage
{
	
	public static function getUsers($params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
		$uid = Better_Registry::get('sess')->admin_uid;
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$state = $params['state'] ? trim($params['state']) : '';
		$avatar = isset($params['avatar']) ? ($params['avatar'] ? '1' : '0') : -1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$bedo_no = $params['bedo_no'] ? (int) $params['bedo_no'] : 0;
		
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
		
		$cacheKey = md5($keyword.'_'.$bedo_no.'_'.$state.'_'.$avatar.'_'.$from.'_'.$to.'_'.$page.'_usermanage');
		
		$results = Better_DAO_Admin_Usermanage::getAllUsers(array(
			'page' => $page,
			'keyword' => $keyword,
			'state' => $state,
			'avatar' => $avatar,
			'cacheKey' => $cacheKey,
			'reload' => $reload,
			'from' => $from,
			'pagesize'=>$pageSize,
			'to' => $to,
			'bedo_no'=>$bedo_no
			));			
		$rows = $results['rows'];
		$data = array_chunk($rows, $pageSize);
		$return['count'] = $results['count'];	
		foreach($data[$page-1] as $row) {
			$return['rows'][] = self::parseUser($row);
		}
		unset($data);
		return $return;		
	}
	
	
	
	
	protected static function parseUser(&$row)
	{
		if ($row['avatar']) {
			$row['avatar_thumb'] = Better_Registry::get('user')->getUserAvatar('thumb', $row);
			$row['avatar_url'] = Better_Registry::get('user')->getUserAvatar('normal', $row);
			$row['avatar_tiny'] = Better_Registry::get('user')->getUserAvatar('tiny', $row);
		} else {
			$row['avatar_tiny'] = $row['avatar_thumb'] = $row['avatar_url'] = Better_Attachment::getInstance()->getConfig()->global->avatar->default_url;
		}
		
		if($row['uid']){
			$sites = Better_User_Syncsites::getInstance($row['uid'])->getSites();
			$row['protocol'] = '';
			if($sites){
				$keys = array_keys($sites);
				if($keys && is_array($keys)){
					$str = implode($keys, '|');
					$row['protocol'] = $str;
				}
			}
		
		}
		
		
		return $row;
	}
	
	
	public static function banAccount($params){

		$uid = $params['uid']? $params['uid'] : '';
		if($uid){
			$user = Better_User::getInstance($uid)->getUserInfo();
			$params['old_state'] = $user['state'];
		}
		
		if($params['old_state']!=Better_User_State::BANNED){
			return Better_DAO_Admin_Usermanage::banAccount($params);
		}else {
			return 2; //已经是封号状态
		}
	
	}
	
	public static function unbanAccount($params){
	
		return Better_DAO_Admin_Usermanage::unbanAccount($params);
	
	}	
	
	public static function lockAccount($params){
	
		return Better_DAO_Admin_Usermanage::lockAccount($params);
	}
	
	public static function unlockAccount($params){
	
		return Better_DAO_Admin_Usermanage::unlockAccount($params);
	}
	
	
}