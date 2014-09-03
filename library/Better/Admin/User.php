<?php

class Better_Admin_User
{
	
	public static function getUsers($params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
			
		$uid = $params['uid'] ? $params['uid'] : ''; 
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$place_keyword = $params['place_keyword'] ? trim($params['place_keyword']) : '';
		$avatar = isset($params['avatar']) ? ($params['avatar'] ? '1' : '0') : -1;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$viewType = $params['view_type']?trim($params['view_type']) : 0;//0:recommend=0,1;1:recommend=1;2:recommend=0
		
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
		$cacheKey = md5($uid.'_'.$keyword.'_'.$place_keyword.'_'.$avatar.'_'.$viewType.'_'.$from.'_'.$to.'_user');
			
		//查取得是最近的用户头像还是查找的是最近成为掌门的头像
		if(isset($params['advance']) && $params['advance']){
			$majors = Better_DAO_Poi_Major::getInstance()->getMajors(array(
				'page'=>$page,
				'from'=>$from,
				'to'=>$to,
				'recommend'=>$viewType
			));	
			$majorscount =  Better_DAO_Poi_Major::getInstance()->getMajorsCount(array(
				'page'=>$page,
				'from'=>$from,
				'to'=>$to,
				'recommend'=>$viewType
			));
			$majorscount = $majorscount['mcount'];
			if(isset($majors) && count($majors)>0){
				$cacheKey = md5($viewType.'_'.$from.'_'.$to.'_user'.'_'.$page);
				$rows = Better_DAO_Admin_User::getAllUsers(array(
					'page' => $page,
					'keyword' => $keyword,
					'place_keyword' => $place_keyword,
					'avatar' => $avatar,
					'cacheKey' => $cacheKey,
					'reload' => $reload,
					'from' => $from,
					'to' => $to,
					'uid'=> $majors,
					'recommend'=>$viewType,
					'advance'=>$params['advance']
					));
			}
		}else{
			$cacheKey = md5($uid.'_'.$keyword.'_'.$place_keyword.'_'.$avatar.'_'.$from.'_'.$to.'_user');
			$rows = Better_DAO_Admin_User::getAllUsers(array(
				'page' => $page,
				'keyword' => $keyword,
				'place_keyword' => $place_keyword,
				'avatar' => $avatar,
				'cacheKey' => $cacheKey,
				'reload' => $reload,
				'from' => $from,
				'to' => $to,
				'uid'=> $uid
				));
		}	
		$data = array_chunk($rows, $pageSize);
		if($majorscount){
			$return['count'] = $majorscount;
			foreach($rows as $row) {
				$return['rows'][] = self::parseUser($row);
			}
		}else{
			$return['count'] = count($rows);		
			foreach($data[$page-1] as $row) {
				$return['rows'][] = self::parseUser($row);
			}
			unset($data);
		}

		return $return;
		
	}
	
	
	/**
	 *删除用户头像
	 *@param array uids
	 */
	public static function delAvatars($uids)
	{ 
		$result = 0;
		
		foreach ($uids as $uid){
		$user=Better_DAO_User::getInstance($uid)->getByUid($uid);
		$avatar=$user['avatar'];
		
		if ($avatar) {
			//list($uid,$tmp) = explode('.', $avatar);
			if(Better_User::getInstance($uid)->avatar()->delete()){
			    Better_User::getInstance($uid)->updateUser(array(
					'avatar' => '',
			    	'recommend'=>0,//在删除头像的同时需要取消推荐用户在首页显示
				));				
				Better_DAO_Poi_Major::getInstance()->recommendAvatar($uid,0);//取消推荐	
				Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($user, 'del_avatar', '删除头像:<br>'.$avatar);
			    $result=1;
			}
			else{
				$result=0;
				break;
			}

			} 
			
			//调用hook
			if($result==1){
				Better_Hook::factory(array(
				  'Admin_DirectMessage',
				))->invoke('UserAvatarDeleted', array(
				'userInfo' => $user
				));
			}
			
		}

		return $result;
	}
	/**
	 * 当用户成为掌门时推荐到首页显示
	 * @param array $uids
	 */
	public function recommendedAvatars($uids){
		$result = false;
		
		foreach($uids as $uid){
			$user=Better_DAO_User::getInstance($uid)->getByUid($uid);
			$username=$user['username'];
			
			Better_User::getInstance($uid)->updateUser(array(
				'recommend' => 1
				));
			Better_DAO_Poi_Major::getInstance()->recommendAvatar($uid,1);	
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($user, 'recommend_user', '推荐掌门【'.$username.'】到首页显示');
			
//			调用hook
//			Better_Hook::factory(array(
//			 'Admin_User',
//			))->invoke('RecommendedAvatar', array(
//			'uid' => $uid
//			));	
			
		}
		
		$result=true;
		return $result;
	}
	
/**
	 * 取消对所选用户的推荐
	 * @param array $uids
	 */
	public function unrecommendedAvatars($uids){
		$result = false;
		
		foreach($uids as $uid){
			$user=Better_DAO_User::getInstance($uid)->getByUid($uid);
			$username=$user['username'];
			
			Better_User::getInstance($uid)->updateUser(array(
				'recommend' => 0
				));
			Better_DAO_Poi_Major::getInstance()->recommendAvatar($uid,0);	
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($user, 'recommend_user', '取消对掌门【'.$username.'】的推荐');
			
			//调用hook
//			Better_Hook::factory(array(
//			 'Admin_DirectMessage',
//			))->invoke('ResetUserName', array(
//			'userInfo' => $user
//			));	
			
		}
		
		$result=true;
		return $result;
	}
	
	/**
	 * 重置用户位置
	 * @param array $uids
	 */
	public static function resetPlace($uids)
	{
		$result = false;
		
		foreach($uids as $uid){
			$user=Better_DAO_User::getInstance($uid)->getByUid($uid);
			$address=$user['address'];
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($user, 'reset_user_place', $address);
			
			Better_User::getInstance($uid)->updateUser(array(
				'lon' => 0,
				'lat' => 0,
				'city' => '',
				'address' => '',
				));
				
			//调用hook
			Better_Hook::factory(array(
				'User', 'Newblog', 'Debug', 'BlogReply', 'DirectMessage', 'Admin_DirectMessage',
				))->invoke('ResetUserPlace', array(
				'userInfo' => $user
				));
		}
		
		$result = true;
		
		return $result;
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
		
		if ($row['poi_id']) {
			$row['poi'] = & Better_Poi_Info::getInstance($row['poi_id'])->getBasic();
		} else if ($row['last_checkin_poi']) {
			$row['user_poi'] = & Better_Poi_Info::getInstance($row['last_checkin_poi'])->getBasic();
		} else {
			$row['poi'] = array();
		}
		
		
		return $row;
	}
	
	
	/**
	 * 重置用户名
	 * @param array $uids
	 */
	public function resetName($uids){
		$result = false;
		
		foreach($uids as $uid){
			$user=Better_DAO_User::getInstance($uid)->getByUid($uid);
			$username=$user['username'];
			
			Better_User::getInstance($uid)->updateUser(array(
				'username' =>'kai'. $uid
				));
				
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($user, 'reset_user_username', '重置用户名:<br>'.$username);
			
			//调用hook
			Better_Hook::factory(array(
			 'Admin_DirectMessage',
			))->invoke('ResetUserName', array(
			'userInfo' => $user
			));	
			
		}
		
		$result=true;
		return $result;
	}
	
	
	/**
	 * 重置用户姓名
	 * @param array $uids
	 */
	public function resetNickName($uids){
		$result = false;
		
		foreach($uids as $uid){
			$user=Better_DAO_User::getInstance($uid)->getByUid($uid);
			$nickname=$user['nickname'];
			
			Better_User::getInstance($uid)->updateUser(array(
				'nickname' => 'kai'.$uid
				));
				
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($user, 'reset_user_nickname', '重置用户姓名:<br>'.$nickname);
				
			//调用hook
			Better_Hook::factory(array(
				'Admin_DirectMessage',
			))->invoke('ResetNickName', array(
			'userInfo' => $user
			));	
			
		}
		
		$result=true;
		return $result;
	}
	
	
	/**
	 * 重置用户自我介绍
	 * @param array $uids
	 */
	public function resetSelfIntro($uids){
		$result = false;
		
		foreach($uids as $uid){
			$user=Better_DAO_User::getInstance($uid)->getByUid($uid);
			$self_intro=$user['self_intro'];
			
			Better_User::getInstance($uid)->updateUser(array(
				'self_intro' => '',
				));

			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($user, 'reset_user_selfintro', '重置用户自我介绍:<br>'.$self_intro);
				
			//调用hook
			Better_Hook::factory(array(
				 'Admin_DirectMessage',
				))->invoke('ResetUserSelfintro', array(
				'userInfo' => $user
				));
		}
		
		$result=true;
		return $result;
	}
	
	public function getUserRp($params)
	{
		$uid = $params['uid'];
		$data = Better_DAO_User_RpLog::getInstance($uid)->getUserRp($params);
		$howtogetrp = array();
		$howtogetrp['postblog'] = '吼吼';
		$howtogetrp['private'] = '私密';
		$howtogetrp['common'] = '其他';
		$howtogetrp['sync'] = '同步';
		$howtogetrp['newtips'] = '贴士';
		$howtogetrp['newfollower'] = '关注他人';
		$howtogetrp['invitesomebody'] = '成功邀请他人';
		$howtogetrp['login'] = '登陆';
		$howtogetrp['bycell'] = '手机号码';
		$howtogetrp['byapi'] = '客户端';
		$howtogetrp['checkin'] = '签到';
		$howtogetrp['friendwithsomebody'] = '加好友';
		$howtogetrp['tobemajor'] = '成为掌门';
		$howtogetrp['delete'] = '后台被删吼吼、贴士';
		$howtogetrp['regedit'] = '原先用户Karma';
		$howtogetrp['Admin'] = '后台处理';
		foreach($data['rows'] as $row){			
			$tmp = split("\_",$row['category']);
			$categoryname = '';
			$joinstr = '';
			for($i=0;$i<count($tmp);$i++){
				$categoryname .=$joinstr.$howtogetrp[$tmp[$i]];
				$joinstr = "_";
			}		
			$row['categoryname'] = $categoryname;	
				
			$result[] = $row;			
		}
		$data['rows'] = &$result;	
		
		return $data;
	}
	
	public function getUserKarma($params)
	{
		$uid = $params['uid'];
		$data = Better_DAO_User_KarmaLog::getInstance($uid)->getUserKarma($params);
		$howtogetrp = array();
		$howtogetrp['blocked_from_friend_request'] = '从好友请求中被阻止';
		$howtogetrp['cancel_friend'] = '取消好友';
		$howtogetrp['checkin'] = '签到';
		$howtogetrp['delete'] = '删除';
		$howtogetrp['friend_request'] = '发出好友邀请';
		$howtogetrp['friend_request_refused'] = '发出的好友邀请被拒绝';
		$howtogetrp['friend_with_somebody'] = '成为好友';
		$howtogetrp['invite_somebody'] = '邀请好友';
		$howtogetrp['login'] = '登陆';
		$howtogetrp['new_blog'] = '发信息';
		$howtogetrp['new_follower'] = '新的粉丝';
		$howtogetrp['new_tips'] = '新贴士';
		$howtogetrp['not_login'] = '未登录';
		$howtogetrp['reduce_follower'] = '减少粉丝';
		$howtogetrp['reduce_following'] = '减少关注的人';
	
		foreach($data['rows'] as $row){			
			$row['categoryname'] = $howtogetrp[$row['category']];				
			$result[] = $row;			
		}
		$data['rows'] = &$result;	
		
		return $data;
	}
}