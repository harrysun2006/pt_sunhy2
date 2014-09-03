<?php

class Better_Admin_Blog
{
	public static function resetPlace(array $bids)
	{
		$result = false;
		
		foreach($bids as $bid) {
			$data = Better_Blog::getBlog($bid);
			$uid = $data['blog']['uid'];
			$address = $data['blog']['address'];
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($data['user'], 'reset_place', $address);
		}
		
		Better_DAO_Admin_Blog::resetPlace($bids);
		$result = true;
		
		return $result;
	}
	
	
	public static function changeBlogType($bid, $type, $uid=''){
		$result = false;
		if(is_string($bid)){
			$data = Better_Blog::getBlog($bid);
			
			Better_Cache_Clear::blogBid($bid);//清blog缓存
			
			if($data['blog']['type']!=$type){
				Better_DAO_Blog::getInstance($uid)->update(array('type'=>$type), $bid);
				$userinfo = Better_DAO_User::getInstance($uid)->getByUid($uid);
				$countdate['uid'] = $uid;
				//Better_Log::getInstance()->logInfo(serialize($userinfo),'changetype');
				//修改吼吼贴士计数更新方式				
				if($type=='tips'){
					$countdate['now_tips'] = +1;	
					$countdate['now_posts'] = -1;						
				} else {
					$countdate['now_tips'] = -1;
					$countdate['now_posts'] = +1;								
				}
				//Better_Log::getInstance()->logInfo(serialize($date),'changetype');
				Better_DAO_User::getInstance($uid)->update($countdate);

				//设置为审核通过
				
				if($data['blog']['checked']=='0'){
					Better_Blog::setNeedCheck($bid, false);
					Better_Hook::$hookResults['BlogPosted'] = 0;
					$blog['blog']['checked'] = 1;										
					if ($bid) {
						Better_Hook::factory(
							array(
								'Syncsites', 'Queue'
							)
						)->invoke('BlogPosted', array(
							'blog' => $blog['blog'],
							'data' => $blog['blog'],
							'bid' => $bid,				
							'uid' => $blog['blog']['uid'],
							'need_sync' => $blog['blog']['need_sync']
							));
						$result = 1;
					}
				}				
				if($result){
					$content = '通过审核：<br>'.$data['blog']['message'];
					Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog($content, 'pass_blog');
				}
			}
			
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($data['user'], 'change_blog_type', '更改微博类型: '.$data['blog']['type'].'=>'.$type.' | bid:'.$bid);
			$result = true;
		}else if(is_array($bid)){
			if($bid && count($bid)>0){
				foreach($bid as $v){
					$tmp = explode('.', $v);
					$u = $tmp[0];
					
					$data = Better_Blog::getBlog($v);
					
					Better_Cache_Clear::blogBid($v);//清blog缓存
					
					if($data['blog']['type']!=$type){
						$userinfo = Better_DAO_User::getInstance($u)->getByUid($u);
						Better_DAO_Blog::getInstance($u)->update(array('type'=>$type), $v);
						$counterdate['uid'] = $u;
						//Better_Log::getInstance()->logInfo(serialize($userinfo),'changetype');
						
						if($type=='tips'){
							$counterdate['now_tips'] = +1;	
							$counterdate['now_posts'] = -1;						
						} else {
							$counterdate['now_tips'] = -1;
							$counterdate['now_posts'] = +1;								
						}
						//Better_Log::getInstance()->logInfo(serialize($date),'changetype');
						Better_DAO_User::getInstance($u)->update($counterdate);
					}
					//设置成审核通过
					$bid = $v;
					if($data['blog']['checked']=='0'){
						Better_Blog::setNeedCheck($bid, false);
						Better_Hook::$hookResults['BlogPosted'] = 0;
						$blog['blog']['checked'] = 1;										
						if ($bid) {
							Better_Hook::factory(
								array(
									'Syncsites', 'Queue'
								)
							)->invoke('BlogPosted', array(
								'blog' => $blog['blog'],
								'data' => $blog['blog'],
								'bid' => $bid,				
								'uid' => $blog['blog']['uid'],
								'need_sync' => $blog['blog']['need_sync']
								));
							$result = 1;
						}
					}				
					if($result){
						$content = '通过审核：<br>'.$data['blog']['message'];
						Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog($content, 'pass_blog');
					}
					
					Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($data['user'], 'change_blog_type', '更改微博类型: '.$data['blog']['type'].'=>'.$type.' | bid:'.$v);
				}
				$result = true;
			}
		}
		
		return $result;
	}
	
	
	
	public static function setTipTop($bid, $isTop, $uid){
		$result = false;
		if(is_string($bid)){
			$data = Better_Blog::getBlog($bid);
			
			Better_Cache_Clear::blogBid($bid);//清blog缓存
			
			if($data['blog']['is_top']!=$isTop){
				Better_DAO_Blog::getInstance($uid)->update(array('is_top'=>$isTop), $bid);
			}
			
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($data['user'], 'set_blog_top', '置顶贴士: '.' bid:'.$bid);
			$result = true;
		}
		
		return $result;
	}
	
	public static function setTipFeatured($bid, $featured, $uid){
		$result = false;
		if(is_string($bid)){
			$data = Better_Blog::getBlog($bid);
			
			Better_Cache_Clear::blogBid($bid);//清blog缓存
			
			if($data['blog']['featured']!=$featured){
				Better_DAO_Blog::getInstance($uid)->update(array('featured'=>$featured), $bid);
			}
			
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($data['user'], 'set_blog_featured', '优质贴士: '.' bid:'.$bid.'=>'.$featured);
			$result = true;
		}
		
		return $result;
	}
	
	public static function passCheck(array $bids)
	{
		$result = false;
		
		foreach($bids as $bid) {
			Better_Blog::setNeedCheck($bid, false);
		}
		
		//Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('审核微博（ids: '.implode(',', $bids).'）', 'update');

		$result = true;
		
		return $result;		
	}
	
	public static function resetPlace2(array $bids)
	{
		$result = false;
		$uids = array();
		
		foreach($bids as $bid) {
			$data = Better_Blog::getBlog($bid);
			$uid = $data['blog']['uid'];
			$uids[] = $uid;
			$address = $data['blog']['address'];
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($data['user'], 'reset_place', $address);
			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($data['user'], 'reset_user_place', $data['user']['address']);

			Better_User::getInstance($uid)->updateUser(array(
				'lon' => 0,
				'lat' => 0,
				'city' => '',
				'address' => '',
				));
		}
		
		Better_DAO_Admin_Blog::resetPlace($bids);
		//Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addLog('重置用户位置（uids: '.implode(',', $uids).'）', 'update');
		
		$result = true;
		
		return $result;
	}	
	
	public static function delBlogs(array $bids)
	{
		$result = false;
		
		foreach($bids as $bid) {
			$data = Better_Blog::getBlog($bid);
			$uid = $data['blog']['uid'];
			$message = $data['blog']['message'];

			Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($data['user'], 'del_blog', '删除微博:<br>'.$message);
			unset($data['blog']['xy']);
			
			Better_DAO_Admin_BlogDeleted::getInstance()->insert(array(
				'uid' => $uid,
				'admin_uid' => Better_Registry::get('sess')->admin_uid,
				'dateline' => BETTER_NOW,
				'username' => $data['user']['username'],
				'nickname' => $data['user']['nickname'],
				'bid' => $bid,
				'data' => serialize($data['blog'])
				));
			
			Better_Blog::delete($bid);
		}
		
		
		$result = true;
		
		return $result;
	}
	
	public static function delAttach(array $bids)
	{
		$result = false;
		
		$fids = array();
		foreach($bids as $bid) {
			$data = Better_Blog::getBlog($bid);
			$attach = $data['blog']['attach'];
			$message = $data['blog']['message'];
			$type = $data['blog']['type'];
			if($message=='' && $type!='checkin'){
				Better_DAO_Admin_BlogDeleted::getInstance()->insert(array(
				'uid' => $data['blog']['uid'],
				'admin_uid' => Better_Registry::get('sess')->admin_uid,
				'dateline' => BETTER_NOW,
				'username' => $data['user']['username'],
				'nickname' => $data['user']['nickname'],
				'bid' => $bid,
				'data' => serialize($data['blog'])
				));
			
				Better_Blog::delete($bid);
			} else {
				$at = Better_Attachment::getInstance($attach);
				$att = $at->parseAttachment();
				
				$filename = $att['filename'];
				
				if ($attach) {
					$fids[] = $attach;
					
					Better_Attachment::getInstance($attach)->delete()->delete();
					
					Better_Hook::factory(array(
					  'Admin_DirectMessage', 'Cache',
					))->invoke('UserAttachDeleted', array(
					'bid' => $bid,
					'userInfo' => $data['user'],
					'filename' => $filename,
					'dateline'=> $data['blog']['dateline']
					));
				}
			}
			Better_DAO_Newimg::getInstance()->deleteByCond(array('refid' => $bid, 'type' => 'attach'));
			
			if($attach){
				Better_Admin_Administrators::getInstance(Better_Registry::get('sess')->admin_uid)->addUserLog($data['user'], 'del_attach', '删除图片:<br>'.$attach);
			}
		}
		
		if (count($fids)) {
			Better_DAO_Admin_Blog::resetAttach($bids);
		}
		
		$result = true;
		
		return $result;
	}
	
	public static function getBlogs($params)
	{
		$return = array(
			'count' => 0,
			'rows' => array(),
			);
		$uid = Better_Registry::get('sess')->admin_uid;
		
		$page = $params['page'] ? intval($params['page']) : 1;
		$keyword = $params['keyword'] ? trim($params['keyword']) : '';
		$user_keyword = $params['user_keyword'] ? trim($params['user_keyword']) : '';
		$place_keyword = $params['place_keyword'] ? trim($params['place_keyword']) : '';
		$photo = $params['photo'] ? $params['photo'] : 2;
		$reload = $params['reload'] ? 1 : 0;
		$pageSize = $params['page_size'] ? intval($params['page_size']) : BETTER_PAGE_SIZE;
		$checked = $params['checked']!='' ? ($params['checked'] ? '1' : '0') : '';
		$source = $params['source'] ? trim($params['source']) : '';
		$uid = $params['uid'] ? trim($params['uid']) : '';
		$order = $params['order']=='asc' ? 'ASC' : 'DESC';
		$from = $to = '';
		$type = $params['type'] ? $params['type'] : '';
		$poi_id = $params['poi_id'] ? $params['poi_id'] : '';
		
		$filter_uids = $params['filter_uids']?$params['filter_uids']:array();
		
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
		
		$cacheKey = md5($from.'_'.$to.'_'.$keyword.'_'.$place_keyword.'_'.$user_keyword.'_'.$photo.'_'.$checked.'_'.$source.'_'.$uid.'_'.$order.'_'.$type.'_'.$poi_id);

		$rows = Better_DAO_Admin_Blog::getAllBlogs(array(
			'uid' => $uid,
			'page' => $page,
			'from' => $from,
			'to' => $to,
			'keyword' => $keyword,
			'user_keyword' => $user_keyword,
			'place_keyword' => $place_keyword,
			'photo' => $photo,
			'reload' => $reload,
			'cacheKey' => $cacheKey,
			'checked' => $checked,
			'source' => $source,
			'order' => $order,
			'type' => $type,
			'poi_id' => $poi_id,
			'filter_uids' =>$filter_uids
			));

		$data = array_chunk($rows, $pageSize);
		$return['count'] = count($rows);
		foreach($data[$page-1] as $row) {
			$return['rows'][] = self::parseBlogRow($row);
		}

		unset($data);

		return $return;
	}
	
	public static function parseBlogRow(&$row)
	{

		if ($row['attach']) {
			$at = Better_Attachment::getInstance($row['attach']);
			$attach = $at->parseAttachment();

			$row['attach_tiny'] = $attach['tiny'];
			$row['attach_thumb'] = $attach['thumb'];
			$row['attach_url'] = $attach['url'];
			
			$row['attach_size'] = Better_Attachment::formatSize($attach['filesize']);
			$row['filename'] = $attach['filename'];
		} else {
			$row['filename'] = $row['attach_size'] = $row['attach_url'] = $row['attach_tiny'] = $row['attach_thumb'] = '';
		}

		if ($row['avatar']) {
			$row['avatar_thumb'] = Better_Registry::get('user')->getUserAvatar('thumb', $row);
			$row['avatar_url'] = Better_Registry::get('user')->getUserAvatar('normal', $row);
			$row['avatar_tiny'] = Better_Registry::get('user')->getUserAvatar('tiny', $row);
		} else {
			$row['avatar_tiny'] = $row['avatar_thumb'] = $row['avatar_url'] = Better_Attachment::getInstance()->getConfig()->global->avatar->default_url;
		}

		list($row['lon'], $row['lat']) = Better_Functions::XY2LL($row['x'], $row['y']);
		list($row['user_lon'], $row['user_lat']) = Better_Functions::XY2LL($row['user_x'], $row['user_y']);

		($row['x'] && $row['y']) && list($row['lon'], $row['lat']) = Better_Functions::XY2LL($row['x'], $row['y']);
		($row['user_x'] && $row['user_y']) && list($row['user_lon'], $row['user_lat']) = Better_Functions::XY2LL($row['user_x'], $row['user_y']);
		
		
		if ($row['poi_id']) {
			$row['poi'] = & Better_Poi_Info::getInstance($row['poi_id'])->getBasic();
		} else if ($row['last_checkin_poi']) {
			$row['user_poi'] = & Better_Poi_Info::getInstance($row['last_checkin_poi'])->getBasic();
		} else {
			$row['poi'] = array();
		}
		
		if($row['message']){
			$row['message'] .='<br>来源： '.$row['source'];
		}

		return $row;
	}	
	
}
